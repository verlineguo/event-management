@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
    
@endsection

@section('content')
    <div class="registration-container">
        <!-- Success Header -->
            <div class="section-title ftco-animate">
                <h2><span>Registration</span> Details</h2>
                <p>Here are the full details of your registration for this event.</p>
            </div>

        <!-- Registration Details -->
        <div class="registration-details">
            <h3 class="section-title">
                <i class="bx bx-info-circle"></i>
                Informasi Registrasi
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
                <span class="detail-value">{{ $registration['event_id']['name'] ?? 'Event Name' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar"></i>
                    Tanggal Event
                </span>
                <span class="detail-value">{{ $registration['event_id']['formatted_date'] ?? 'Date' }}</span>
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
                    <i class="bx bx-check-circle"></i>
                    Status Pembayaran
                </span>
                <span class="detail-value">
                    @php
                        $paymentStatus = $registration['payment_status'] ?? 'pending';
                        $statusClass = 'status-pending';
                        $statusText = 'Menunggu Verifikasi';
                        
                        if ($paymentStatus === 'approved') {
                            $statusClass = 'status-confirmed';
                            $statusText = 'Disetujui';
                        } elseif ($paymentStatus === 'rejected') {
                            $statusClass = 'status-cancelled';
                            $statusText = 'Ditolak';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-info-circle"></i>
                    Status Registrasi
                </span>
                <span class="detail-value">
                    @php
                        $regStatus = $registration['registration_status'] ?? 'registered';
                        $regStatusClass = 'status-registered';
                        $regStatusText = 'Terdaftar';
                        
                        if ($regStatus === 'confirmed') {
                            $regStatusClass = 'status-confirmed';
                            $regStatusText = 'Terkonfirmasi';
                        } elseif ($regStatus === 'cancelled') {
                            $regStatusClass = 'status-cancelled';
                            $regStatusText = 'Dibatalkan';
                        } elseif ($regStatus === 'draft') {
                            $regStatusClass = 'status-pending';
                            $regStatusText = 'Draft';
                        }
                    @endphp
                    <span class="status-badge {{ $regStatusClass }}">{{ $regStatusText }}</span>
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

            @if(isset($registration['payment_verified_at']))
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bx bx-check-double"></i>
                        Tanggal Verifikasi
                    </span>
                    <span class="detail-value">
                        {{ \Carbon\Carbon::parse($registration['payment_verified_at'])->format('d M Y, H:i') }}
                    </span>
                </div>
            @endif

            @if(isset($registration['rejection_reason']))
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bx bx-message-square-error"></i>
                        Alasan Penolakan
                    </span>
                    <span class="detail-value" style="color: #dc3545;">
                        {{ $registration['rejection_reason'] }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Session Registrations -->
        @if(isset($registration['session_registrations']) && count($registration['session_registrations']) > 0)
            <div class="registration-details">
                <h3 class="section-title">
                    <i class="bx bx-list-ul"></i>
                    Sesi yang Diikuti ({{ count($registration['session_registrations']) }} sesi)
                </h3>

                <div class="sessions-list">
                    @foreach($registration['session_registrations'] as $sessionReg)
                        <div class="session-item">
                            <div class="session-header">
                                <div>
                                    <div class="session-title">{{ $sessionReg['session_id']['title'] ?? 'Session Title' }}</div>
                                    <div class="session-meta">
                                        <span>
                                            <i class="bx bx-calendar"></i>
                                            {{ isset($sessionReg['session_id']['date']) ? \Carbon\Carbon::parse($sessionReg['session_id']['date'])->format('d M Y') : 'Date' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-time"></i>
                                            {{ $sessionReg['session_id']['start_time'] ?? 'Start' }} - {{ $sessionReg['session_id']['end_time'] ?? 'End' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-location-plus"></i>
                                            {{ $sessionReg['session_id']['location'] ?? 'Location' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-user"></i>
                                            {{ $sessionReg['session_id']['speaker'] ?? 'Speaker' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Registration Status -->
                            @php
                                $sessionStatus = $sessionReg['status'] ?? 'registered';
                                $sessionStatusClass = 'attendance-pending';
                                $sessionStatusText = 'Terdaftar';
                                
                                if ($sessionStatus === 'completed') {
                                    $sessionStatusClass = 'attendance-present';
                                    $sessionStatusText = 'Selesai';
                                } elseif ($sessionStatus === 'cancelled') {
                                    $sessionStatusClass = 'attendance-absent';
                                    $sessionStatusText = 'Dibatalkan';
                                }
                            @endphp
                            <div class="attendance-status {{ $sessionStatusClass }}">
                                {{ $sessionStatusText }}
                            </div>

                            <!-- Attendance Information -->
                            @if(isset($sessionReg['attendance']))
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                                    <strong>Kehadiran:</strong>
                                    @if($sessionReg['attendance']['attended'])
                                        <span class="attendance-status attendance-present">Hadir</span>
                                        <small style="display: block; margin-top: 5px; color: #6c757d;">
                                            Check-in: {{ \Carbon\Carbon::parse($sessionReg['attendance']['check_in_time'])->format('d M Y, H:i') }}
                                        </small>
                                    @else
                                        <span class="attendance-status attendance-absent">Tidak Hadir</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="action-buttons">
            @if(($registration['registration_status'] ?? 'registered') === 'confirmed' || ($registration['payment_amount'] ?? 0) == 0)
                <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}" class="btn btn-success">
                    <i class="bx bx-qr-code"></i>
                    Lihat QR Code
                </a>
            @endif

            @if(isset($registration['payment_proof_url']))
                <a href="{{ route('member.myRegistrations.payment-proof', $registration['_id']) }}" class="btn btn-primary">
                    <i class="bx bx-download"></i>
                    Download Bukti Bayar
                </a>
            @endif

            @if(($registration['registration_status'] ?? 'registered') === 'registered' || ($registration['registration_status'] ?? 'registered') === 'confirmed')
                <form method="POST" action="{{ route('member.myRegistrations.cancel', $registration['_id']) }}" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan registrasi ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-x"></i>
                        Batalkan Registrasi
                    </button>
                </form>
            @endif

            <a href="{{ route('member.myRegistrations.index') }}" class="btn btn-outline">
                <i class="bx bx-list"></i>
                Kembali ke Daftar Registrasi
            </a>

            <a href="{{ route('member.events.show', $registration['event_id']['_id'] ?? '') }}" class="btn btn-outline">
                <i class="bx bx-eye"></i>
                Lihat Detail Event
            </a>
        </div>
    </div>
@endsection