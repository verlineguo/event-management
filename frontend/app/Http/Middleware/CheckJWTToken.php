<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckJWTToken
{
    private $apiUrl = 'http://localhost:5000/api';

    public function handle($request, Closure $next)
{
    $token = session('jwt_token');
    
    Log::info('JWT from session:', ['token_exists' => !empty($token), 'token_length' => $token ? strlen($token) : 0]);
    
    if (empty($token)) {
        Log::warning('JWT token missing in session');
        return redirect('/login')->withErrors(['message' => 'Please login to continue']);
    }
    
    try {
        // Verifikasi token ke backend Node.js
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($this->apiUrl.'/verify-token');
        
        if ($response->successful()) {
            Log::info('Token verification successful');
            return $next($request);
        } else {
            Log::warning('Token verification failed', ['status' => $response->status()]);
            session()->forget(['jwt_token', 'user_email', 'user_role_id']);
            return redirect('/login')->withErrors(['message' => 'Session expired or invalid']);
        }
    } catch (\Exception $e) {
        Log::error('Exception during token verification', ['message' => $e->getMessage()]);
        return redirect('/login')->withErrors(['message' => 'Authentication service unavailable']);
    }
}
}