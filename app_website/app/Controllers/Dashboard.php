<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        if($this->isUserLoggedIn()){
            $this->pageName('Dashboard');
			$this->pageTitle('Dashboard');
			return view('dashboard', $this->viewdata);
        }else{
            return redirect()->route('login');
        }
    }
}
