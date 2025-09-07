<?php

namespace App\Controllers;

class Errors extends BaseController
{
    public function show404(): string
    {
        return view('errors/html/error_404', array('message' => 'page not found'));
    }
}
