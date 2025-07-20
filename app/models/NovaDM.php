<?php

namespace App\Models;

use App\Models\Auth;
use App\Traits\CurlAPI;

class NovaIDtest
{
    use CurlAPI;
    private $EnpointOrder = "/api/order";

    public function findQuote($id, $companyCode)
    {

        $token = Auth::getInstance()->token($companyCode);

        $url = getenv('URL_QUOTE') . $this->EnpointOrder . "?CodRefExterna=" . $id;

        $result = $this->CurlGet($url,  $token);
        return $result;
    }
    public function CreateQuoteID($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . $this->EnpointOrder;
        $result = $this->CurlPost($data, $url, $token, "POST");
        return $result;
    }
    public function UpdateQuoteID($data, $companyCode)
    {
        $token = Auth::getInstance()->token($companyCode);
        $url = getenv('URL_QUOTE') . $this->EnpointOrder;
        $result = $this->CurlPost($data, $url, $token, "PUT");
        return $result;
    }
}
