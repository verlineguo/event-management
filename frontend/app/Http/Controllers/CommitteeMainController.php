<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CommitteeMainController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index()
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/dashboard/committee-dashboard');
            
            if ($response->successful()) {
                $dashboardData = $response->json();
                return view('committee.dashboard', compact('dashboardData'));
            }

            // Jika gagal ambil data, tetap tampilkan dashboard dengan data kosong
            $dashboardData = $this->getEmptyDashboardData();
            
            return view('committee.dashboard', compact('dashboardData'))
                ->with('warning', 'Gagal mengambil data statistik');
                
        } catch (\Exception $e) {
            // Handle error dan tetap tampilkan dashboard
            $dashboardData = $this->getEmptyDashboardData();
            
            return view('committee.dashboard', compact('dashboardData'))
                ->with('error', 'Terjadi kesalahan saat mengambil data dashboard');
        }
    }

    private function getEmptyDashboardData()
    {
        return [
            'stats' => [
                'totalEvents' => 0,
                'openEvents' => 0,
                'closedEvents' => 0,
                'completedEvents' => 0,
                'cancelledEvents' => 0,
                'totalSessions' => 0,
                'scheduledSessions' => 0,
                'ongoingSessions' => 0,
                'completedSessions' => 0,
                'totalRegistrations' => 0,
                'pendingPayments' => 0,
                'approvedPayments' => 0,
                'totalAttendance' => 0,
                'totalCertificates' => 0,
                'eventGrowthPercentage' => 0
            ],
            'charts' => [
                'monthlyRegistrations' => [],
                'eventsByCategory' => [],
                'registrationStatusStats' => [],
                'eventsPerformance' => []
            ],
            'recentActivity' => [
                'recentEvents' => [],
                'upcomingSessions' => [],
                'recentAttendance' => []
            ]
        ];
    }
}