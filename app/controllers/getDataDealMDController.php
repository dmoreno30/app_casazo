<?php

namespace App\Controllers;

use App\Models\DealBitrix;
use App\Models\ContactBitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;
use App\Models\NovaID;
use App\Models\UserBitrix;

/**
 * Creación de cotizaciones en NOVA desde imagenes dentales.
 */
class getDataDealIDController extends \Leaf\Controller
{
    public $DealBitrix;
    public $ContactBitrix;
    public $UserBitrix;
    public $helpers;
    public $HttpRequest;
    public $Nova;
    public function __construct()
    {
        parent::__construct();
        $this->DealBitrix = new DealBitrix();
        $this->ContactBitrix = new ContactBitrix();
        $this->UserBitrix = new UserBitrix();
        $this->Nova = new NovaID();
        $this->helpers = new Auxhelpers();
        $this->HttpRequest = new Request;
    }



    public function index()
    {
        $datos = $this->HttpRequest->body();

        (int)$id = $datos["id"];
        $sucursal = $this->helpers->extractValue($this->helpers->FieldsValue($datos["Venta"], "Venta"));
        $resultNova = $this->Nova->findQuote((int)$id, 58);
        $resultBitrix = $this->DealBitrix->GETDealtBitrix($datos["id"]);

        $data = $this->buildDataFromBitrix($resultBitrix);

        if ($resultNova == "") {
            $resultCreateDealNova = $this->Nova->CreateQuoteID($data, 58);
            $this->helpers->LogRegister($resultCreateDealNova, "resultCreateContactNova");
            $this->DealBitrix->MessaggeDeal((int)$datos["id"], 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cotización creada en Nova');
        } else {
            (int)$id = $datos["id"];
            $this->DealBitrix->MessaggeDeal((int)$id, 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cotización existente, información actualizada');
            $result = $this->Nova->updateDeal($data, 58);
            print_r($result);
        }
    }

    private function buildDataFromBitrix($resultBitrix)
    {
        $moneda = $resultBitrix["CURRENCY_ID"] == "CRC" ? 1 : "";
        $moneda = $resultBitrix["CURRENCY_ID"] == "USD" ? 2 : "";
        $moneda = $resultBitrix["CURRENCY_ID"] == "EUR" ? 3 : "";

        $montoTasaCambio = $this->DealBitrix->GETCurrencyID($moneda);
        $resultContact = $this->ContactBitrix->GETContactBitrix($resultBitrix["CONTACT_ID"]);
        $codigoCentroCosto = $this->helpers->extractValue($this->helpers->FieldsValue($resultContact["UF_CRM_1729952491"], "UF_CRM_1729952491"));
        $codigoFormaPago = $this->helpers->extractValue($this->helpers->FieldsValue($resultContact["UF_CRM_1729952639"], "UF_CRM_1729952639"));


        $resultUser = $this->UserBitrix->GETUsertBitrix($resultBitrix["ASSIGNED_BY_ID"]);

        $direccionPri = $this->getDireccion($resultBitrix, [
            "UF_CRM_66F44E9847FD2",
            "UF_CRM_66EED02679DF7",
            "UF_CRM_66EED0268A3A3",
            "UF_CRM_66EED026A23A4",
            "UF_CRM_66EED026C5E1E"
        ]);

        $direccionMedico = $this->getDireccion($resultBitrix, [
            "UF_CRM_1730153280",
            "UF_CRM_1730153445",
            "UF_CRM_1730155105",
            "UF_CRM_1730155278"
        ]);

        return [
            [
                "codigoRefExterna" => $resultBitrix["ID"],
                "codTipoTransaccion" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1727286256"], "UF_CRM_1727286256")),
                "codigoSucursal" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986A5B6"], "UF_CRM_66F44E986A5B6")),
                "codigoMoneda" => $moneda,
                "tipoFacturaElec" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1727286167"], "UF_CRM_1727286167")),
                "tipoTasaCambio" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1731680467"], "UF_CRM_1731680467")),
                "montoTasaCambio" => $montoTasaCambio,
                "fechaDoc" => "consultar",
                "fechaVencimientoDoc" => "consultar",
                "diasValidezDoc" => $resultBitrix["UF_CRM_1731684990"],
                "codigoRefCliente" => $resultBitrix["CONTACT_ID"],
                "codigoFormaPago" => (int)$codigoFormaPago,
                "codigoCentroCosto" => $codigoCentroCosto,
                "anotaciones" => $resultBitrix["COMMENTS"],
                "codigoVendedor" => $resultUser["UF_USR_1727286282223"],
                "subtotal" => $this->helpers->extractMonto($resultBitrix["OPPORTUNITY"]),
                "descuento" => "ACT",
                "impuesto" => "ACT",
                "otrosCargos" => "ACT",
                "totalDocumento" => $this->helpers->extractMonto($resultBitrix["OPPORTUNITY"]),
                "items" =>
                [
                    "numeroLinea" => 1,
                    "codigoBodega" => "B01",
                    "tipoCodigo" => "sistema / externo",
                    "codigoItem" => 123,
                    "descripción" => "vaso",
                    "unidadPrincipal" => "UND",
                    "cantidad" => 1.00,
                    "precio" => 1000.00,
                    "descuento" => 0.00,
                    "impuesto" => 200.00,
                    "totalLinea" => 1200.00
                ]
            ]
        ];
    }

    private function getDireccion($resultBitrix, $fields)
    {
        $values = array_map(function ($field) use ($resultBitrix) {
            return $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix[$field], $field));
        }, $fields);

        return [
            'codigo' => implode("-", $values),
        ];
    }
}
