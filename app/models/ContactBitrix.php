<?php

namespace App\Models;

use App\Core\crest;

/**
 * Base Contact Bitrix
 * ---
 * The base model provides a space to set atrributes
 * that are common to all models
 */
class ContactBitrix
{


    public function GETContactBitrix($id)
    {

        $result = crest::call("crm.contact.get", [
            "id" => $id
        ]);
        return $result["result"];
    }

    public function ContactBitrix($entity, $method, $arr)
    {

        crest::call("crm." . $entity . "." . $method, $arr);
    }
    public function dataFields(string $FIELD_NAME)
    {

        $result = CRest::call(
            "crm.contact.userfield.list",
            [
                "FILTER[FIELD_NAME]" => $FIELD_NAME,
            ]
        );
        return $result["result"][0]["LIST"];
    }

    public function MessaggeContact($id, $mensaje)
    {
        $result = CRest::call(
            'crm.timeline.comment.add',
            [
                'fields' => [
                    'ENTITY_ID' => $id,
                    'ENTITY_TYPE' => 'contact',
                    'COMMENT' => $mensaje,
                    'AUTHOR_ID' => 5
                ]
            ]
        );
        return $result;
    }
}
