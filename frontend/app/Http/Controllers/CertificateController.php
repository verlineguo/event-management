<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    private $apiUrl = 'http://localhost:5000/api/certificate';

    /**
     * Upload single certificate (also handles replacement)
     */
    public function uploadCertificate(Request $request, $sessionId)
    {
        $request->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'participant_id' => 'required|string',
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('certificate')) {
                $file = $request->file('certificate');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('certificates', $filename, 'public');

                // Send certificate info to API
                $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/sessions/' . $sessionId . '/certificate', [
                    'participant_id' => $request->participant_id,
                    'certificate_path' => $path,
                    'uploaded_by' => session('user_id'),
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $message = isset($responseData['certificate']) && strpos($responseData['message'], 'updated') !== false ? 'Sertifikat berhasil diperbarui!' : 'Sertifikat berhasil diupload!';

                    return redirect()->back()->with('success', $message);
                } else {
                    // Delete the uploaded file if API call fails
                    Storage::disk('public')->delete($path);

                    $errorMessage = 'Gagal menyimpan data sertifikat';
                    if ($response->status() === 400) {
                        $errorData = $response->json();
                        $errorMessage = $errorData['message'] ?? $errorMessage;
                    }

                    return redirect()->back()->with('error', $errorMessage);
                }
            }

            return redirect()->back()->with('error', 'File sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk upload certificates for completed session
     */
    public function bulkUploadCertificates(Request $request, $sessionId)
    {
        $request->validate([
            'certificates' => 'required|array|min:1',
            'certificates.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'required|string',
        ]);

        try {
            $certificatesData = [];
            $uploadedFiles = []; // Track uploaded files for cleanup on error

            $files = $request->file('certificates');
            $participantIds = $request->participant_ids;

            // Validate that we have files to process
            if (empty($files) || empty($participantIds)) {
                return redirect()->back()->with('error', 'Tidak ada file atau peserta yang dipilih');
            }

            // Upload all files first
            foreach ($files as $index => $file) {
                try {
                    // Generate unique filename
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('certificates', $filename, 'public');
                    $uploadedFiles[] = $path; // Track for potential cleanup

                    // Determine participant ID for this file
                    $participantId = null;
                    if (count($participantIds) > $index) {
                        $participantId = $participantIds[$index];
                    } else {
                        // If more files than selected participants, cycle through participants
                        $participantId = $participantIds[$index % count($participantIds)];
                    }

                    $certificatesData[] = [
                        'participant_id' => $participantId,
                        'certificate_path' => $path,
                    ];
                } catch (\Exception $e) {
                    // Cleanup uploaded files if error occurs
                    foreach ($uploadedFiles as $uploadedPath) {
                        Storage::disk('public')->delete($uploadedPath);
                    }

                    return redirect()
                        ->back()
                        ->with('error', 'Gagal mengupload file: ' . $e->getMessage());
                }
            }

            // Send all certificate data to API in one request
            $response = Http::withToken(session('jwt_token'))->post($this->apiUrl . '/sessions/' . $sessionId . '/bulk-certificates', [
                'certificates' => $certificatesData,
                'uploaded_by' => session('user_id'),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $successCount = $responseData['success_count'] ?? 0;
                $errorCount = $responseData['error_count'] ?? 0;

                if ($successCount > 0 && $errorCount === 0) {
                    return redirect()
                        ->back()
                        ->with('success', "Berhasil mengupload {$successCount} sertifikat!");
                } elseif ($successCount > 0 && $errorCount > 0) {
                    $message = "Berhasil mengupload {$successCount} sertifikat, {$errorCount} gagal.";

                    // Show first few error details
                    if (isset($responseData['results']['errors']) && !empty($responseData['results']['errors'])) {
                        $errors = array_slice($responseData['results']['errors'], 0, 3);
                        $errorMessages = array_map(function ($error) {
                            return $error['error'];
                        }, $errors);

                        $message .= ' Detail error: ' . implode(', ', $errorMessages);

                        if (count($responseData['results']['errors']) > 3) {
                            $remaining = count($responseData['results']['errors']) - 3;
                            $message .= " dan {$remaining} error lainnya.";
                        }
                    }

                    return redirect()->back()->with('warning', $message);
                } else {
                    // All failed - cleanup uploaded files
                    foreach ($uploadedFiles as $uploadedPath) {
                        Storage::disk('public')->delete($uploadedPath);
                    }

                    $message = 'Semua upload gagal.';
                    if (isset($responseData['results']['errors']) && !empty($responseData['results']['errors'])) {
                        $errors = array_slice($responseData['results']['errors'], 0, 3);
                        $errorMessages = array_map(function ($error) {
                            return $error['error'];
                        }, $errors);
                        $message .= ' Detail error: ' . implode(', ', $errorMessages);
                    }

                    return redirect()->back()->with('error', $message);
                }
            } else {
                // API call failed - cleanup uploaded files
                foreach ($uploadedFiles as $uploadedPath) {
                    Storage::disk('public')->delete($uploadedPath);
                }

                $errorMessage = 'Gagal mengirim data ke server';
                if ($response->status() === 400) {
                    $errorData = $response->json();
                    $errorMessage = $errorData['message'] ?? $errorMessage;
                }

                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            // Cleanup uploaded files on exception
            if (isset($uploadedFiles)) {
                foreach ($uploadedFiles as $uploadedPath) {
                    Storage::disk('public')->delete($uploadedPath);
                }
            }

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download certificate
     */
    public function downloadCertificate($participantId)
    {
        try {
            $response = Http::withToken(session('jwt_token'))->get($this->apiUrl . '/download/' . $participantId);
            if (!$response->successful()) {
                return redirect()->back()->with('error', 'Certificate not found');
            }

            $data = $response->json();

            // Redirect to the certificate file URL
            return redirect(asset('storage/' . $data['download_url']));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Revoke certificate
     */
    public function revokeCertificate(Request $request, $participantId)
{
    $request->validate([
        'reason' => 'required|string|min:10|max:500',
    ]);

    try {
        $data = [
            'reason' => $request->reason,
            'participant_id' => $participantId,
            'revoked_by' => session('user_id'), // Tambahkan ini
        ];

        // Perbaikan: Gunakan DELETE method dengan body data
        $response = Http::withToken(session('jwt_token'))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->delete($this->apiUrl . '/revoke/' . $participantId, $data);
        
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Sertifikat berhasil dicabut');
        } else {
            $errorMessage = 'Gagal mencabut sertifikat';
            if ($response->status() === 404) {
                $errorMessage = 'Sertifikat tidak ditemukan';
            } elseif ($response->status() === 400) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorMessage;
            }
            
            return redirect()->back()->with('error', $errorMessage);
        }
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

/**
 * Export attendance with certificate status
 */
public function exportAttendance($sessionId)
{
    try {
        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/sessions/' . $sessionId . '/export');
        
        if ($response->successful()) {
            $data = $response->json();
            
            // Return CSV download
            return response()->streamDownload(function () use ($data) {
                $handle = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($handle, [
                    'Name',
                    'Email',
                    'Check-in Time',
                    'Method',
                    'Scanned By',
                    'Certificate Status',
                    'Certificate Number',
                    'Issued Date'
                ]);
                
                // CSV Data
                foreach ($data['attendances'] as $attendance) {
                    fputcsv($handle, [
                        $attendance['user_id']['name'],
                        $attendance['user_id']['email'],
                        $attendance['check_in_time'],
                        $attendance['attendance_method'] === 'qr_scan' ? 'QR Scan' : 'Manual',
                        $attendance['scanned_by']['name'] ?? 'System',
                        isset($attendance['certificate_path']) ? 'Issued' : 'Not Issued',
                        $attendance['certificate_number'] ?? 'N/A',
                        $attendance['certificate_issued_date'] ?? 'N/A'
                    ]);
                }
                
                fclose($handle);
            }, 'attendance_' . $sessionId . '_' . date('Y-m-d') . '.csv');
        }
        
        return redirect()->back()->with('error', 'Gagal mengekspor data');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

public function downloadCertificateMember($sessionId, $userId)
{
    try {
        // Pastikan member hanya bisa download sertifikat miliknya sendiri
        if ($userId !== session('user_id')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke sertifikat ini');
        }

        $response = Http::withToken(session('jwt_token'))
            ->get($this->apiUrl . '/download/member/' . $sessionId . '/' . $userId);

        if (!$response->successful()) {
            $errorMessage = 'Sertifikat tidak ditemukan';
            if ($response->status() === 404) {
                $errorMessage = 'Sertifikat belum tersedia untuk Anda';
            }
            return redirect()->back()->with('error', $errorMessage);
        }

        $data = $response->json();

        // Redirect ke URL file sertifikat
        return redirect(asset('storage/' . $data['download_url']));
                
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->with('error', 'Terjadi kesalahan saat mengunduh sertifikat: ' . $e->getMessage());
    }
}

}
