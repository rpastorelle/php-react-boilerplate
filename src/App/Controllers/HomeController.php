<?php
namespace App\Controllers;

use Core\BaseController;

class HomeController extends BaseController
{
    /**
     * GET /
     */
    public function index()
    {
        return $this->view('home.html');
    }
}
