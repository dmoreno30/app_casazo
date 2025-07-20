<?php

namespace App\Models;

use App\Core\CRest;

/**
 * Base Model
 * ---
 * The base model provides a space to set atrributes
 * that are common to all models
 */
class Bitrix extends \Leaf\Model
{
    public function AddEvent()
    {
        CRest::call("add.event.calendar", []);
    }
}
