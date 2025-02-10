<?php

namespace App\Models;

use App\Core\crest;
use App\helpers\Auxhelpers;

/**
 * Base Contact Bitrix
 * ---
 * The base model provides a space to set atrributes
 * that are common to all models
 */
class ProductBitrix24
{
    public $helpers;
    public function __construct()
    {
        $this->helpers = new Auxhelpers();
    }

    public function getInfoProductModel($id, $name,)
    {
        $result = CRest::call(
            "catalog.product.offer.list",
            [
                "id" => $id,
                "filter[iblockId]" => 16,
                "filter[name]" => $name,
                "select[0]" => "id",
                "select[1]" => "iblockId",
                "select[2]" => "name",
                "select[3]" => "parentId",
            ]
        );
        return $result;
    }

    public function getStoreProduct($idVariant)
    {
        try {
            $result = CRest::call(
                "catalog.storeproduct.list",
                [
                    "filter[productId]" => $idVariant,
                ]
            );

            // Verifica si la clave ["result"]["storeProducts"]["storeId"] existe antes de acceder
            if (isset($result["result"]["storeProducts"]["storeId"])) {
                return $result["result"]["storeProducts"]["storeId"];
            } else {
                // Si no existe, puedes retornar un valor por defecto o manejar el error de otra manera
                return null; // O algún valor predeterminado adecuado
            }
        } catch (\Throwable $th) {
            // En caso de un error, puedes manejarlo apropiadamente
            $this->helpers->LogRegister($th->getMessage(), "error");
            return null; // O algún valor predeterminado en caso de error
        }
    }


    private function getInfoStore($id)
    {
        $result = CRest::call(
            "catalog.store.get",
            [
                "id" => $id,
            ]
        );
        return $result["result"];
    }
}
