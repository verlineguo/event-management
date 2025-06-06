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
                $transformedEvents = $this->transformEventsForHomeView($events);
                
                return view('member.home', compact('transformedEvents'));
            }

            return back()->withErrors(['message' => 'Gagal mengambil data events']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function events()
    {
        try {
            $response = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/member/events/featured');
                
            if ($response->successful()) {
                $events = $response->json();
                $transformedEvents = $this->transformEventsForHomeView($events);
                $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');

            $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];
                
                return view('member.events', compact('transformedEvents', 'categories'));
            }

            return back()->withErrors(['message' => 'Gagal mengambil data events']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    // Improved transformation for home page
    private function transformEventsForHomeView($events)
    {
        return collect($events)->map(function ($event) {
            // Extract session dates and fees
            $sessions = $event['sessions'] ?? [];
            $sessionDates = collect($sessions)
                ->pluck('date')
                ->filter()
                ->map(function ($date) {
                    try {
                        return Carbon::parse($date)->toDateString();
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter()
                ->sort();
                
            $sessionFees = collect($sessions)->pluck('session_fee')->filter();
            
            return [
                'id' => $event['_id'],
                'name' => $event['name'],
                'description' => $event['description'],
                'poster' => $this->getPosterUrl($event['poster'] ?? null),
                
                // Fix category extraction
                'category' => $this->extractCategory($event),
                'status' => $event['status'] ?? 'open',
                
                // Date info from sessions
                'display_date' => $this->getDisplayDateFromSessions($sessionDates),
                'date_range' => $this->getDateRangeFromSessions($sessionDates),
                
                // Session info
                'sessions_count' => $event['sessions_count'] ?? count($sessions),
                'has_available_sessions' => $event['has_available_sessions'] ?? true,
                
                // Calculate minimum fee
                'min_fee' => $sessionFees->min() ?? 0,
                'max_fee' => $sessionFees->max() ?? 0,
                'is_free' => $sessionFees->min() == 0,
                
                // Enhanced availability info
                'availability_status' => $this->getAvailabilityStatus($event),
                'registered_count' => $event['registered_count'] ?? 0,
                'max_participants' => $event['max_participants'] ?? 0,
                'quota_percentage' => $event['quota_percentage'] ?? 0,
                
                // Additional useful info for home page
                'is_featured' => true, // since this comes from featured endpoint
                'speaker_info' => $this->getMainSpeakerInfo($sessions),
                'location_info' => $this->getMainLocationInfo($sessions),
                'next_session' => $this->getNextSession($sessions),
            ];
        })->toArray();
    }

    private function extractCategory($event)
    {
        // Handle both string and object category
        if (isset($event['category_id']) && is_array($event['category_id'])) {
            return $event['category_id']['name'] ?? 'General';
        }
        
        if (isset($event['category'])) {
            return is_string($event['category']) ? $event['category'] : 'General';
        }
        
        return 'General';
    }

    private function getDisplayDateFromSessions($sessionDates)
    {
        if ($sessionDates->isEmpty()) {
            return now()->toDateString();
        }
        
        try {
            return Carbon::parse($sessionDates->first())->toDateString();
        } catch (\Exception $e) {
            return now()->toDateString();
        }
    }

    private function getDateRangeFromSessions($sessionDates)
    {
        if ($sessionDates->isEmpty()) {
            return 'Date TBA';
        }

        try {
            $firstDate = $sessionDates->first();
            $lastDate = $sessionDates->last();

            if ($sessionDates->count() === 1) {
                return Carbon::parse($firstDate)->format('M j, Y');
            }

            $first = Carbon::parse($firstDate);
            $last = Carbon::parse($lastDate);

            // Same month and year
            if ($first->format('Y-m') === $last->format('Y-m')) {
                return $first->format('M j') . ' - ' . $last->format('j, Y');
            }

            // Different months
            if ($first->year === $last->year) {
                return $first->format('M j') . ' - ' . $last->format('M j, Y');
            }

            // Different years
            return $first->format('M j, Y') . ' - ' . $last->format('M j, Y');
        } catch (\Exception $e) {
            return 'Date TBA';
        }
    }

    private function getMainSpeakerInfo($sessions)
    {
        $speakers = collect($sessions)->pluck('speaker')->filter()->unique();
        
        if ($speakers->isEmpty()) {
            return null;
        }
        
        if ($speakers->count() === 1) {
            return $speakers->first();
        }
        
        return $speakers->first() . ' +' . ($speakers->count() - 1) . ' more';
    }

    private function getMainLocationInfo($sessions)
    {
        $locations = collect($sessions)->pluck('location')->filter()->unique();
        
        if ($locations->isEmpty()) {
            return 'Location TBA';
        }
        
        if ($locations->count() === 1) {
            return $locations->first();
        }
        
        return $locations->first() . ' +' . ($locations->count() - 1) . ' venues';
    }

    private function getNextSession($sessions)
    {
        $now = now();
        $upcomingSessions = collect($sessions)
            ->filter(function ($session) use ($now) {
                try {
                    // Parse date from ISO format and combine with time
                    $sessionDate = Carbon::parse($session['date'])->format('Y-m-d');
                    $sessionDateTime = Carbon::createFromFormat('Y-m-d H:i', $sessionDate . ' ' . $session['start_time']);
                    return $sessionDateTime->isFuture();
                } catch (\Exception $e) {
                    // If parsing fails, assume it's upcoming
                    return true;
                }
            })
            ->sortBy('date')
            ->first();
            
        if (!$upcomingSessions) {
            return null;
        }
        
        return [
            'title' => $upcomingSessions['title'],
            'date' => $upcomingSessions['date'],
            'time' => $upcomingSessions['start_time'] . ' - ' . $upcomingSessions['end_time'],
            'location' => $upcomingSessions['location'],
            'speaker' => $upcomingSessions['speaker'],
        ];
    }

    private function getPosterUrl($poster)
    {
        if (!$poster) {
            return asset('images/default-event.jpg');
        }
        
        // If it's already a full URL
        if (str_starts_with($poster, 'http')) {
            return $poster;
        }
        
        // Try storage path first
        if (file_exists(storage_path('app/public/' . $poster))) {
            return asset('storage/' . $poster);
        }
        
        // Try public path
        if (file_exists(public_path($poster))) {
            return asset($poster);
        }
        
        // Default fallback
        return asset('images/default-event.jpg');
    }

    private function getAvailabilityStatus($event)
    {
        // Check if event has available sessions
        if (!($event['has_available_sessions'] ?? true)) {
            return 'full';
        }

        // Check session count
        $sessionsCount = $event['sessions_count'] ?? 0;
        if ($sessionsCount === 0) {
            return 'no_sessions';
        }

        // Check quota percentage
        $quotaPercentage = $event['quota_percentage'] ?? 0;
        if ($quotaPercentage >= 100) {
            return 'full';
        } elseif ($quotaPercentage >= 80) {
            return 'almost_full';
        }

        return 'available';
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


    public function search(Request $request)
{
    $params = [];
             
    if ($request->filled('q')) {
        $params['q'] = $request->q;
    }
             
    if ($request->filled('category')) {
        $params['category'] = $request->category;
    }
             
    if ($request->filled('status')) {
        $params['status'] = $request->status;
    }


    $response = Http::withToken(session('jwt_token'))
        ->timeout(30)
        ->get($this->apiUrl . '/events', $params);  


    if ($response->successful()) {
        $events = $response->json();
        
        $categoriesResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/category');

        
        $categories = $categoriesResponse->successful() ? $categoriesResponse->json() : [];
        
        if ($request->ajax()) {
            return response()->json($events);
        }
                     
        return view('committee.event.index', compact('events', 'categories'));
    }

    return back()->withErrors(['message' => 'Gagal melakukan pencarian events: ' . $response->body()]);
}

}