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
         'https://r10api.denr10.com.ph/api/*',
    '/login',
    '/logout',
    '/user',
    '/dms/login', // <-- Add this line
];
}
