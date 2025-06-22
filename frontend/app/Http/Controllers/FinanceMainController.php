<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FinanceMainController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        try {
            $response = Http::withToken(session('jwt_token'))
                          ->get($this->apiUrl . '/dashboard/finance-dashboard');
            
            if ($response->successful()) {
                $dashboardData = $response->json();
                return view('finance.dashboard', compact('dashboardData'));
            }

            // Jika gagal ambil data, tetap tampilkan dashboard dengan data kosong
            $dashboardData = $this->getEmptyDashboardData();
            return view('finance.dashboard', compact('dashboardData'))
                   ->with('warning', 'Gagal mengambil data statistik');

        } catch (\Exception $e) {
            // Handle error dan tetap tampilkan dashboard
            $dashboardData = $this->getEmptyDashboardData();
            return view('finance.dashboard', compact('dashboardData'))
                   ->with('error', 'Terjadi kesalahan saat mengambil data dashboard');
        }
    }

    private function getEmptyDashboardData()
    {
        return [
            'stats' => [
                'totalRegistrations' => 0,
                'pendingPayments' => 0,
                'approvedPayments' => 0,
                'rejectedPayments' => 0,
                'todayPendingPayments' => 0,
                'todayProcessedPayments' => 0,
                'weeklyPendingPayments' => 0,
                'weeklyProcessedPayments' => 0,
                'totalRevenue' => 0,
                'monthlyRevenue' => 0,
                'paymentGrowthPercentage' => 0
            ],
            'charts' => [
                'dailyPaymentStats' => [],
                'monthlyPaymentStats' => [],
                'paymentStatusStats' => [],
                'eventsPendingPayments' => []
            ],
            'recentActivity' => [
                'recentPayments' => [],
                'urgentPendingPayments' => []
            ]
        ];
    }
}