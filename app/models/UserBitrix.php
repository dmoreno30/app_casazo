<?php

namespace App\Models;

use App\Core\crest;

/**
 * Base Contact Bitrix
 * ---
 * The base model provides a space to set atrributes
 * that are common to all models
 */
class UserBitrix
{


    public function GETUsertBitrix($id)
    {

        $result = crest::call("user.get", [
            "id" => $id
        ]);
        return $result["result"];
    }
}
