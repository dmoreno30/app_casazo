<?php

namespace App\Controllers;

use App\Models\ContactBitrix;
use App\helpers\Auxhelpers;
use Leaf\Http\Request;

/**
 * This is the base controller for your Leaf MVC Project.
 * You can initialize packages or define methods here to use
 * them across all your other controllers which extend this one.
 */
class TokenNovaController extends \Leaf\Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {}
}
