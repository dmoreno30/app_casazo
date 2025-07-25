<?php

namespace App\Models;

use App\Core\crest;

/**
 * Base Contact Bitrix
 * ---
 * The base model provides a space to set atrributes
 * that are common to all models
 */
class DealBitrix
{


    public function GETDealtBitrix($id)
    {

        $result = crest::call("crm.deal.get", [
            "id" => $id
        ]);
        return $result["result"];
    }
    public function GETCurrencyID($IDcurrency)
    {

        $result = crest::call("crm.currency.get", [
            "id" => "$IDcurrency"
        ]);
        return $result["result"];
    }

    public function dataFields(string $FIELD_NAME)
    {

        $result = CRest::call(
            "crm.deal.userfield.list",
            [
                "FILTER[FIELD_NAME]" => $FIELD_NAME,
            ]
        );
        return $result["result"][0]["LIST"];
    }

    public function DealSetProducts(string $id)
    {

        $result = CRest::call(
            "crm.deal.productrows.get",
            [
                "id" => $id,
            ]
        );
        return $result;
    }
    public function MessaggeDeal($id, $mensaje)
    {
        $result = CRest::call(
            'crm.timeline.comment.add',
            [
                'fields' => [
                    'ENTITY_ID' => $id,
                    'ENTITY_TYPE' => 'deal',
                    'COMMENT' => $mensaje,
                    'AUTHOR_ID' => 5
                ]
            ]
        );
        return $result;
    }
}
