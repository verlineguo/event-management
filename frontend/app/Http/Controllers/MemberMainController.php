<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberMainController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';


    public function index()
    {
        try {
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/member/events/featured');
            if ($response->successful()) {
                $events = $response->json();
                $transformedEvents = $this->transformEventsForView($events);
                return view('member.home', compact('transformedEvents'));
            }

            return back()->withErrors(['message' => 'Gagal mengambil data events']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    private function transformEventsForView($events)
    {
        return collect($events)->map(function ($event) {
            // Get event-level data
            $totalRegistered = $event['registered_count'] ?? 0;
            $maxParticipants = $event['max_participants'] ?? 0; // Kapasitas maksimum event
            $availableSlots = max(0, $maxParticipants - $totalRegistered); // Sisa slot yang tersedia
            $hasAvailableSessions = $event['has_available_sessions'] ?? false;
            
            // Process sessions with detailed quota info
            $sessions = collect($event['sessions'] ?? [])->map(function ($session, $index) {
                $sessionRegistered = $session['registered_count'] ?? 0;
                $sessionMaxParticipants = $session['max_participants'] ?? 0; // Kapasitas maksimum session
                $sessionAvailableSlots = max(0, $sessionMaxParticipants - $sessionRegistered); // Sisa slot session
                
                // Hitung persentase terisi (bukan tersedia)
                $quotaPercentage = $sessionMaxParticipants > 0 
                    ? round(($sessionRegistered / $sessionMaxParticipants) * 100) 
                    : 0;
                
                return [
                    'id' => $session['_id'] ?? $session['id'] ?? null,
                    'session_order' => $session['session_order'] ?? ($index + 1),
                    'title' => $session['title'] ?? "Session " . ($index + 1),
                    'date' => $session['date'] ?? null,
                    'start_time' => $session['start_time'] ?? null,
                    'end_time' => $session['end_time'] ?? null,
                    'location' => $session['location'] ?? 'Location TBA',
                    'speaker' => $session['speaker'] ?? 'Speaker TBA',
                    'description' => $session['description'] ?? '',
                    'status' => $session['status'] ?? 'scheduled',
                    // Quota information (DIPERBAIKI)
                    'registered_count' => $sessionRegistered, // Jumlah yang sudah daftar
                    'max_participants' => $sessionMaxParticipants, // Kapasitas maksimum
                    'available_slots' => $sessionAvailableSlots, // Sisa slot yang masih bisa diisi
                    'quota_percentage' => $quotaPercentage, // Persentase terisi
                    'is_full' => $sessionAvailableSlots <= 0, // Penuh jika sisa slot = 0
                    'is_almost_full' => $quotaPercentage >= 80, // Hampir penuh jika 80% terisi
                ];
            })->sortBy('session_order')->values();
            
            // Get date range from sessions
            $dateRange = $this->getEventDateRange($sessions);
            $primarySession = $sessions->first();
            
            // Determine overall event status (DIPERBAIKI)
            $eventStatus = $event['status'] ?? 'open';
            if ($eventStatus === 'open' && $availableSlots <= 0) {
                $eventStatus = 'full'; // Event penuh jika tidak ada slot tersedia
            }
            
            // Calculate session summary for display
            $sessionSummary = $this->getSessionSummary($sessions);
            
            // Hitung persentase event terisi
            $eventQuotaPercentage = $maxParticipants > 0 
                ? round(($totalRegistered / $maxParticipants) * 100) 
                : 0;
            
            return [
                'id' => $event['_id'],
                'name' => $event['name'] ?? 'Event Name',
                'description' => $event['description'] ?? '',
                'poster' => $this->getPosterUrl($event['poster'] ?? null),
                'category' => isset($event['category_id']) && is_array($event['category_id'])
                    ? ($event['category_id']['name'] ?? 'General')
                    : 'General',
                'registration_fee' => $event['registration_fee'] ?? 0,
                
                // Event quota information (DIPERBAIKI)
                'max_participants' => $maxParticipants, // Kapasitas maksimum event
                'available_slots' => $availableSlots, // Sisa slot event yang tersedia
                'registered_count' => $totalRegistered, // Total yang sudah daftar
                'quota_percentage' => $eventQuotaPercentage, // Persentase event terisi
                'is_full' => $availableSlots <= 0, // Event penuh
                'is_almost_full' => $eventQuotaPercentage >= 80, // Event hampir penuh
                
                'status' => $eventStatus,
                'has_available_sessions' => $hasAvailableSessions,
                'created_at' => $event['createdAt'] ?? $event['created_at'] ?? now(),
                
                // Session information
                'sessions_count' => $sessions->count(),
                'sessions' => $sessions->toArray(),
                'primary_session' => $primarySession,
                'session_summary' => $sessionSummary,
                
                // Display data (from primary session or default)
                'display_date' => $primarySession['date'] ?? now()->addDays(7)->format('Y-m-d'),
                'display_time' => $this->formatSessionTime($primarySession),
                'display_location' => $primarySession['location'] ?? 'Location TBA',
                'display_speaker' => $primarySession['speaker'] ?? 'Speaker TBA',
                'date_range' => $dateRange,
            ];
        })->take(6)->toArray();
    }

    private function getPosterUrl($poster)
    {
        if (!$poster) {
            return asset('images/default-event.jpg');
        }
        
        // Check if it's already a full URL
        if (str_starts_with($poster, 'http')) {
            return $poster;
        }
        
        // Check if file exists in storage
        if (file_exists(storage_path('app/public/' . $poster))) {
            return asset('storage/' . $poster);
        }
        
        // Check if file exists in public directory
        if (file_exists(public_path($poster))) {
            return asset($poster);
        }
        
        // Fallback to default image
        return asset('images/default-event.jpg');
    }

    private function getSessionSummary($sessions)
    {
        if ($sessions->isEmpty()) {
            return ['total' => 0, 'available' => 0, 'full' => 0, 'almost_full' => 0];
        }

        return [
            'total' => $sessions->count(),
            'available' => $sessions->where('available_slots', '>', 0)->count(),
            'full' => $sessions->where('is_full', true)->count(),
            'almost_full' => $sessions->where('is_almost_full', true)->where('is_full', false)->count(),
        ];
    }

    private function getEventDateRange($sessions)
    {
        if ($sessions->isEmpty()) {
            return 'Date TBA';
        }

        $dates = $sessions->pluck('date')->filter()->sort();
        
        if ($dates->count() === 1) {
            return Carbon::parse($dates->first())->format('M j, Y');
        }
        
        if ($dates->count() > 1) {
            $firstDate = Carbon::parse($dates->first());
            $lastDate = Carbon::parse($dates->last());
            
            if ($firstDate->format('Y') === $lastDate->format('Y')) {
                if ($firstDate->format('m') === $lastDate->format('m')) {
                    return $firstDate->format('M j') . ' - ' . $lastDate->format('j, Y');
                } else {
                    return $firstDate->format('M j') . ' - ' . $lastDate->format('M j, Y');
                }
            } else {
                return $firstDate->format('M j, Y') . ' - ' . $lastDate->format('M j, Y');
            }
        }

        return 'Date TBA';
    }

    private function formatSessionTime($session)
    {
        if (!$session || !isset($session['start_time'])) {
            return 'Time TBA';
        }

        $startTime = $session['start_time'];
        $endTime = $session['end_time'] ?? null;

        if ($endTime) {
            return $startTime . ' - ' . $endTime;
        }

        return $startTime;
    }

    public function events(Request $request)
    {
        try {
            $query = $request->get('search');
            $category = $request->get('category');
            $status = $request->get('status', 'open');

            $queryParams = [
                'q' => $query,
                'category' => $category,
                'status' => $status
            ];

            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/member/events/search', array_filter($queryParams));

            if ($response->successful()) {
                $events = $response->json();
                $transformedEvents = $this->transformEventsForView($events);
                
                // Get categories for filter
                $categoriesResponse = Http::withToken(session('jwt_token'))
                    ->get($this->apiUrl . '/categories');
                $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];
                
                return view('member.events.index', compact('transformedEvents', 'categories'));
            }

            return back()->withErrors(['message' => 'Gagal mengambil data events']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function showEvent($id)
    {
        try {
            // Get event detail with sessions
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/member/events/' . $id);

            if ($response->successful()) {
                $event = $response->json();
                
                // Check if user already registered for this event
                $registrationResponse = Http::withToken(session('jwt_token'))
                    ->get($this->apiUrl . '/registrations/check/' . $id);
                
                $userRegistration = null;
                if ($registrationResponse->successful()) {
                    $userRegistration = $registrationResponse->json();
                }

                return view('member.events.show', compact('event', 'userRegistration'));
            }

            return back()->withErrors(['message' => 'Event tidak ditemukan']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    public function downloadCertificate($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/certificates/' . $id . '/download');

            if ($response->successful()) {
                $certificate = $response->json();
                return redirect($certificate['download_url']);
            }

            return back()->withErrors(['message' => 'Sertifikat tidak ditemukan']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

   
}
