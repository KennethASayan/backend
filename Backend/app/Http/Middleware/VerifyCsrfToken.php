<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //  'https://r10api.denr10.com.ph/api/*',
        'sanctum/csrf-cookie',
            'dms/*', // Temporarily exclude all DMS routes for testing
    '/dms/login',
    '/dms/logout',
    '/dms/users',
    'dms/user'
];
}
