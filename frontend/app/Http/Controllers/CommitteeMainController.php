<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public function profile()
    {
        // Ambil data user dari session atau API
        $userId = session('user_id'); // Asumsikan user_id disimpan di session saat login
        if (!$userId) {
            return redirect()->route('login')->withErrors(['message' => 'Session expired, please login again']);
        }

        try {
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . "/users/{$userId}");
            if ($response->successful()) {
                $user = $response->json();
                // Update session dengan data user terbaru
                session(['user' => $user]);
                return view('committee.profile', compact('user'));
            }

            return back()->withErrors(['message' => 'Failed to fetch profile data']);
        } catch (\Exception $e) {
            Log::error('Error fetching profile: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Error occurred while fetching profile']);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|max:20',
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->withErrors(['message' => 'Session expired, please login again']);
        }

        try {
            $userData = [
                'name' => $request->name,
                'gender' => $request->gender,
                'phone' => $request->phone,
            ];

            $response = Http::withToken(session('jwt_token'))
                ->put($this->apiUrl . "/profile/{$userId}", $userData);
            if ($response->successful()) {

                $updatedUser = $response->json();
                // Update session dengan data user terbaru
                session(['user' => $updatedUser]);
                return redirect()->route('committee.profile')
                    ->with('status', 'profile-updated');
            }

            

            $errorMessage = $response->json('message') ?? 'Failed to update profile';
            return back()
                ->withErrors(['message' => $errorMessage])
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return back()
                ->withErrors(['message' => 'Error occurred while updating profile'])
                ->withInput();
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->withErrors(['message' => 'Session expired, please login again']);
        }

        try {
            $passwordData = [
                'current_password' => $request->current_password,
                'new_password' => $request->password,
            ];

            $response = Http::withToken(session('jwt_token'))
                ->put($this->apiUrl . "/profile/{$userId}/password", $passwordData);

            if ($response->successful()) {
                return redirect()->route('committee.profile')
                    ->with('status', 'password-updated');
            }

            $errorMessage = $response->json('message') ?? 'Failed to update password';
            return back()->withErrors(['current_password' => $errorMessage]);

        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return back()->withErrors(['current_password' => 'Error occurred while updating password']);
        }
    }

    /**
     * Get current user data (for AJAX requests)
     */
    public function getCurrentUser()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . "/users/{$userId}");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Failed to fetch user data'], 500);
        } catch (\Exception $e) {
            Log::error('Error fetching current user: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

}
