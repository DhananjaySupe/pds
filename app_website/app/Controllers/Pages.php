<?php namespace App\Controllers;

	class Pages extends BaseController
	{
		public function ping()
		{
			return $this->response->setStatusCode(200)->setBody('pong');
		}
	}