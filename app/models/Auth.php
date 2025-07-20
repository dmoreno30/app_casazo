<?php

namespace App\Models;

use App\Traits\CurlAPI;

class Auth
{

    use CurlAPI;

    private static $instance = null;
    private $token;

    // Constructor privado para evitar instanciación directa
    private function __construct() {}

    // Método para obtener la instancia única de Auth
    public static function getInstance(): Auth
    {
        if (self::$instance === null) {
            self::$instance = new Auth();
        }

        return self::$instance;
    }
    public function token($companyCode)
    {
        if (!$this->token) {
            $this->token = $this->GetToken($companyCode);
        }

        return $this->token;
    }
    private function GetToken($companyCode): string|array
    {

        $data = [
            "authByCompanyTIN" => false,
            "companyCode" => $companyCode,
            "username" => "admin",
            "password" => "Admin1234",
            "audience" => "Bitrix24"
        ];
        $apiUrl = getenv('URL_TOKEN') . getenv('ENDPOINT_LOGIN');
        $response = $this->fetchToken($data, $apiUrl);
        // Decodifica la respuesta
        $responseData = json_decode($response, true);
        return $responseData["token"];
    }
}
