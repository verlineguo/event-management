@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">

@endsection

@section('content')
    <div class="registration-container">
        <!-- Success Header -->
        <div class="header">
            <h1 class="title">Registrasi Berhasil!</h1>
            <p class="subtitle">
                @if(($registration['payment_amount'] ?? 0) > 0)
                    Terima kasih! Registrasi Anda telah berhasil disubmit. Silakan tunggu verifikasi pembayaran dari tim kami.
                @else
                    Selamat! Anda telah berhasil terdaftar untuk event ini.
                @endif
            </p>
        </div>

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
                        $status = $registration['status'] ?? 'pending';
                        $statusClass = 'status-pending';
                        $statusText = 'Menunggu Verifikasi';
                        
                        if ($status === 'confirmed') {
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
        @if(($registration['payment_amount'] ?? 0) > 0 && ($registration['status'] ?? 'pending') === 'pending')
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
        @elseif(($registration['status'] ?? 'pending') === 'confirmed')
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

            @if(($registration['status'] ?? 'pending') === 'confirmed' || ($registration['payment_amount'] ?? 0) == 0)
                <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}" class="btn btn-success">
                    <i class="bx bx-qrcode"></i>
                    Lihat QR Code
                </a>
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
@endsection