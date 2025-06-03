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
        
        // Add filters if provided
        if ($request->has('payment_status') && $request->payment_status != '') {
            $params['payment_status'] = $request->payment_status;
        }
        if ($request->has('event_id') && $request->event_id != '') {
            $params['event_id'] = $request->event_id;
        }
        if ($request->has('page')) {
            $params['page'] = $request->page;
        }

        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/registrations', $params);

        if ($response->successful()) {
            $data = $response->json();
            
            // Get events for filter dropdown
            $eventsResponse = Http::withToken(session('jwt_token'))
                ->get($this->apiUrl . '/events');
            $events = $eventsResponse->successful() ? $eventsResponse->json() : [];

            return view('admin.registrations.index', [
                'registrations' => $data['registrations'],
                'pagination' => [
                    'current_page' => $data['currentPage'],
                    'total_pages' => $data['totalPages'],
                    'total' => $data['total']
                ],
                'events' => $events,
                'filters' => $request->only(['payment_status', 'event_id'])
            ]);
        }

        return back()->withErrors(['message' => 'Gagal mengambil data registrasi']);
    }

    public function pendingPayments(Request $request)
    {
        $params = [];
        if ($request->has('page')) {
            $params['page'] = $request->page;
        }

        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/registrations/pending-payments', $params);

        if ($response->successful()) {
            $data = $response->json();
            
            return view('admin.registrations.pending', [
                'registrations' => $data['registrations'],
                'pagination' => [
                    'current_page' => $data['currentPage'],
                    'total_pages' => $data['totalPages'],
                    'total' => $data['total']
                ]
            ]);
        }

        return back()->withErrors(['message' => 'Gagal mengambil data pembayaran pending']);
    }

    public function show($id)
    {
        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . "/registrations/{$id}");

        if ($response->successful()) {
            $registration = $response->json();
            return view('admin.registrations.show', compact('registration'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil detail registrasi']);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'required_if:payment_status,rejected|string|max:500'
        ]);

        try {
            $paymentData = [
                'payment_status' => $request->payment_status,
            ];

            if ($request->payment_status === 'rejected') {
                $paymentData['rejection_reason'] = $request->rejection_reason;
            }

            $response = Http::withToken(session('jwt_token'))
                ->put($this->apiUrl . "/registrations/{$id}/payment-status", $paymentData);

            if ($response->successful()) {
                $message = $request->payment_status === 'approved' 
                    ? 'Pembayaran berhasil disetujui' 
                    : 'Pembayaran berhasil ditolak';
                    
                return redirect()
                    ->route('admin.registrations.index')
                    ->with('success', $message);
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

    public function statistics()
    {
        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/registrations/statistics');

        if ($response->successful()) {
            $statistics = $response->json();
            return view('admin.registrations.statistics', compact('statistics'));
        }

        return back()->withErrors(['message' => 'Gagal mengambil statistik pembayaran']);
    }
}