/* public function testContact()
{
$id = $this->HttpRequest->body("id");
$result = $this->Nova->findContact($id["id"], 58);

$resultBitrix = $this->ContactBitrix->GETContactBitrix($id["id"]);
$data = $this->buildDataFromBitrix($resultBitrix);

if (empty($result)) {
$this->helpers->LogRegister($data);
$result = $this->Nova->CreateContact($data, 58);
print_r($result);
} else {
$this->helpers->LogRegister(count($result));
print_r("existe");
// Actualiza el contacto existente utilizando el $data
$this->Nova->updateContact($result['id'], $data); // Asegúrate de que el ID del contacto correcto se pasa aquí
}
} */

/* public function index()

{
$datos = $this->HttpRequest->body("Cedula");

(int)$cedula = $datos["Cedula"];
$resultNova = $this->Nova->findContact((int)$cedula, 58);

if ($resultNova == "") {
$resultBitrix = $this->ContactBitrix->GETContactBitrix($datos["id"]);
$sucursal = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E9847FD2"], "UF_CRM_66F44E9847FD2"));
//dirección paciente
$Provincia = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED02679DF7"], "UF_CRM_66EED02679DF7"));
$canton = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0268A3A3"], "UF_CRM_66EED0268A3A3"));
$distrito = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED026A23A4"], "UF_CRM_66EED026A23A4"));
$barrio = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED026C5E1E"], "UF_CRM_66EED026C5E1E"));
$codDireccionPri = $Provincia . "-" . $canton . "-" . $distrito . "-" . $barrio;
//dirección medico
$ProvinciaMedico = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1730153280"], "UF_CRM_1730153280"));
$cantonMedico = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1730153445"], "UF_CRM_1730153445"));
$distritoMedico = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1730155105"], "UF_CRM_1730155105"));
$barrioMedico = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1730155278"], "UF_CRM_1730155278"));
$codDireccionMedico = $ProvinciaMedico . "-" . $cantonMedico . "-" . $distritoMedico . "-" . $barrioMedico;

$tCedula = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_66F44E986011B"], "UF_CRM_66F44E986011B"));

$tCedulaMedico = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1726926586710"], "UF_CRM_1726926586710"));
$centroCostos = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952491"], "UF_CRM_1729952491"));
$formaPago = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729952639"], "UF_CRM_1729952639"));
$tCedulaReceptor = $this->helpers->extractValue($this->helpers->FieldsValue($resultBitrix["UF_CRM_1729989153"], "UF_CRM_1729989153"));
$diasCredMax = $this->helpers->FieldsValue($resultBitrix["UF_CRM_66EED0266C053"], "UF_CRM_66EED0266C053");


$tipo = $resultBitrix["TYPE_ID"] == 1 ? "Médico" : "Paciente";
$receptor = $resultBitrix["UF_CRM_1729999951"] == 1 ? true : false;
$data = [
[
"codigoRefCliente" => $resultBitrix["ID"],
"tipoCliente" => $tipo,
"tipoCedula" => $tCedula,
"cedula" => $resultBitrix["UF_CRM_66EED02625C8F"],
"nombreCompleto" => $resultBitrix["NAME"] . " " . $resultBitrix["LAST_NAME"],
"telefono" => $resultBitrix["PHONE"][0]["VALUE"],
"correo" => $resultBitrix["EMAIL"][0]["VALUE"],
"codigoDireccionPri" => $codDireccionPri,
"codigoDireccionSec" => "",
"direccionDetalladaPri" => $resultBitrix["UF_CRM_66EED027B1CF0"],
"direccionDetalladaSec" => "",
"codigoSucursal" => (int)$sucursal,
"diasCreditoMax" => (int)$diasCredMax,
"montoCreditoMax" => 30,
"montoCreditoMin" => 0,
"codigoCentroCosto" => $centroCostos,
"estado" => "ACT",
"codigoFormaPago" => (int)$formaPago,
"contacto" => [
[
"tipoContacto" => "Médico",
"nombreCompleto" => $resultBitrix["UF_CRM_1726926647122"] . $resultBitrix["UF_CRM_1726926654666"],
"tipoCedula" => $tCedulaMedico,
"cedula" => $resultBitrix["UF_CRM_1726926537782"],
"codigoDireccionPri" => $codDireccionMedico,
"ocupacion" => $resultBitrix["UF_CRM_1729988613"],
"prioridadEmergencia" => false,
"comentario" => $resultBitrix["UF_CRM_1726926698338"]
]
],
"receptorFEPrincipal" => true,
"codigoPartidaImp" => "1",
"tipoCedulaReceptor" => $tCedulaReceptor,
"cedulaReceptor" => $resultBitrix["UF_CRM_1729989673"],
"nombreCompletoReceptor" => $resultBitrix["UF_CRM_1729989779"],
"correoReceptor" => $resultBitrix["UF_CRM_1729989815"],
"anotaciones" => $resultBitrix["UF_CRM_1729989843"]
]
];
$resultCreateContactNova = $this->Nova->CreateContact($data, 58);
$this->helpers->LogRegister($resultCreateContactNova, "resultCreateContactNova");
$this->ContactBitrix->MessaggeContact((int)$datos["id"], 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Contacto creado en Nova');
} else {
(int)$id = $datos["id"];
$this->ContactBitrix->MessaggeContact((int)$id, 'Mensaje de Sistema: Información enviada a NOVA de manera correcta - Cliente existente, información actualizada');
//$result = $this->Nova->updateContact(123, $data);
}
} */