<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

helper('jwt_helper');

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine("Authorization");
        if (!$authHeader) {
            return Services::response()->setJSON(['error' => 'Authorization header missing'])->setStatusCode(401);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = decodeJWT($token);

        if (!$decoded) {
            return Services::response()->setJSON(['error' => 'Invalid token'])->setStatusCode(401);
        }

        // You can add additional checks here, such as checking user roles
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
