<?php

namespace App\helpers;

use App\Models\ContactBitrix;

class Auxhelpers
{
    private $Bitrix;
    public function LogRegister($arr, $title = '')
    {
        try {
            $log = "\n------------------------\n";
            $log .= date("Y.m.d G:i:s") . "\n";
            $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
            $log .= print_r($arr, 1);
            $log .= "\n------------------------\n";
            $filePath = __DIR__ . '/log.log';  // Usa __DIR__ para evitar problemas de ruta

            if (file_put_contents($filePath, $log, FILE_APPEND) === false) {
                throw new \Exception("No se pudo escribir en el archivo de log: $filePath");
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());  // Registrar el error en el log de PHP
        }

        return true;
    }
    public function FormatPrint($arr)
    {
        $info = "<pre>";
        $info .= print_r($arr);
        $info .= "</pre>";
        return $info;
    }
    public function log_error($message)
    {
        error_log("[" . date("Y-m-d H:i:s") . "] " . __FUNCTION__ . ": " . $message, 3, "error.log");
    }
    public function FieldsValue($FIELD_ID, string $FIELD_NAME, $entity)
    {

        $this->Bitrix = new ContactBitrix();
        $fielData = $this->Bitrix->dataFields($FIELD_NAME, $entity);
        foreach ($fielData as $data) {
            if ($data["ID"] == $FIELD_ID) {
                return $data["VALUE"];
            }
        }
    }
    public function extractValue($string)
    {
        $valor = explode('|', $string);

        if (isset($valor[1])) {
            $resultado = trim($valor[1]);
            return $resultado;
        }
    }
    public function extractMonto($monto)
    {
        $valor = explode('|', $monto);

        if (isset($valor[0])) {
            $resultado = trim($valor[0]);
            return $resultado;
        }
    }
    public function FormatDate($nuevafecha)
    {
        $fecha = new \DateTime($nuevafecha);

        // Formatear la fecha al formato deseado
        $fechaFormateada = $fecha->format('Y-m-d');

        // Mostrar el resultado
        return $fechaFormateada;
    }
}
