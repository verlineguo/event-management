<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function index(Request $request)
    {
        $params = [];
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $params['status'] = $request->status;
        }
        
        // Filter by event
        if ($request->has('event_id') && $request->event_id != '') {
            $params['event_id'] = $request->event_id;
        }

        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/payments', $params);

        if ($response->successful()) {
            $registrations = $response->json();
            
            // Calculate totals for each registration
            $registrations = array_map(function($registration) {
                $registration['total_session_fees'] = 0;
                $registration['session_count'] = 0;
                
                if (isset($registration['sessions']) && is_array($registration['sessions'])) {
                    $registration['session_count'] = count($registration['sessions']);
                    foreach ($registration['sessions'] as $session) {
                        $registration['total_session_fees'] += $session['session_fee'] ?? 0;
                    }
                }
                
                return $registration;
            }, $registrations);
            
            // Get events for filter dropdown
            $eventsResponse = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/events');
            $events = $eventsResponse->successful() ? $eventsResponse->json() : [];
            
            return view('finance.payment.index', compact('registrations', 'events'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil data pembayaran']);
    }

    public function show($id)
    {
        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . "/payments/{$id}");
        
        if ($response->successful()) {
            $registration = $response->json();
            
            // Calculate session totals
            $registration['total_session_fees'] = 0;
            $registration['session_count'] = 0;
            
            if (isset($registration['sessions']) && is_array($registration['sessions'])) {
                $registration['session_count'] = count($registration['sessions']);
                foreach ($registration['sessions'] as $session) {
                    $registration['total_session_fees'] += $session['session_fee'] ?? 0;
                }
            }

            return view('finance.payment.show', compact('registration'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil detail pembayaran']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'required_if:payment_status,rejected|string|max:500',
        ]);

        try {
            $paymentData = [
                'payment_status' => $request->payment_status,
            ];

            if ($request->payment_status === 'rejected') {
                $paymentData['rejection_reason'] = $request->rejection_reason;
            }

            $response = Http::withToken(session('jwt_token'))
                ->put($this->apiUrl . "/payments/{$id}/payment-status", $paymentData);

            if ($response->successful()) {
                $statusText = [
                    'approved' => 'disetujui',
                    'rejected' => 'ditolak',
                    'pending' => 'dikembalikan ke pending'
                ];

                return redirect()->route('finance.payment.index')
                    ->with('success', "Pembayaran berhasil {$statusText[$request->payment_status]}");
            }

            $error = $response->json('message') ?? 'Gagal mengupdate status pembayaran';
            return back()
                ->withErrors(['message' => $error])
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'string',
        ]);

        try {
            $response = Http::withToken(session('jwt_token'))
                ->post($this->apiUrl . '/payments/bulk-approve', [
                    'registration_ids' => $request->registration_ids
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return redirect()->route('finance.payment.index')
                    ->with('success', $result['message']);
            }

            $error = $response->json('message') ?? 'Gagal melakukan bulk approve';
            return back()->withErrors(['message' => $error]);

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function dashboard()
    {
        try {
            // Get pending payments count
            $pendingResponse = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/payments/pending-count');
            
            $pendingCount = $pendingResponse->successful() 
                ? $pendingResponse->json()['count'] 
                : 0;

            // Get recent payments
            $recentResponse = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/payments', ['limit' => 5]);
            
            $recentPayments = $recentResponse->successful() 
                ? $recentResponse->json() 
                : [];

            return view('finance.payment.dashboard', compact('pendingCount', 'recentPayments'));

        } catch (\Exception $e) {

        }
    }
}