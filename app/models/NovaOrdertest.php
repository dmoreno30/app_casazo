<?php

namespace App\Models;

use App\Models\Auth;
use App\Traits\CurlAPI;

class NovaOrdertest
{
    use CurlAPI;


    public function findOrder($id, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_ORDER') . "?CodRefExterna=" . $id;
        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateOrder($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_ORDER');
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function UpdateOrder($data, $companyCode,$id)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_API') . getenv('ENDPONT_ORDER'). "/" . $id;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
