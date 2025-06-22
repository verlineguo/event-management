<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminMainController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/dashboard/admin-dashboard');
            if ($response->successful()) {
                $dashboardData = $response->json();
                return view('admin.dashboard', compact('dashboardData'));
            }
            
            // Jika gagal ambil data, tetap tampilkan dashboard dengan data kosong
            $dashboardData = [
                'stats' => [
                    'totalUsers' => 0,
                    'activeUsers' => 0,
                    'inactiveUsers' => 0,
                    'totalRoles' => 0,
                    'activeRoles' => 0,
                    'totalCategories' => 0,
                    'activeCategories' => 0,
                    'userGrowthPercentage' => 0
                ],
                'charts' => [
                    'monthlyUserRegistrations' => [],
                    'usersByRole' => [],
                    'userStatusStats' => []
                ],
                'recentActivity' => [
                    'recentUsers' => []
                ]
            ];
            
            return view('admin.dashboard', compact('dashboardData'))
                ->with('warning', 'Gagal mengambil data statistik');
                
        } catch (\Exception $e) {
            // Handle error dan tetap tampilkan dashboard
            $dashboardData = [
                'stats' => [
                    'totalUsers' => 0,
                    'activeUsers' => 0,
                    'inactiveUsers' => 0,
                    'totalRoles' => 0,
                    'activeRoles' => 0,
                    'totalCategories' => 0,
                    'activeCategories' => 0,
                    'userGrowthPercentage' => 0
                ],
                'charts' => [
                    'monthlyUserRegistrations' => [],
                    'usersByRole' => [],
                    'userStatusStats' => []
                ],
                'recentActivity' => [
                    'recentUsers' => []
                ]
            ];
            
            return view('admin.dashboard', compact('dashboardData'))
                ->with('error', 'Terjadi kesalahan saat mengambil data dashboard');
        }
    }
}
