<?php

namespace App\Controllers;

use App\Models\Bitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;

/**
 * This is the base controller for your Leaf MVC Project.
 * You can initialize packages or define methods here to use
 * them across all your other controllers which extend this one.
 */
class GetDataEventController extends \Leaf\Controller
{
    public $bitrix;
    public $helpers;
    public function __construct()
    {
        $this->bitrix = new Bitrix();
        $this->helpers = new Auxhelpers();
        parent::__construct();
        $this->request = new Request;
    }

    public function index()
    {
        //$data = $request->getBody();
        $iniciocita = $this->request->body("iniciocita");
        $fincita = $this->request->body("fincita");
        $sucursal = $this->request->body("sucursal");
    }
}
