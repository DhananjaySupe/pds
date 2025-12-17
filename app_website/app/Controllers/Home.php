<?php namespace App\Controllers;

	class Home extends BaseController
	{
		public function index()
		{
			$this->pageName('Home');
			$this->pageTitle('Home');
			return view('home',$this->viewdata);
		}
	}