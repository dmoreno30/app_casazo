<?php

namespace App\Models;

use App\Models\Auth;
use App\Traits\CurlAPI;

class NovaQuotetest
{
    use CurlAPI;


    public function findQuote($id, $companyCode)
    {

        $token = Auth::getInstance()->token($companyCode);

        $url = getenv('URL_QUOTE') . getenv('ENDPONT_QUOTE') . "?CodRefExterna=" . $id;

        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateQuote($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . getenv('ENDPONT_QUOTE');
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function UpdateQuote($data, $companyCode,$id)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . getenv('ENDPONT_QUOTE'). "/" . $id;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
