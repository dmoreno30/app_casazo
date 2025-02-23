<?php

namespace App\Models;

use App\Models\Auth;
use App\Traits\CurlAPI;

class NovaContactTest
{
    use CurlAPI;


    public function findContact($cedula, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_CONTACT'). "/?Cedula=" . $cedula;
        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateContact($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_CONTACT');
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function UpdateContact($data, $companyCode,$id)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_CONTACT'). "/" . $id;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
