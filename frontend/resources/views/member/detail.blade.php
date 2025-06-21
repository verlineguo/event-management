@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="registration-container">
        <!-- Success Header -->
        <div class="header">
            <h1 class="title">
                @if(($registration['payment_status'] ?? '') === 'rejected')
                    <i class="bx bx-x-circle" style="color: #dc3545;"></i>
                    Pembayaran Ditolak
                @else
                    <i class="bx bx-check-circle" style="color: #28a745;"></i>
                    Registrasi Berhasil!
                @endif
            </h1>
            <p class="subtitle">
                @if(($registration['payment_status'] ?? '') === 'rejected')
                    Maaf, pembayaran Anda ditolak. Silakan upload ulang bukti pembayaran yang valid.
                @elseif(($registration['payment_amount'] ?? 0) > 0)
                    Terima kasih! Registrasi Anda telah berhasil disubmit. Silakan tunggu verifikasi pembayaran dari tim kami.
                @else
                    Selamat! Anda telah berhasil terdaftar untuk event ini.
                @endif
            </p>
        </div>

        <!-- Rejection Reason (if payment rejected) -->
        @if(($registration['payment_status'] ?? '') === 'rejected' && !empty($registration['rejection_reason']))
            <div class="alert alert-danger">
                <h4><i class="bx bx-info-circle"></i> Alasan Penolakan:</h4>
                <p>{{ $registration['rejection_reason'] }}</p>
            </div>
        @endif

        <!-- Upload Ulang Section (if payment rejected) -->
        @if(($registration['payment_status'] ?? '') === 'rejected')
            <div class="upload-section">
                <h4><i class="bx bx-upload"></i> Upload Ulang Bukti Pembayaran</h4>
                <form action="{{ route('member.myRegistrations.reupload-payment', $registration['_id']) }}" method="POST" enctype="multipart/form-data" id="reuploadForm">
                    @csrf
                    <div class="form-group">
                        <label for="payment_proof">Bukti Pembayaran Baru</label>
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/*,application/pdf" required>
                        <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 2MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload"></i>
                        Upload Ulang
                    </button>
                </form>
            </div>
        @endif

        <!-- Registration Details -->
        <div class="registration-details">
            <h3 class="section-title">
                <i class="bx bx-receipt"></i>
                Detail Registrasi
            </h3>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-hash"></i>
                    ID Registrasi
                </span>
                <span class="detail-value">{{ $registration['_id'] ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar-check"></i>
                    Event
                </span>
                <span class="detail-value">{{ $event['name'] ?? 'Event Name' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar"></i>
                    Tanggal Event
                </span>
                <span class="detail-value">{{ $event['date_range'] ?? 'Date Range' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-location-plus"></i>
                    Lokasi
                </span>
                <span class="detail-value">{{ $event['display_location'] ?? 'Location' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bxs-layer-plus"></i>
                    Jumlah Sesi
                </span>
                <span class="detail-value">{{ count($registration['session_registrations'] ?? []) }} sesi</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-credit-card"></i>
                    Total Pembayaran
                </span>
                <span class="detail-value">
                    @if(($registration['payment_amount'] ?? 0) == 0)
                        <strong style="color: #28a745;">GRATIS</strong>
                    @else
                        <strong>Rp {{ number_format($registration['payment_amount'] ?? 0) }}</strong>
                    @endif
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-info-circle"></i>
                    Status
                </span>
                <span class="detail-value">
                    @php
                        $status = $registration['registration_status'] ?? 'pending';
                        $paymentStatus = $registration['payment_status'] ?? 'pending';
                        $statusClass = 'status-pending';
                        $statusText = 'Menunggu Verifikasi';
                        
                        if ($paymentStatus === 'rejected') {
                            $statusClass = 'status-rejected';
                            $statusText = 'Pembayaran Ditolak';
                        } elseif ($status === 'confirmed') {
                            $statusClass = 'status-confirmed';  
                            $statusText = 'Terkonfirmasi';
                        } elseif ($status === 'cancelled') {
                            $statusClass = 'status-cancelled';
                            $statusText = 'Dibatalkan';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-timer"></i>
                    Tanggal Registrasi
                </span>
                <span class="detail-value">
                    {{ isset($registration['createdAt']) ? \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y, H:i') : 'N/A' }}
                </span>
            </div>
        </div>

        <!-- Next Steps -->
        @if(($registration['payment_status'] ?? '') === 'rejected')
            <div class="next-steps">
                <h4>
                    <i class="bx bx-exclamation-triangle"></i>
                    Langkah Selanjutnya
                </h4>
                <ol class="steps-list">
                    <li>
                        <span class="step-number">1</span>
                        <span>Upload ulang bukti pembayaran yang valid menggunakan form di atas</span>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <span>Pastikan bukti pembayaran jelas dan sesuai dengan jumlah yang harus dibayar</span>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <span>Tim kami akan memverifikasi ulang dalam waktu 1x24 jam</span>
                    </li>
                </ol>
            </div>
        @elseif(($registration['payment_amount'] ?? 0) > 0 && ($registration['registration_status'] ?? 'pending') === 'registered')
            <div class="next-steps">
                <h4>
                    <i class="bx bx-list-check"></i>
                    Langkah Selanjutnya
                </h4>
                <ol class="steps-list">
                    <li>
                        <span class="step-number">1</span>
                        <span>Tim kami akan memverifikasi pembayaran Anda dalam waktu 1x24 jam</span>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <span>Anda akan menerima notifikasi email setelah pembayaran terverifikasi</span>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <span>QR Code untuk check-in akan tersedia setelah konfirmasi pembayaran</span>
                    </li>
                    <li>
                        <span class="step-number">4</span>
                        <span>Tunjukkan QR Code saat hari pelaksanaan event</span>
                    </li>
                </ol>
            </div>
        @elseif(($registration['registration_status'] ?? 'pending') === 'confirmed')
            <div class="next-steps">
                <h4>
                    <i class="bx bx-party-horn"></i>
                    Selamat! Registrasi Anda Telah Dikonfirmasi
                </h4>
                <ol class="steps-list">
                    <li>
                        <span class="step-number">1</span>
                        <span>QR Code Anda sudah tersedia untuk check-in</span>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <span>Simpan atau screenshot QR Code untuk ditunjukkan saat event</span>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <span>Datang tepat waktu sesuai jadwal sesi yang dipilih</span>
                    </li>
                </ol>
            </div>
        @else
            <div class="next-steps">
                <h4>
                    <i class="bx bx-check-circle"></i>
                    Event Gratis - Registrasi Langsung Dikonfirmasi
                </h4>
                <ol class="steps-list">
                    <li>
                        <span class="step-number">1</span>
                        <span>QR Code Anda sudah tersedia untuk check-in</span>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <span>Simpan atau screenshot QR Code untuk ditunjukkan saat event</span>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <span>Datang tepat waktu sesuai jadwal sesi yang dipilih</span>
                    </li>
                </ol>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('member.myRegistrations.show', $registration['_id']) }}" class="btn btn-primary">
                <i class="bx bx-eye"></i>
                Lihat Detail Registrasi
            </a>

            @if(($registration['registration_status'] ?? 'pending') === 'confirmed' || ($registration['payment_amount'] ?? 0) == 0)
                <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}" class="btn btn-success">
                    <i class="bx bx-qrcode"></i>  
                    Lihat QR Code
                </a>
            @endif

            <!-- Cancel Button (only if not confirmed and not already cancelled) -->
            @if(($registration['registration_status'] ?? '') !== 'confirmed' && ($registration['registration_status'] ?? '') !== 'cancelled')
                <button type="button" class="btn btn-danger" onclick="cancelRegistration('{{ $registration['_id'] }}')">
                    <i class="bx bx-x"></i>
                    Batalkan Registrasi
                </button>
            @endif

            <a href="{{ route('member.myRegistrations.index') }}" class="btn btn-outline">
                <i class="bx bx-list"></i>
                Lihat Semua Registrasi
            </a>

            <a href="{{ route('member.events.index') }}" class="btn btn-outline">
                <i class="bx bx-calendar"></i>
                Kembali ke Event
            </a>
        </div>
    </div>

    <style>
    .upload-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
    }

    .upload-section h4 {
        color: #dc3545;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-group input[type="file"] {
        width: 100%;
        padding: 8px;
        border: 2px dashed #ddd;
        border-radius: 4px;
        background: white;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .status-rejected {
        background: #dc3545;
        color: white;
    }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Handle reupload form
        document.getElementById('reuploadForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Upload Bukti Pembayaran',
                text: 'Apakah Anda yakin ingin mengupload ulang bukti pembayaran?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Upload!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Mengupload...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Bukti pembayaran berhasil diupload ulang. Tim kami akan memverifikasi dalam 1x24 jam.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat mengupload',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        });

        // Cancel Registration Function
        function cancelRegistration(registrationId) {
            Swal.fire({
                title: 'Batalkan Registrasi?',
                html: `
                    <p>Apakah Anda yakin ingin membatalkan registrasi ini?</p>
                    <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <small style="color: #856404;">
                            <i class="bx bx-info-circle"></i>
                            <strong>Perhatian:</strong> Uang yang sudah dibayar TIDAK akan dikembalikan.
                        </small>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Membatalkan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit cancel request
                    fetch(`/member/registrations/${registrationId}/cancel`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Dibatalkan!',
                                text: 'Registrasi Anda telah dibatalkan.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '/member/registrations';
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal membatalkan registrasi',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        // Show success message if any
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        // Show error message if any
        @if($errors->any())
            Swal.fire({
                title: 'Error!',
                text: '{{ $errors->first() }}',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
@endsection