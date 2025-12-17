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
        // You can use an env variable, database flag, or config file
        if (env('MAINTENANCE_MODE', false) === true) {

            return service('response')
                ->setStatusCode(503)
                ->setBody(view('maintenance'));
        }

        return; // Continue as normal
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Not needed
    }
}
