<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    /**
     * Show participants list for an event
     */
    public function participants($eventId)
    {
        try {
            // Get event participants data from API
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/attendance/events/' . $eventId);

            if (!$response->successful()) {
                return redirect()->route('committee.event.index')->with('error', 'Gagal mengambil data peserta');
            }

            $data = $response->json();
            // Handle case where event is not found
            if (!$data['event']) {
                return redirect()->route('committee.event.index')->with('error', 'Event tidak ditemukan');
            }

            // Use stats from API if available, otherwise calculate locally
            $stats = isset($data['stats']) ? $data['stats'] : $this->calculateStats($data['participants']);

            // Apply filters if they exist
            $participants = $this->applyFilters($data['participants']);

            // Update the data array with filtered participants
            $data['participants'] = $participants;
            return view('committee.event.participant', compact('data', 'stats', 'participants'));
        } catch (\Exception $e) {
            return redirect()
                ->route('committee.event.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Apply filters to participants based on request parameters
     */
    private function applyFilters($participants)
    {
        $paymentStatus = request('payment_status');
        $registrationStatus = request('registration_status');
        $search = request('search');

        $filtered = collect($participants);

        // Filter by payment status
        if ($paymentStatus) {
            $filtered = $filtered->filter(function ($participant) use ($paymentStatus) {
                return $participant['payment_status'] === $paymentStatus;
            });
        }

        // Filter by registration status
        if ($registrationStatus) {
            $filtered = $filtered->filter(function ($participant) use ($registrationStatus) {
                return $participant['registration_status'] === $registrationStatus;
            });
        }

        // Filter by search (name or email)
        if ($search) {
            $filtered = $filtered->filter(function ($participant) use ($search) {
                $name = strtolower($participant['user_id']['name'] ?? '');
                $email = strtolower($participant['user_id']['email'] ?? '');
                $searchTerm = strtolower($search);

                return str_contains($name, $searchTerm) || str_contains($email, $searchTerm);
            });
        }

        return $filtered->values()->all();
    }

    /**
     * Show QR Scanner page
     */
    public function scanQR($eventId)
    {
        try {
            // Get event data for scanner page
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/attendance/events/' . $eventId);

            if (!$response->successful()) {
                return redirect()->route('committee.event.participants', $eventId)->with('error', 'Event teventIdak ditemukan');
            }

            $data = $response->json();
            
            $scannedResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/attendance/scanned-participants/' . $eventId);
            
if ($scannedResponse->successful()) {
    $scannedData = $scannedResponse->json();
} else {
    $scannedData = [];
}        
        $data['scanned_participants'] = $scannedData['scanned_participants'];
        $data['total_scanned'] = $scannedData['total_scanned'];
            return view('committee.event.scan-qr', compact('data'));
        } catch (\Exception $e) {
            return redirect()
                ->route('committee.event.participants', $eventId)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Process QR Code scan
     */
    public function processScan(Request $request) {
    $request->validate([
        'qr_token' => 'required|string',
        'event_id' => 'required|string',
    ]);

    try {
   

        // Send scan request to Node.js API
        $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/attendance/scan-qr', [
            'qr_token' => $request->qr_token,
            'scanned_by' => session('user_id'),
        ]);

        $result = $response->json();
        
        // Log the response
        Log::info('API Response:', [
            'status' => $response->status(),
            'body' => $result
        ]);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil!',
                'participant' => $result['participant'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Scan gagal',
            ], $response->status());
        }
    } catch (\Exception $e) {
        Log::error('QR Scan Exception:', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Manual check-in (backup method)
     */
    public function manualCheckIn(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'user_id' => 'required|string',
        ]);

        try {
            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/attendance/manual-checkin', [
                'session_id' => $request->session_id,
                'user_id' => $request->user_id,
                'scanned_by' => session('user_id'),
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manual check-in berhasil!',
                    'participant' => $result['participant'],
                ]);
            } else {
                return response()->json(
                    [
                        'success' => false,
                        'message' => $result['message'] ?? 'Check-in gagal',
                    ],
                    $response->status(),
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get session attendance list
     */
    public function sessionAttendance($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/attendance/session/' . $id);
            
            if (!$response->successful()) {
                return redirect()->back()->with('error', 'Gagal mengambil data kehadiran');
            }

            $data = $response->json();
            return view('committee.event.attendance', compact('data'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

  

    /**
     * Get participant details
     */
    public function participantDetails($participantId)
{
    try {
        // Get participant details from API
        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/participants/' . $participantId . '/details');
        if ($response->successful()) {
            $data = $response->json();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } else {
            $error = $response->json();
            return response()->json([
                'success' => false,
                'message' => $error['message'] ?? 'Failed to get participant details'
            ], $response->status());
        }
    } catch (\Exception $e) {
        Log::error('Participant Details Error:', [
            'participant_id' => $participantId,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Calculate statistics for participants
     */
    private function calculateStats($participants)
    {
        $stats = [
            'total_participants' => count($participants),
            'pending_payments' => 0,
            'confirmed_registrations' => 0,
            'total_attendances' => 0,
        ];

        foreach ($participants as $participant) {
            // Count pending payments
            if ($participant['payment_status'] === 'pending') {
                $stats['pending_payments']++;
            }

            // Count confirmed registrations
            if ($participant['registration_status'] === 'confirmed') {
                $stats['confirmed_registrations']++;
            }

            // Count total attendances
            if (isset($participant['session_registrations'])) {
                foreach ($participant['session_registrations'] as $session) {
                    if (isset($session['attendance']) && $session['attendance'] && $session['attendance']['attended']) {
                        $stats['total_attendances']++;
                    }
                }
            }
        }

        return $stats;
    }
}
