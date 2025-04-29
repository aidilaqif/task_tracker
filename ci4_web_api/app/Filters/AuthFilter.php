<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Check if user is logged in
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            // If this is an AJAX request, return 401 Unauthorized
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'status' => false,
                        'msg' => 'Authentication required'
                    ]);
            }
            
            // Remember the URL the user is trying to access
            session()->set('redirect_url', current_url());
            
            // Redirect to login page
            return redirect()->to('/login');
        }
        
        // Check if user is admin (when required)
        if (in_array('admin', $arguments ?? []) && session()->get('role') !== 'admin') {
            // If this is an AJAX request, return 403 Forbidden
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'status' => false,
                        'msg' => 'Access denied'
                    ]);
            }
            
            // Redirect to dashboard with access denied message
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient privileges.');
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}