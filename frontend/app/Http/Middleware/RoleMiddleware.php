<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!session('jwt_token')) {
            return redirect('/login');
        }

        $userRole = session('user_role');
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}