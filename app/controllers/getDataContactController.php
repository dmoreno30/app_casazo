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

        $resultBitrix = $this->ContactBitrix->GETContactBitrix($datos["id"]);
        $this->helpers->LogRegister($resultBitrix, "resultBitrix");

        $sucursal = $this->helpers->extractValue($datos["Venta"]);
        $cedula = $resultBitrix["UF_CRM_66EED02625C8F"];

        print_r($datos);

        $resultNova = $this->Nova->findContact((string)$cedula, $sucursal);

        $data = $this->buildDataFromBitrix($resultBitrix);
        $data2 = json_encode($data);
        $this->helpers->LogRegister($data, "data");
        print_r($data2);
        $this->helpers->LogRegister($data2, "data2");

        //el contacto NO existe en nova por lo que puedo crearlo allí
        if ($resultNova["statusCode"] == 400) {
            $resultCreateContactNova = $this->Nova->CreateContact($data, $sucursal);
            print_r($resultCreateContactNova);
            $this->helpers->LogRegister($resultCreateContactNova, "resultCreateContactNova");

            if (!isset($resultCreateContactNova["status"]) || $resultCreateContactNova["status"] !== 200) {
                $this->ContactBitrix->MessaggeContact((int)$datos["id"], 'Mensaje de Sistema: Error al crear el contacto en NOVA falta el campo');
            } else {
                $this->ContactBitrix->MessaggeContact((int)$datos["id"], 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Contacto creado en Nova');
            }
        }
        //el contacto SI existe en nova por lo que puedo actualizarlo allí
        if ($resultNova["statusCode"] == 200) {
            (int)$id = $datos["id"];
            $result = $this->Nova->updateContact($data, $sucursal);
            $this->helpers->LogRegister($result, "result actualización");
            $this->ContactBitrix->MessaggeContact((int)$id, 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cliente existente, información actualizada');
        }
    }

    private function buildDataFromBitrix($resultBitrix)
    {
        $direccionPri = $this->getDireccion($resultBitrix, [
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
        $direccionSecundaria = $this->getDireccion($resultBitrix, [
            "UF_CRM_1732329275",
            "UF_CRM_1732329313",
            "UF_CRM_1732329352",
            "UF_CRM_1732329400"
        ]);
        $receptor = ($resultBitrix["UF_CRM_1729999951"] == 1)  ? true : false;



        $nombreContacto = $resultBitrix["UF_CRM_1726926647122"] . $resultBitrix["UF_CRM_1726926654666"];




        return [
            [
                "codigoRefCliente" => $resultBitrix["ID"],
                "tipoCliente" => $resultBitrix["TYPE_ID"] == 1 ? "Medico" : "Paciente",
                "tipoCedula" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B")),
                "cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
                "nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
                "telefono" => $resultBitrix["PHONE"][0]["VALUE"],
                "correo" => $resultBitrix["EMAIL"][0]["VALUE"],
                "codigoDireccionPri" => $direccionPri['codigo'],
                "direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
                "codigoSucursal" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2")),
                "diasCreditoMax" => (int)$this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053"),
                "montoCreditoMax" => 30,
                "montoCreditoMin" => 0,
                "codigoCentroCosto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491")),
                "estado" => "ACT",
                "codigoFormaPago" => (int)$this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639")),
                "contacto" => [
                    [
                        "tipoContacto" => $this->helpers->FieldsValue($resultBitrix["UF_CRM_1732333689"], "UF_CRM_1732333689"),
                        "nombreContacto" => (string)$nombreContacto,
                        "tipoCedulaContacto" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1726926586710"], "UF_CRM_1726926586710")),
                        "cedulaContacto" => $resultBitrix["UF_CRM_1726926537782"],
                        "codDireccionContacto" => $direccionMedico['codigo'],
                        "ocupacionContacto" => $resultBitrix["UF_CRM_1729988613"],
                        "comentarioContacto" => $resultBitrix["UF_CRM_1726926698338"],
                        "contactoPrioridad" => false
                    ]
                ],
                "receptorFEPrincipal" => (bool)$receptor,
                "codigoPartidaImp" => "1",
                "tipoCedulaReceptor" => $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729989153"], "UF_CRM_1729989153")),
                "cedulaReceptor" => $resultBitrix["UF_CRM_1729989673"],
                "nombreCompletoReceptor" => $resultBitrix["UF_CRM_1729989779"],
                "correoReceptor" => $resultBitrix["UF_CRM_1729989815"],
                "direccionDetalladaSec" => $resultBitrix["UF_CRM_1732245140"],
                "codigoDireccionSec" => $direccionSecundaria['codigo'],
                "anotaciones" => $resultBitrix["UF_CRM_1729989843"]
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
