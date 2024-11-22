<?php

namespace App\Models;

/**
 * Nova Model
 * ---
 * The Nova model provides a space to set atrributes
 * that are common to all models
 */
class NovaID
{
    private $login = 'api/Auth/login';
    private $EnpointCustomer = "/api/quote";

    private function CurlPost($arr, $apiUrl, $token, $method)
    {
        $ch = curl_init();

        // Configura las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));

        // Ejecuta la solicitud
        $response = curl_exec($ch);

        // Maneja errores de cURL
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['error' => true, 'message' => $error_msg];
        }

        // Cierra la conexión cURL
        curl_close($ch);

        // Decodifica la respuesta
        $responseData = json_decode($response, true);
        return $responseData;
    }
    private function CurlGet($apiUrl, $token)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token, // Aquí pasas el token en el encabezado
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['error' => true, 'message' => $error_msg];
        }

        curl_close($ch);

        $responseData = json_decode($response, associative: true);

        return $responseData;
    }
    private function fetchToken($companyCode): string|array
    {

        $data = [
            "authByCompanyTIN" => false,
            "companyCode" => $companyCode,
            "username" => "admin",
            "password" => "Admin1234",
            "audience" => "Bitrix24"
        ];
        $ch = curl_init();

        $url = getenv('URL_TOKEN') . $this->login;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Ejecuta la solicitud
        $response = curl_exec($ch);

        // Maneja errores de cURL
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['error' => true, 'message' => $error_msg];
        }

        // Cierra la conexión cURL
        curl_close($ch);

        // Decodifica la respuesta
        $responseData = json_decode($response, true);
        return $responseData["token"];
    }
    public function findQuote($id, $companyCode)
    {

        $token = $this->fetchToken($companyCode);

        $url = getenv('URL_CUSTOMER') . $this->EnpointCustomer . "/?CodRefExterna=" . $id;
        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateQuoteID($data, $companyCode)
    {
        $token = $this->fetchToken($companyCode);
        $url = getenv('URL_CUSTOMER') . $this->EnpointCustomer;
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function updateContact($data, $companyCode)
    {

        $token = $this->fetchToken($companyCode);
        $url = getenv('URL_CUSTOMER') . $this->EnpointCustomer;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
