<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MemberRegistrationController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api';

    public function registerEvent($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/member/events/' . $id);
            if (!$response->successful()) {
                return back()->withErrors(['message' => 'Event tidak ditemukan']);
            }

            $event = $response->json();

            $registrationResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/check/' . $id);
            if ($registrationResponse->successful() && $registrationResponse->json()) {
                return redirect()->route('member.events.show', $id)->with('info', 'Anda sudah terdaftar untuk event ini');
            }

            $this->formatEventDisplay($event);

            $draft = null;
            $draftResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/draft/' . $id);

            if ($draftResponse->successful()) {
                $draftData = $draftResponse->json();

                if ($draftData && isset($draftData['selected_sessions'])) {
                    $draft = [
                        'selected_sessions' => $draftData['selected_sessions'],
                    ];
                }
            }

            return view('member.registration', compact('event', 'draft'));
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

  
    public function storeRegistration(Request $request, $id)
    {
        try {
            $request->validate([
                'selected_sessions' => 'required|array|min:1',
                'selected_sessions.*' => 'required|string',
            ]);

            $registrationCheckResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/check/' . $id);
            if ($registrationCheckResponse->successful() && $registrationCheckResponse->json()) {
                return redirect()->route('member.events.show', $id)->with('info', 'Anda sudah terdaftar untuk event ini');
            }

            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/member/events/' . $id);
            if (!$eventResponse->successful()) {
                return back()->withErrors(['message' => 'Event tidak ditemukan']);
            }
            $event = $eventResponse->json();

            $paymentAmount = 0;
            if (isset($event['sessions'])) {
                foreach ($event['sessions'] as $session) {
                    if (in_array($session['_id'], $request->selected_sessions)) {
                        $paymentAmount += $session['session_fee'] ?? 0;
                    }
                }
            }

            $registrationData = [
                'event_id' => $id,
                'selected_sessions' => $request->selected_sessions,
                'payment_amount' => $paymentAmount,
            ];

            session(['registration_data' => $registrationData]);
            
            $this->saveDraftData($registrationData);

            return redirect()->route('member.events.payment', $id);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function showPayment($id)
    {
        try {
            $registrationCheckResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/check/' . $id);
            if ($registrationCheckResponse->successful() && $registrationCheckResponse->json()) {
                return redirect()->route('member.events.show', $id)->with('info', 'Anda sudah terdaftar untuk event ini');
            }

            $registrationData = session('registration_data');
            if (!$registrationData) {
                return redirect()
                    ->route('member.events.register', $id)
                    ->withErrors(['message' => 'Data registrasi tidak ditemukan']);
            }

            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/member/events/' . $id);
            if (!$eventResponse->successful()) {
                return redirect()
                    ->route('member.events.index')
                    ->withErrors(['message' => 'Event tidak ditemukan']);
            }
            $event = $eventResponse->json();
            $this->formatEventDisplay($event);

            $selectedSessionsDetails = [];
            if (isset($event['sessions']) && isset($registrationData['selected_sessions'])) {
                foreach ($event['sessions'] as $session) {
                    if (in_array($session['_id'], $registrationData['selected_sessions'])) {
                        $selectedSessionsDetails[] = $session;
                    }
                }
            }

            return view('member.payment', compact('event', 'selectedSessionsDetails'));
        } catch (\Exception $e) {
            return redirect()
                ->route('member.events.index')
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Process payment and complete registration (Step 3)
     */
    public function processPayment(Request $request, $id)
    {
        try {
            // Check if user already registered first
            $registrationCheckResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/check/' . $id);
            if ($registrationCheckResponse->successful() && $registrationCheckResponse->json()) {
                return redirect()->route('member.events.show', $id)->with('info', 'Anda sudah terdaftar untuk event ini');
            }

            $registrationData = session('registration_data');
            if (!$registrationData) {
                return redirect()
                    ->route('member.events.register', $id)
                    ->withErrors(['message' => 'Data registrasi tidak ditemukan']);
            }

            // If event has fee, require payment proof
            if (($registrationData['payment_amount'] ?? 0) > 0) {
                $request->validate([
                    'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ]);

                // Upload payment proof
                if ($request->hasFile('payment_proof')) {
                    $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
                    $registrationData['payment_proof_url'] = $paymentProofPath;
                }
            }

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/registrations', $registrationData);
            if ($response->successful()) {
                // Clear session data
                session()->forget('registration_data');

                $registration = $response->json();
                return redirect()
                    ->route('member.events.registration-success', [
                        'id' => $id,
                        'registration_id' => $registration['registration']['_id'],
                    ])
                    ->with('success', 'Registrasi berhasil! Silakan tunggu verifikasi pembayaran.');
            }

            $error = $response->json()['message'] ?? 'Gagal memproses registrasi';
            return back()
                ->withErrors(['message' => $error])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show registration success page
     */
    public function registrationSuccess($id, $registrationId)
    {
        try {
            // Get registration details
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/' . $registrationId);
            if (!$response->successful()) {
                return redirect()
                    ->route('member.events.show', $id)
                    ->withErrors(['message' => 'Registrasi tidak ditemukan']);
            }

            $registration = $response->json();

            // Get event details
            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/member/events/' . $id);

            if (!$eventResponse->successful()) {
                return redirect()
                    ->route('member.events.index')
                    ->withErrors(['message' => 'Event tidak ditemukan']);
            }

            $event = $eventResponse->json();
            $this->formatEventDisplay($event);

            return view('member.detail', compact('registration', 'event'));
        } catch (\Exception $e) {
            return redirect()
                ->route('member.events.index')
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show user's registrations
     */
    public function myRegistrations()
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/my-registrations');

            if ($response->successful()) {
                $registrations = collect($response->json())->map(function ($registration) {
                    // Format dates and additional info for each registration
                    if (isset($registration['event_id'])) {
                        $registration['event_id']['formatted_date'] = Carbon::parse($registration['event_id']['date'])->format('d M Y');
                    }
                    return $registration;
                });

                return view('member.registrations.index', compact('registrations'));
            }

            return back()->withErrors(['message' => 'Gagal mengambil data registrasi']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show specific registration detail
     */
    public function showRegistration($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/' . $id);

            if ($response->successful()) {
                $registration = $response->json();

                // Format registration data
                if (isset($registration['event_id']) && is_array($registration['event_id']) && isset($registration['event_id']['date'])) {
                    $registration['event_id']['formatted_date'] = Carbon::parse($registration['event_id']['date'])->format('d M Y');
                }

                return view('member.registrations.show', compact('registration'));
            }
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show QR codes for confirmed registration
     */
    public function showQRCodes($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/' . $id . '/qr-codes');

            if (!$response->successful()) {
                return redirect()
                    ->route('member.myRegistrations.index')
                    ->withErrors(['message' => 'QR Code tidak ditemukan atau belum tersedia']);
            }

            $qrData = $response->json();
            return view('member.registrations.qr-codes', compact('qrData'));
        } catch (\Exception $e) {
            return redirect()
                ->route('member.myRegistrations.index')
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function saveDraftRegistration(Request $request, $id)
    {
        try {
            $request->validate([
                'selected_sessions' => 'required|array|min:1',
                'selected_sessions.*' => 'required|string',
            ]);

            // Get event details to calculate payment
            $eventResponse = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/member/events/' . $id);
            if (!$eventResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event tidak ditemukan',
                ], 404);
            }

            $event = $eventResponse->json();
            
            // Calculate payment amount based on selected sessions
            $paymentAmount = 0;
            if (isset($event['sessions'])) {
                foreach ($event['sessions'] as $session) {
                    if (in_array($session['_id'], $request->selected_sessions)) {
                        $paymentAmount += $session['session_fee'] ?? 0;
                    }
                }
            }

            $draftData = [
                'event_id' => $id,
                'selected_sessions' => $request->selected_sessions,
                'payment_amount' => $paymentAmount,
            ];

            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/registrations/draft', $draftData);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft berhasil disimpan',
                ]);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan draft',
                ],
                400,
            );
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

    public function getDraft($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/draft/' . $id);
            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(null);
        } catch (\Exception $e) {
            return response()->json(null);
        }
    }

    public function deleteDraft($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->delete($this->apiUrl . '/registrations/draft/' . $id);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft berhasil dihapus',
                ]);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus draft',
                ],
                400,
            );
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
     * Cancel registration
     */
    public function cancelRegistration($id)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->patch($this->apiUrl . '/registrations/' . $id . '/cancel');

            if ($response->successful()) {
                return redirect()->route('member.registrations.index')->with('success', 'Registrasi berhasil dibatalkan');
            }

            $error = $response->json()['message'] ?? 'Gagal membatalkan registrasi';
            return back()->withErrors(['message' => $error]);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Download payment proof
     */
    public function downloadPaymentProof($registrationId)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/registrations/' . $registrationId);

            if (!$response->successful()) {
                return redirect()
                    ->back()
                    ->withErrors(['message' => 'Registrasi tidak ditemukan']);
            }

            $registration = $response->json();

            if (!isset($registration['payment_proof_url'])) {
                return redirect()
                    ->back()
                    ->withErrors(['message' => 'Bukti pembayaran tidak ditemukan']);
            }

            // Return file download
            $filePath = Storage::disk('public')->path($registration['payment_proof_url']);
            return response()->download($filePath);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Private helper methods
     */
    private function formatEventDisplay(&$event)
    {
        if (isset($event['sessions']) && count($event['sessions']) > 0) {
            $firstDate = Carbon::parse($event['sessions'][0]['date']);
            $lastDate = Carbon::parse($event['sessions'][count($event['sessions']) - 1]['date']);
            $event['date_range'] = $firstDate->eq($lastDate) ? $firstDate->translatedFormat('l, d F Y') : $firstDate->translatedFormat('d M Y') . ' - ' . $lastDate->translatedFormat('d M Y');
        } else {
            $event['date_range'] = '-';
        }

        // Generate display location dari sessions
        $locations = collect($event['sessions'] ?? [])
            ->pluck('location')
            ->unique()
            ->filter()
            ->values();
        $event['display_location'] = $locations->count() === 1 ? $locations[0] : $locations->implode(', ');
    }

    private function saveDraftData($registrationData)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/registrations/draft', $registrationData);

            if ($response->successful()) {
                logger('Draft berhasil disimpan');
            } else {
                logger('Draft gagal disimpan: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            logger('Gagal kirim draft ke API: ' . $e->getMessage());
        }
    }
}