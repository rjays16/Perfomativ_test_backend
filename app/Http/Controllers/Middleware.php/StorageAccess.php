<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StorageAccess
{
    public function handle(Request $request, Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        return $next($request);
    }
}