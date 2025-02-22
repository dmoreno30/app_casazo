<?php

namespace App\Controllers;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\helpers\Auxhelpers;
use Leaf\Http\Request;
use App\Models\ProductBitrix24;

/**
 * Creación de cotizaciones en NOVA desde master Dental.
 */
class GetProductsBitrix extends \Leaf\Controller
{
    public $ProductBitrix24;
    public $helpers;
    public $HttpRequest;

    public function __construct()
    {
        parent::__construct();
        $this->ProductBitrix24 = new ProductBitrix24();
        $this->helpers = new Auxhelpers();
        $this->HttpRequest = new Request;
    }

    public function getInfoProduct($idProduct, $name, $lineaproduct)
    {
        /* $resultProduct = $this->ProductBitrix24->getInfoProductModel($idProduct, $name);

        //obtengo el ID de la variante
        $dataStore =  $this->getStore($resultProduct["result"]["offers"][$lineaproduct]["id"]);
        $this->helpers->LogRegister($resultProduct, "resultProduct");
        return $dataStore; */
    }
    public function getParentID($idProduct)
    {
        /*  try {
            $resultParent =   $this->ProductBitrix24->getParent($idProduct);

            // Verifica si la clave ["result"]["storeProducts"]["storeId"] existe antes de acceder
            if (isset($resultParent["result"]["product"]["parentId"])) {
                return $result["result"]["product"]["parentId"]["value"];
            } else {
                // Si no existe, puedes retornar un valor por defecto o manejar el error de otra manera
                return null; // O algún valor predeterminado adecuado
            }
        } catch (\Throwable $th) {
            // En caso de un error, puedes manejarlo apropiadamente
            $this->helpers->LogRegister($th->getMessage(), "error");
            return null; // O algún valor predeterminado en caso de error
        } */
        $resultParent =   $this->ProductBitrix24->getParent($idProduct);
        $this->helpers->LogRegister($resultParent, "resultParent");
        return $resultParent;
    }
    public function getStore($idStore)
    {

        switch ($idStore) {
            case 8:
                return "BPR";
            case 1:
                return "B01";
        }
    }
}
