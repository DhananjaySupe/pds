<?php
	
	namespace App\Filters;
	
	use CodeIgniter\HTTP\RequestInterface;
	use CodeIgniter\HTTP\ResponseInterface;
	use CodeIgniter\Filters\FilterInterface;
	
	class MaintenanceMode implements FilterInterface
	{
		public function before(RequestInterface $request, $arguments = null)
		{
			helper('app');
			if (env('MAINTENANCE_MODE', false) === true) {
				$response = service('response');
                return $response->setStatusCode(503)
				->setJSON([
				"success" => false,
				"message" => "Under Maintenance",
				"data" => (object)[],
				"version" => [
				"admin_version" => "0",
				"force_update" => "0",
				"admin_app_url" => "******"
				]
				]);
			}
			
			return; // Continue as normal
		}
		
		public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
		{
			// Not needed
		}
	}
