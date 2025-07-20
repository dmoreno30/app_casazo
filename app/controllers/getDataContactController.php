<?php

namespace App\Controllers;

use App\Models\ContactBitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;
use App\Models\Nova;

/**
 * Manejo de datos del contacto de Bitrix24
 * Envio de información a NOVA
 */
class getDataContactController extends \Leaf\Controller
{
    public $ContactBitrix;
    public $helpers;
    public $HttpRequest;
    public $Nova;
    public function __construct()
    {
        parent::__construct();
        $this->ContactBitrix = new ContactBitrix();
        $this->Nova = new Nova();
        $this->helpers = new Auxhelpers();
        $this->HttpRequest = new Request;
    }


    public function index()
    {
        $datos = $this->HttpRequest->body("Cedula");
        (int)$id = $datos["id"];
        $resultBitrix = $this->ContactBitrix->GETContactBitrix($datos["id"]);
        $this->ContactBitrix->MessaggeContact((int)$id, 'Mensaje de Sistema: Información enviada a NOVA espere...');

        
        $sucursal = $this->helpers->extractValue($datos["Venta"]);
        $this->helpers->LogRegister($sucursal, "sucursal");


        $cedula = $resultBitrix["UF_CRM_66EED02625C8F"];
        $resultNova = $this->Nova->findContact((string)$cedula, $sucursal);

        //$this->helpers->LogRegister($resultNova, "resultNova");

        /* $data2 = json_encode($data);
        $this->helpers->LogRegister($data2, "data2"); */

        //el contacto SI existe en nova por lo que puedo actualizarlo allí

        switch ($resultNova["statusCode"]) {
            case '200':
                $data = $this->buildDataFromBitrixUpdate($resultBitrix, $id);
                $resultUpdate = $this->Nova->updateContact($data, $sucursal, $id);
                $this->ContactBitrix->MessaggeContact((int)$id, "Mensaje de Sistema: Cliente existente - Enviando información para actualizar");
                $this->helpers->LogRegister($resultUpdate, "actualizado en nova");
                $data2 = json_encode($data);
                $this->helpers->LogRegister($data2, "data2 Json update");
                switch ($resultUpdate["statusCode"]) {
                    case 200:
                        $this->ContactBitrix->MessaggeContact((int)$id, "Mensaje de Sistema: {$resultUpdate['message']}");
                        break;
                }
                break;
            case '400':
                $data = $this->buildDataFromBitrix($resultBitrix, $id);
                $resultCreateContactNova = $this->Nova->CreateContact($data, $sucursal);
                $data2 = json_encode($data);
                $this->helpers->LogRegister($data2, "data2");
                $this->helpers->LogRegister($resultCreateContactNova, "resultCreateContactNova");
                if ($resultCreateContactNova["statusCode"] == 200) {
                    $this->ContactBitrix->MessaggeContact((int)$id, "Mensaje de Sistema: {$resultCreateContactNova['message']} ");
                }
                if (!isset($resultCreateContactNova["statusCode"]) || $resultCreateContactNova["statusCode"] !== 200) {
                    $this->ContactBitrix->MessaggeContact((int)$id, 'Mensaje de Sistema: Error al crear el contacto en NOVA falta un campo');
                    exit();
                }
                break;
        }
    }

