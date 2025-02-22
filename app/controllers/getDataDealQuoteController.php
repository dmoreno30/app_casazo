<?php

namespace App\Controllers;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Models\DealBitrix;
use App\Models\ContactBitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;
use App\Models\NovaQuote;
use App\Models\UserBitrix;
use App\Controllers\GetProductsBitrix;

/**
 * Creación de cotizaciones en NOVA desde master Dental.
 */
class getDataDealQuoteController extends \Leaf\Controller
{
    public $DealBitrix;
    public $ContactBitrix;
    public $UserBitrix;
    public $helpers;
    public $HttpRequest;
    public $NovaQuote;
    public $infoProduct;
    public function __construct()
    {
        parent::__construct();
        $this->DealBitrix = new DealBitrix();
        $this->ContactBitrix = new ContactBitrix();
        $this->UserBitrix = new UserBitrix();
        $this->NovaQuote = new NovaQuote();
        $this->helpers = new Auxhelpers();
        $this->HttpRequest = new Request;
        $this->infoProduct = new GetProductsBitrix();
    }


    public function index()
    {
        $datos = $this->HttpRequest->body();
        //$this->helpers->LogRegister($datos, "datos");
        (int)$id = $datos["id"];
        $resultBitrixMD = $this->DealBitrix->GETDealtBitrix($id);
        $this->helpers->LogRegister($resultBitrixMD, "resultBitrix");

        //$sucursal = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986A5B6"], "UF_CRM_66F44E986A5B6"));
        $companyCode = $this->helpers->extractValue($datos["venta"]);
        $this->DealBitrix->MessaggeDeal($id, "Mensaje de Sistema: Información enviada a nova, espere...");
        $resultNova = $this->NovaQuote->findQuote($id, $companyCode);
        $this->helpers->LogRegister($resultNova, "resultNova");


        switch ($resultNova["statusCode"]) {
            case '200':
                $data = $this->buildDataFromBitrixUpdate($resultBitrixMD);
                $this->DealBitrix->MessaggeDeal($id, "Mensaje de Sistema: {$resultNova['message']}");


                $resultUpdateQuote = $this->NovaQuote->UpdateQuoteMD($data, $companyCode, $id);
                $this->helpers->LogRegister($resultUpdateQuote, "result actualización");
                switch ($resultUpdateQuote["statusCode"]) {
                    case 200:
                        $this->DealBitrix->MessaggeDeal($id, "Mensaje de Sistema: Información enviada a NOVA - {$resultUpdateQuote['message']}");
                        break;
                }
                break;
            case '400':
                $data = $this->buildDataFromBitrix($resultBitrixMD);
                $this->DealBitrix->MessaggeDeal($id, "Mensaje de Sistema: Información enviada a NOVA - {$resultNova["message"]}");
                $resultCreateQuoteNova = $this->NovaQuote->CreateQuoteMD($data, $companyCode);
                $data2 = json_encode($data);
                $this->helpers->LogRegister($data, "data");
                $this->helpers->LogRegister($data2, "data2");
                $this->helpers->LogRegister($resultCreateQuoteNova, "resultCreateQuoteNova");

                switch ($resultCreateQuoteNova["statusCode"]) {
                    case 200:
                        $this->DealBitrix->MessaggeDeal($id, 'Mensaje de Sistema: Información enviada a NOVA - Cotización creada en NOVA');

                        break;
                    case 400:

                        $this->DealBitrix->MessaggeDeal($id, "Mensaje de Sistema: Error al crear el pedido - {$resultCreateQuoteNova['message']}");
                        break;
                }
                break;
        }
    }

    private function buildDataFromBitrix($resultBitrix)
    {

        try {
            $moneda = $this->getMoneda($resultBitrix["CURRENCY_ID"]);
            $montoTasaCambio = $this->DealBitrix->GETCurrencyID($resultBitrix["CURRENCY_ID"]);
            $resultContact = $this->ContactBitrix->GETContactBitrix($resultBitrix["CONTACT_ID"]);
            //$this->helpers->LogRegister($resultContact, "resultContact");


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
                    "items" => $products,
                ]
            ];
        } catch (\Throwable $th) {
            $this->DealBitrix->MessaggeDeal($resultBitrix["ID"], 'Mensaje de Sistema: Ocurrio un error al enviar la informción a Nova');
            $this->helpers->LogRegister($th->getMessage());
        }
    }

    private function buildDataFromBitrixUpdate($resultBitrix)
    {



        $moneda = $this->getMoneda($resultBitrix["CURRENCY_ID"]);
        $montoTasaCambio = $this->DealBitrix->GETCurrencyID($resultBitrix["CURRENCY_ID"]);
        $resultContact = $this->ContactBitrix->GETContactBitrix($resultBitrix["CONTACT_ID"]);
        //$this->helpers->LogRegister($resultContact, "resultContact");


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
                "items" => $products,
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
        $numerodeLinea = 1;

        foreach ($resultDealProductos["result"] as $product) {
            $resultInfoStore = $this->infoProduct->getStore($product["STORE_ID"]);
            $ResultproductID = $this->infoProduct->getParentID($product["PRODUCT_ID"]);
            if ($ResultproductID == 0) {
                $productID = $product["PRODUCT_ID"];
                $codigoBodega = "BPR";
            } else {
                $productID = $ResultproductID;
                $codigoBodega = $resultInfoStore;
            }
            $this->helpers->LogRegister($productID, "id producto");

            // Mapeamos los datos del producto a la estructura que espera el ERP
            $items[] = [
                "numeroLinea" => $numerodeLinea,  // Incrementamos el número de línea
                "codigoBodega" => (string)$codigoBodega,         // Aquí puedes poner el código de bodega adecuado
                "tipoCodigo" => "externo",  // El tipo de código puede ser siempre el mismo o variar
                "codigoItem" => (string)$productID,  // El código del producto
                "descripción" => $product["PRODUCT_NAME"],  // Nombre del producto
                "unidadPrincipal" => $product["MEASURE_NAME"],  // Unidad de medida
                "cantidad" => (float)$product["QUANTITY"],  // Cantidad del producto
                "precio" => (float)$product["PRICE"],  // Precio del producto
                "descuento" => (float)$product["DISCOUNT_SUM"],  // Descuento (puedes tomarlo del producto si es necesario)
                "impuesto" => (float)$product["TAX_RATE"],  // Impuesto, si lo tienes calculado
                "totalLinea" => (float)$product["PRICE"] * (float)$product["QUANTITY"] - (float)$product["DISCOUNT_SUM"]  // Total por línea considerando cantidad y descuento
            ];

            $numerodeLinea++;
        }

        return $items;
    }
}
