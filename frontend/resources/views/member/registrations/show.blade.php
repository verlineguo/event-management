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
                <i class="bx bx-info-circle align-middle"></i>
                Informasi Registrasi
            </h3>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-hash align-middle"></i>
                    ID Registrasi
                </span>
                <span class="detail-value">{{ $registration['_id'] ?? 'N/A' }}</span>
            </div>


            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar-check align-middle"></i>
                    Event
                </span>
                <span class="detail-value">{{ $registration['event_id']['name'] ?? 'Event Name' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar align-middle"></i>
                    Tanggal Event
                </span>
                <span class="detail-value">{{ $registration['event_id']['formatted_date'] ?? 'Date' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-credit-card align-middle"></i>
                    Total Pembayaran
                </span>
                <span class="detail-value">
                    @if (($registration['payment_amount'] ?? 0) == 0)
                        <strong style="color: #28a745;">GRATIS</strong>
                    @else
                        <strong>Rp {{ number_format($registration['payment_amount'] ?? 0) }}</strong>
                    @endif
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-check-circle align-middle"></i>
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
                    <i class="bx bx-info-circle align-middle"></i>
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
                    <i class="bx bx-timer align-middle"></i>
                    Tanggal Registrasi
                </span>
                <span class="detail-value">
                    {{ isset($registration['createdAt']) ? \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y, H:i') : 'N/A' }}
                </span>
            </div>

            @if (isset($registration['payment_verified_at']))
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bx bx-check-double align-middle"></i>
                        Tanggal Verifikasi
                    </span>
                    <span class="detail-value">
                        {{ \Carbon\Carbon::parse($registration['payment_verified_at'])->format('d M Y, H:i') }}
                    </span>
                </div>
            @endif

            @if (isset($registration['rejection_reason']))
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="bx bx-message-square-error align-middle"></i>
                        Alasan Penolakan
                    </span>
                    <span class="detail-value" style="color: #dc3545;">
                        {{ $registration['rejection_reason'] }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Session Registrations -->
        @if (isset($registration['session_registrations']) && count($registration['session_registrations']) > 0)
            <div class="registration-details">
                <h3 class="section-title">
                    <i class="bx bx-list-ul align-middle"></i>
                    Sesi yang Diikuti ({{ count($registration['session_registrations']) }} sesi)
                </h3>

                <div class="sessions-list">
                    @foreach ($registration['session_registrations'] as $sessionReg)
                        {{-- Session item with improved attendance and certificate display --}}
                        <div class="session-item card mb-4 p-3 shadow-sm">
                            <div class="session-header">
                                <div>
                                    <div class="session-title fw-bold fs-5 mb-2" style="color:#4a4a4a;">
                                        {{ $sessionReg['session_id']['title'] ?? 'Session Title' }}
                                    </div>
                                    <div class="session-meta">
                                        <span>
                                            <i class="bx bx-calendar align-middle"></i>
                                            {{ isset($sessionReg['session_id']['date']) ? \Carbon\Carbon::parse($sessionReg['session_id']['date'])->format('d M Y') : 'Date' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-time align-middle"></i>
                                            {{ $sessionReg['session_id']['start_time'] ?? 'Start' }} -
                                            {{ $sessionReg['session_id']['end_time'] ?? 'End' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-location-plus align-middle"></i>
                                            {{ $sessionReg['session_id']['location'] ?? 'Location' }}
                                        </span>
                                        <span>
                                            <i class="bx bx-user align-middle"></i>
                                            {{ $sessionReg['session_id']['speaker'] ?? 'Speaker' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Session Registration Status --}}
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

                            {{-- Attendance Information --}}
                            <div class="mt-3 pt-3 border-top">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-2 mt-3">
                                            <i class="bx bx-user-check align-middle"></i> Status Kehadiran
                                        </h6>
                                        @if (isset($sessionReg['attendance']) && $sessionReg['attendance'])
                                            @if ($sessionReg['attendance']['attended'])
                                                <div class="mt-2">
                                                    <span class="badge bg-success text-white">
                                                        <i class="bx bx-check-circle align-middle"></i>
                                                        Hadir
                                                    </span>
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block">
                                                            <i class="bx bx-time align-middle"></i>
                                                            Check-in:
                                                            {{ isset($sessionReg['attendance']['check_in_time']) ? \Carbon\Carbon::parse($sessionReg['attendance']['check_in_time'])->format('d M Y, H:i') : 'N/A' }}
                                                        </small>
                                                        @if (isset($sessionReg['attendance']['attendance_method']))
                                                            <small class="text-muted d-block">
                                                                <i class="bx bx-scan align-middle"></i>
                                                                Metode:
                                                                {{ $sessionReg['attendance']['attendance_method'] === 'qr_scan' ? 'QR Code Scan' : 'Manual' }}
                                                            </small>
                                                        @endif
                                                        @if (isset($sessionReg['attendance']['scanned_by']['name']))
                                                            <small class="text-muted d-block">
                                                                <i class="bx bx-user align-middle"></i>
                                                                Dicatat oleh:
                                                                {{ $sessionReg['attendance']['scanned_by']['name'] }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-2">
                                                    <span class="badge bg-danger">
                                                        <i class="bx bx-x-circle align-middle"></i>
                                                        Tidak Hadir
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="mt-2">
                                                <span class="badge bg-secondary text-white">
                                                    <i class="bx bx-help-circle align-middle"></i>
                                                    Belum Dicatat
                                                </span>
                                                <small class="text-muted d-block mt-1">
                                                    Kehadiran akan dicatat saat sesi berlangsung
                                                </small>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-2 mt-3">
                                            <i class="bx bx-certificate align-middle"></i> Status Sertifikat
                                        </h6>
                                        @if (isset($sessionReg['certificate']) && $sessionReg['certificate'])
                                            <div class="mt-2">
                                                <span class="badge bg-success text-white">
                                                    Tersedia
                                                </span>
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-hash align-middle"></i>
                                                        No. Sertifikat:
                                                        {{ $sessionReg['certificate']['certificate_number'] ?? 'N/A' }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="bx bx-calendar align-middle"></i>
                                                        Tanggal Terbit:
                                                        {{ isset($sessionReg['certificate']['issued_date']) ? \Carbon\Carbon::parse($sessionReg['certificate']['issued_date'])->format('d M Y') : 'N/A' }}
                                                    </small>
                                                    @if (isset($sessionReg['certificate']['uploaded_by']['name']))
                                                        <small class="text-muted d-block">
                                                            <i class="bx bx-user align-middle"></i>
                                                            Diterbitkan oleh:
                                                            {{ $sessionReg['certificate']['uploaded_by']['name'] }}
                                                        </small>
                                                    @endif
                                                    @if (isset($sessionReg['certificate']['notes']))
                                                        <small class="text-muted d-block">
                                                            <i class="bx bx-note align-middle"></i>
                                                            Catatan: {{ $sessionReg['certificate']['notes'] }}
                                                        </small>
                                                    @endif
                                                    <div class="mt-2">
                                                        <a href="{{ route('member.certificate.download', ['sessionId' => $sessionReg['session_id']['_id'], 'userId' => $registration['user_id']['_id'] ?? session('user_id')]) }}"
                                                            class="btn btn-success btn-sm">
                                                            <i class="bx bx-arrow-down-circle align-middle"></i>
                                                            Download Sertifikat
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif(isset($sessionReg['attendance']) && $sessionReg['attendance'] && $sessionReg['attendance']['attended'])
                                            <div class="mt-2">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bx bx-clock align-middle"></i>
                                                    Dalam Proses
                                                </span>
                                                <small class="text-muted d-block mt-1">
                                                    Sertifikat sedang dalam proses penerbitan
                                                </small>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="badge bg-secondary text-white">
                                                    <i class="bx bx-info-circle align-middle"></i>
                                                    Belum Tersedia
                                                </span>
                                                <small class="text-muted d-block mt-1">
                                                    Sertifikat akan diterbitkan setelah kehadiran dikonfirmasi
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code Status (jika masih registered) --}}
                            @if ($sessionReg['status'] === 'registered' && isset($sessionReg['qr_token']))
                                <div class="mt-3 pt-3 border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-2 mt-3">
                                                <i class="bx bx-qr align-middle"></i> QR Code untuk Check-in:
                                            </h6>
                                            <div class="mt-1">
                                                @if ($sessionReg['qr_used'])
                                                    <span class="badge bg-success">
                                                        <i class="bx bx-check align-middle"></i>
                                                        Sudah Digunakan
                                                    </span>
                                                    @if (isset($sessionReg['used_at']))
                                                        <small class="text-muted d-block">
                                                            Digunakan pada:
                                                            {{ \Carbon\Carbon::parse($sessionReg['used_at'])->format('d M Y, H:i') }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-primary text-white">
                                                        <i class="bx bx-qr-scan align-middle"></i>
                                                        Siap Digunakan
                                                    </span>
                                                    <small class="text-muted d-block">
                                                        Tunjukkan QR Code ini saat check-in
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            @if (!$sessionReg['qr_used'])
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#qrModal{{ $loop->index }}">
                                                    <i class="bx bx-qr align-middle"></i>
                                                    Lihat QR Code
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- QR Code Modal --}}
                                @if (!$sessionReg['qr_used'])
                                    <div class="modal fade" id="qrModal{{ $loop->index }}" tabindex="-1"
                                        aria-labelledby="qrModalLabel{{ $loop->index }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="qrModalLabel{{ $loop->index }}">
                                                        <i class="bx bx-qr align-middle"></i>
                                                        QR Code Check-in
                                                    </h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal"
                                                        aria-label="Close"><i class="bx bx-x align-middle"></i></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <h6 class="mb-3">
                                                        {{ $sessionReg['session_id']['title'] ?? 'Session Title' }}</h6>
                                                    <div class="mb-3">
                                                        <img src="{{ $sessionReg['qr_code'] }}" alt="QR Code"
                                                            class="img-fluid" style="max-width: 250px;">
                                                    </div>
                                                    <p class="text-muted mb-2">
                                                        <i class="bx bx-info-circle align-middle"></i>
                                                        Tunjukkan QR Code ini kepada petugas saat check-in
                                                    </p>
                                                    <small class="text-muted">
                                                        Token: {{ $sessionReg['qr_token'] }}
                                                    </small>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="action-buttons">
            @if (
                ($registration['registration_status'] ?? 'registered') === 'confirmed' ||
                    ($registration['payment_amount'] ?? 0) == 0)
                <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}" class="btn btn-success">
                    <i class="bx bx-qr align-middle"></i>
                    Lihat QR Code
                </a>
            @endif

            @if (isset($registration['payment_proof_url']))
                <a href="{{ route('member.myRegistrations.payment-proof', $registration['_id']) }}"
                    class="btn btn-primary">
                    <i class="bx bx-arrow-down-circle align-middle"></i>
                    Download Bukti Bayar
                </a>
            @endif

            @if (
                ($registration['registration_status'] ?? 'registered') === 'registered' ||
                    ($registration['registration_status'] ?? 'registered') === 'confirmed')
                <form method="POST" action="{{ route('member.myRegistrations.cancel', $registration['_id']) }}"
                    style="display: inline;"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan registrasi ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-x align-middle"></i>
                        Batalkan Registrasi
                    </button>
                </form>
            @endif

            <a href="{{ route('member.myRegistrations.index') }}" class="btn btn-outline">
                <i class="bx bx-list align-middle"></i>
                Kembali ke Daftar Registrasi
            </a>

            <a href="{{ route('member.events.show', $registration['event_id']['_id'] ?? '') }}" class="btn btn-outline">
                <i class="bx bx-eye align-middle"></i>
                Lihat Detail Event
            </a>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
@endsection