    private function buildDataFromBitrix($resultBitrix, $id)
    {
        //dirección del contacto
        $direccionPri = $this->getDireccion($resultBitrix, [
            "UF_CRM_66EED02679DF7",
            "UF_CRM_66EED0268A3A3",
            "UF_CRM_66EED026A23A4",
            "UF_CRM_1738084726"
        ]);

        //dirección del tercero
        $direccionMedico = $this->getDireccion($resultBitrix, [
            "UF_CRM_1730153280",
            "UF_CRM_1730153445",
            "UF_CRM_1730155105",
            "UF_CRM_1738084726"
        ]);

        //dirección del contacto segundaria

        if ($resultBitrix["UF_CRM_1738078190"] !== "") {
            $resultBitrixTercero = $this->ContactBitrix->GETContactBitrix($resultBitrix["UF_CRM_1738078190"]);
        }
        $direccionSecundaria = $this->getDireccion($resultBitrixTercero, [
            "UF_CRM_1732329275",
            "UF_CRM_1732329313",
            "UF_CRM_1732329352",
            "UF_CRM_1738084726"
        ]);
        $receptor = ($resultBitrix["UF_CRM_1733280870"] == 16348)  ? true : false;


        if ($resultBitrix["UF_CRM_1738637256024"] == 95990) {
            $direccionContactoAdicional = $this->getDireccion($resultBitrix, [
                "UF_CRM_1730153280",
                "UF_CRM_1730153445",
                "UF_CRM_1730155105",
                "UF_CRM_1738084726"
            ]);

            //array Con datos de contacto adicional
            $ob = [
                [
                    "codigoRefCliente" => $resultBitrix["ID"],
                    "tipoCliente" => (string)$resultBitrix["TYPE_ID"] == 1 ? "Médico" : "Paciente",
                    "telefono" => $resultBitrix["PHONE"][0]["VALUE"],
                    "correo" => $resultBitrix["EMAIL"][0]["VALUE"],
                    "direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
                    "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2", "contact")),
                    "codigoCentroCosto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491", "contact")),
                    "diasCreditoMax" => (int)$this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053", "contact"),
                    "receptorFEPrincipal" => (bool)$receptor,
                    "codigoPartidaImp" => "1",
                    "montoCreditoMax" => 30,
                    "montoCreditoMin" => 0,
                    "codigoFormaPago" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639", "contact")),
                    "codigoDireccionSec" => $direccionSecundaria['codigo'],
                    "direccionDetalladaSec" => ($resultBitrixTercero["UF_CRM_1732245140"]) ? $resultBitrixTercero["UF_CRM_1732245140"] : '',
                    "estado" => "ACT",
                    "tipoCedulaReceptor" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrixTercero["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                    "cedulaReceptor" => $resultBitrixTercero["UF_CRM_66EED02625C8F"],
                    "correoReceptor" =>  $resultBitrixTercero["EMAIL"][0]["VALUE"],
                    "nombreCompletoReceptor" => (string)$resultBitrixTercero["NAME"] . " " . $resultBitrixTercero["LAST_NAME"],
                    "contacto" => [
                        [
                            "tipoContacto" => (string)$this->helpers->FieldsValue($resultBitrix["UF_CRM_1732333689"], "UF_CRM_1732333689", "contact"),
                            "ocupacion" => (string)$resultBitrix["UF_CRM_1729988613"],
                            "prioridadEmergencia" => true,
                            "codigoTipo" => 0,
                            "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1726926586710"], "UF_CRM_1726926586710", "contact")),
                            "cedula" => $resultBitrix["UF_CRM_1729989673"],
                            "nombreCompleto" => (string)$resultBitrix["UF_CRM_1729989779"],
                            "codigoDireccionPri" => (string)$direccionContactoAdicional['codigo'],
                            "anotaciones" => (string)$resultBitrix["UF_CRM_1726926698338"],
                        ]
                    ],
                    "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                    "cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
                    "nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
                    "codigoDireccionPri" => $direccionPri['codigo'],
                    "anotaciones" => $resultBitrix["UF_CRM_1729989843"]
                ]

            ];
            $this->helpers->LogRegister($ob, "ob");
            return $ob;
        } else {
            //array sin contacto adicional
            $ob = [
                [
                    "codigoRefCliente" => $resultBitrix["ID"],
                    "tipoCliente" => (string)$resultBitrix["TYPE_ID"] == 1 ? "Médico" : "Paciente",
                    "telefono" => $resultBitrix["PHONE"][0]["VALUE"],
                    "correo" => $resultBitrix["EMAIL"][0]["VALUE"],
                    "direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
                    "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2", "contact")),
                    "codigoCentroCosto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491", "contact")),
                    "diasCreditoMax" => (int)$this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053", "contact"),
                    "receptorFEPrincipal" => (bool)$receptor,
                    "codigoPartidaImp" => "1",
                    "montoCreditoMax" => 30,
                    "montoCreditoMin" => 0,
                    "codigoFormaPago" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639", "contact")),
                    "codigoDireccionSec" => $direccionSecundaria['codigo'],
                    "direccionDetalladaSec" => $resultBitrixTercero["UF_CRM_1732245140"],
                    "estado" => "ACT",
                    "tipoCedulaReceptor" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrixTercero["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                    "cedulaReceptor" => $resultBitrixTercero["UF_CRM_66EED02625C8F"],
                    "correoReceptor" =>  $resultBitrixTercero["EMAIL"][0]["VALUE"],
                    "nombreCompletoReceptor" => (string)$resultBitrixTercero["NAME"] . " " . $resultBitrixTercero["LAST_NAME"],
                    "contacto" => [],
                    "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                    "cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
                    "nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
                    "codigoDireccionPri" => $direccionPri['codigo'],
                    "anotaciones" => $resultBitrix["UF_CRM_1729989843"]
                ]

            ];
            $this->helpers->LogRegister($ob, "ob");
            return $ob;
        }
    }
    private function buildDataFromBitrixUpdate($resultBitrix, $id)
    {
        //dirección del contacto
        $direccionPri = $this->getDireccion($resultBitrix, [
            "UF_CRM_66EED02679DF7",
            "UF_CRM_66EED0268A3A3",
            "UF_CRM_66EED026A23A4",
            "UF_CRM_1738084726"
        ]);

        //dirección del tercero
        $direccionMedico = $this->getDireccion($resultBitrix, [
            "UF_CRM_1730153280",
            "UF_CRM_1730153445",
            "UF_CRM_1730155105",
            "UF_CRM_1738084726"
        ]);

        //dirección del contacto segundaria

        $TerceroBitrix = $this->ContactBitrix->GETContactBitrix($id);
        if ($TerceroBitrix["UF_CRM_1738078190"] !== "") {
            $resultBitrixTercero = $this->ContactBitrix->GETContactBitrix($TerceroBitrix["UF_CRM_1738078190"]);
        }
        $direccionSecundaria = $this->getDireccion($resultBitrixTercero, [
            "UF_CRM_1732329275",
            "UF_CRM_1732329313",
            "UF_CRM_1732329352",
            "UF_CRM_1738084726"
        ]);
        $receptor = ($resultBitrix["UF_CRM_1733280870"] == 16348)  ? true : false;


        if ($resultBitrix["UF_CRM_1738637256024"] == 95990) {
            $direccionContactoAdicional = $this->getDireccion($resultBitrix, [
                "UF_CRM_1730153280",
                "UF_CRM_1730153445",
                "UF_CRM_1730155105",
                "UF_CRM_1738084726"
            ]);

            //array Con datos de contacto adicional
            $ob = [

                "codigoRefCliente" => $resultBitrix["ID"],
                "tipoCliente" => (string)$resultBitrix["TYPE_ID"] == 1 ? "Médico" : "Paciente",
                "telefono" => $resultBitrix["PHONE"][0]["VALUE"],
                "correo" => $resultBitrix["EMAIL"][0]["VALUE"],
                "direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
                "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2", "contact")),
                "codigoCentroCosto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491", "contact")),
                "diasCreditoMax" => (int)$this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053", "contact"),
                "receptorFEPrincipal" => (bool)$receptor,
                "codigoPartidaImp" => "1",
                "montoCreditoMax" => 30,
                "montoCreditoMin" => 0,
                "codigoFormaPago" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639", "contact")),
                "codigoDireccionSec" => $direccionSecundaria['codigo'],
                "direccionDetalladaSec" => $resultBitrixTercero["UF_CRM_1732245140"],
                "estado" => "ACT",
                "tipoCedulaReceptor" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrixTercero["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                "cedulaReceptor" => $resultBitrixTercero["UF_CRM_66EED02625C8F"],
                "correoReceptor" =>  $resultBitrixTercero["EMAIL"][0]["VALUE"],
                "nombreCompletoReceptor" => (string)$resultBitrixTercero["NAME"] . " " . $resultBitrixTercero["LAST_NAME"],
                "contacto" => [
                    [
                        "tipoContacto" => (string)$this->helpers->FieldsValue($resultBitrix["UF_CRM_1732333689"], "UF_CRM_1732333689", "contact"),
                        "ocupacion" => (string)$resultBitrix["UF_CRM_1729988613"],
                        "prioridadEmergencia" => true,
                        "codigoTipo" => 0,
                        "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1726926586710"], "UF_CRM_1726926586710", "contact")),
                        "cedula" => $resultBitrix["UF_CRM_1729989673"],
                        "nombreCompleto" => (string)$resultBitrix["UF_CRM_1729989779"],
                        "codigoDireccionPri" => (string)$direccionContactoAdicional['codigo'],
                        "anotaciones" => (string)$resultBitrix["UF_CRM_1726926698338"],
                    ]
                ],
                "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                "cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
                "nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
                "codigoDireccionPri" => $direccionPri['codigo'],
                "anotaciones" => $resultBitrix["UF_CRM_1729989843"]


            ];
            $this->helpers->LogRegister($ob, "actualizar");
            return $ob;
        } else {
            //array sin contacto adicional
            $ob = [

                "codigoRefCliente" => $resultBitrix["ID"],
                "tipoCliente" => (string)$resultBitrix["TYPE_ID"] == 1 ? "Médico" : "Paciente",
                "telefono" => $resultBitrix["PHONE"][0]["VALUE"],
                "correo" => $resultBitrix["EMAIL"][0]["VALUE"],
                "direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
                "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2", "contact")),
                "codigoCentroCosto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491", "contact")),
                "diasCreditoMax" => (int)$this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053", "contact"),
                "receptorFEPrincipal" => (bool)$receptor,
                "codigoPartidaImp" => "1",
                "montoCreditoMax" => 30,
                "montoCreditoMin" => 0,
                "codigoFormaPago" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639", "contact")),
                "codigoDireccionSec" => $direccionSecundaria['codigo'],
                "direccionDetalladaSec" => $resultBitrixTercero["UF_CRM_1732245140"],
                "estado" => "ACT",
                "tipoCedulaReceptor" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrixTercero["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                "cedulaReceptor" => $resultBitrixTercero["UF_CRM_66EED02625C8F"],
                "correoReceptor" =>  $resultBitrixTercero["EMAIL"][0]["VALUE"],
                "nombreCompletoReceptor" => (string)$resultBitrixTercero["NAME"] . " " . $resultBitrixTercero["LAST_NAME"],
                "contacto" => [],
                "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B", "contact")),
                "cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
                "nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
                "codigoDireccionPri" => $direccionPri['codigo'],
                "anotaciones" => $resultBitrix["UF_CRM_1729989843"]
            ];
            $this->helpers->LogRegister($ob, "actualizar");
            return $ob;
        }
    }
    private function ConsultarDatosReceptor($id)
    {
        $this->ContactBitrix->GETContactBitrix($id);
    }
    private function getDireccion($resultBitrix, $fields)
    {

        $values = array_map(function ($field) use ($resultBitrix) {
            $reto = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix[$field], $field, "contact"));
            return $reto;
        }, $fields);

        return [
            'codigo' => implode("-", $values),
        ];
    }
}
