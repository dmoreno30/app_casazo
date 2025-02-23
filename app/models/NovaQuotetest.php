<?php

namespace App\Models;

use App\Models\Auth;
use App\Traits\CurlAPI;

class NovaQuotetest
{
    use CurlAPI;
    private $EnpointQuote = "/api/quote";



    public function findQuote($id, $companyCode)
    {

        $token = Auth::getInstance()->token($companyCode);

        $url = getenv('URL_QUOTE') . $this->EnpointQuote . "?CodRefExterna=" . $id;

        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateQuoteID($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . $this->EnpointQuote;
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function UpdateQuoteID($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . $this->EnpointQuote;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
