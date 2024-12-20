<?php

namespace App\Controllers;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Models\DealBitrix;
use App\Models\ContactBitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;
use App\Models\NovaID;
use App\Models\UserBitrix;

/**
 * Creación de cotizaciones en NOVA desde master Dental.
 */
class getDataDealIDController extends \Leaf\Controller
{
    public $DealBitrix;
    public $ContactBitrix;
    public $UserBitrix;
    public $helpers;
    public $HttpRequest;
    public $NovaID;
    public function __construct()
    {
        parent::__construct();
        $this->DealBitrix = new DealBitrix();
        $this->ContactBitrix = new ContactBitrix();
        $this->UserBitrix = new UserBitrix();
        $this->NovaID = new NovaID();
        $this->helpers = new Auxhelpers();
        $this->HttpRequest = new Request;
    }


    public function index()
    {
        $datos = $this->HttpRequest->body();
        //$this->helpers->LogRegister($datos, "datos");

        $resultBitrix = $this->DealBitrix->GETDealtBitrix($datos["id"]);
        //$this->helpers->LogRegister($resultBitrix, "resultBitrix");

        //$sucursal = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986A5B6"], "UF_CRM_66F44E986A5B6"));
        $companyCode = $this->helpers->extractValue($datos["Pipeline"]);
        print_r($datos);

        /* $resultNova = $this->NovaID->findQuote($datos["id"], $companyCode);
        $this->helpers->LogRegister($resultNova, "resultNova"); */

        $data = $this->buildDataFromBitrix($resultBitrix);
        $data2 = json_encode($data);
        $this->helpers->LogRegister($data, "data");
        print_r($data2);
        $this->helpers->LogRegister($data2, "data2");

        //La cotización no existe
        /* if ($resultNova["statusCode"] == 400) {
            $resultCreateQuoteNova = $this->NovaID->CreateQuoteID($data, $companyCode);
            $this->helpers->LogRegister($resultCreateQuoteNova, "resultCreateQuoteNova");

            if (!isset($resultCreateQuoteNova["status"]) || $resultCreateQuoteNova["status"] !== 200) {
                $this->ContactBitrix->MessaggeContact((int)$datos["id"], 'Mensaje de Sistema: Error al crear la cotización en NOVA falta el campo');
            } else {
                $this->ContactBitrix->MessaggeContact((int)$datos["id"], 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cotización creado en Nova');
            } 
        }
        //el contacto SI existe en nova por lo que puedo actualizarlo allí
        if ($resultNova["statusCode"] == 200) {
            (int)$id = $datos["id"];
            $result = $this->NovaID->UpdateQuoteID($data, $companyCode);
            $this->helpers->LogRegister($result, "result actualización");
            $this->ContactBitrix->MessaggeContact((int)$id, 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cotización existente, información actualizada');
        }*/
    }

    private function buildDataFromBitrix($resultBitrix)
    {
        $moneda = $this->getMoneda($resultBitrix["CURRENCY_ID"]);


        $montoTasaCambio = $this->DealBitrix->GETCurrencyID($resultBitrix["CURRENCY_ID"]);
        $resultContact = $this->ContactBitrix->GETContactBitrix($resultBitrix["CONTACT_ID"]);
        $this->helpers->LogRegister($resultContact, "resultContact");


        $codigoCentroCosto = $this->helpers->extractValue($this->helpers->FieldsValue($resultContact["UF_CRM_1729952491"], "UF_CRM_1729952491", "contact"));
        $codigoFormaPago = $this->helpers->extractValue($this->helpers->FieldsValue($resultContact["UF_CRM_1729952639"], "UF_CRM_1729952639", "contact"));

        $resultUser = $this->UserBitrix->GETUsertBitrix($resultBitrix["ASSIGNED_BY_ID"]);
        //"codigoVendedor" => $resultUser["UF_USR_1727286282223"],
        $products = $this->getProductsDeal($resultBitrix["ID"]);

        return [
            [
                "codigoRefExterna" => (string)$resultBitrix["ID"],
                "codTipoTransaccion" => (string)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1727286256"], "UF_CRM_1727286256", "deal")),
                "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986A5B6"], "UF_CRM_66F44E986A5B6", "deal")),
                "codigoActividad" => 1,
                "tipoFacturaElec" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1727286167"], "UF_CRM_1727286167", "deal")),
                "codigoMoneda" => (int)$moneda,
                "tipoTasaCambio" => (string)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1731680467"], "UF_CRM_1731680467", "deal")),
                "montoTasaCambio" => (float)$montoTasaCambio["AMOUNT"],
                "fechaDoc" => "2024-01-01",
                "fechaVencimientoDoc" => "2024-01-01",
                "diasValidezDoc" => (int)$resultBitrix["UF_CRM_1731684990"],
                "codigoRefCliente" => (string)$resultBitrix["CONTACT_ID"],
                "codigoFormaPago" => (int)$codigoFormaPago,
                "codigoCentroCosto" => (string)$codigoCentroCosto,
                "anotaciones" => (string)$resultBitrix["COMMENTS"],
                "codigoVendedor" => (int)$resultUser[0]["UF_USR_1727286282223"],
                "subtotal" => (float)$this->helpers->extractMonto($resultBitrix["OPPORTUNITY"]),
                "descuento" => 0.00,
                "impuesto" => 0.00,
                "otrosCargos" => 0.00,
                "totalDocumento" => (float)$this->helpers->extractMonto($resultBitrix["OPPORTUNITY"]),
                "items" =>
                [
                    $products,
                ]
            ]
        ];
    }
    private function getMoneda($moneda)
    {
        switch ($moneda) {
            case "CRC":
                return $moneda = 1;
            case "USD":
                return $moneda = 2;
            case "EUR":
                return $moneda = 3;
            default:
                return $moneda = 1;  // Valor por defecto
        }
    }

    private function getProductsDeal($id)
    {
        $resultDealProductos = $this->DealBitrix->DealSetProducts($id);
        $items = [];
        $lineNumber = 1; // Para numerar las líneas

        foreach ($resultDealProductos["result"] as $product) {
            // Mapeamos los datos del producto a la estructura que espera el ERP
            $items[] = [
                "numeroLinea" => 1,  // Incrementamos el número de línea
                "codigoBodega" => "B01",         // Aquí puedes poner el código de bodega adecuado
                "tipoCodigo" => "sistema / externo",  // El tipo de código puede ser siempre el mismo o variar
                "codigoItem" => (int)$product["PRODUCT_ID"],  // El código del producto
                "descripción" => $product["PRODUCT_NAME"],  // Nombre del producto
                "unidadPrincipal" => $product["MEASURE_NAME"],  // Unidad de medida
                "cantidad" => (float)$product["QUANTITY"],  // Cantidad del producto
                "precio" => (float)$product["PRICE"],  // Precio del producto
                "descuento" => (float)$product["DISCOUNT_SUM"],  // Descuento (puedes tomarlo del producto si es necesario)
                "impuesto" => (float)$product["TAX_RATE"],  // Impuesto, si lo tienes calculado
                "totalLinea" => (float)$product["PRICE"] * (float)$product["QUANTITY"] - (float)$product["DISCOUNT_SUM"]  // Total por línea considerando cantidad y descuento
            ];
        }

        return $items;
    }
}
