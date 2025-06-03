@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <style>
       

        .success-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: successPulse 2s ease-in-out infinite;
        }

        .success-icon i {
            font-size: 36px;
            color: white;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success-title {
            font-size: 28px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 10px;
        }

        .success-subtitle {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
        }

        .registration-details {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            font-weight: 500;
            color: #212529;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .next-steps {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .next-steps h4 {
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .steps-list li {
            padding: 8px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .step-number {
            background: #007bff;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,123,255,0.3);
            color: white;
            text-decoration: none;
        }

        .btn-outline {
            background: white;
            border: 2px solid #007bff;
            color: #007bff;
        }

        .btn-outline:hover {
            background: #007bff;
            color: white;
            text-decoration: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40,167,69,0.3);
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 15px;
            }

            .registration-details {
                padding: 20px;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="registration-container">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">
                <i class="bx bx-check"></i>
            </div>
            <h1 class="success-title">Registrasi Berhasil!</h1>
            <p class="success-subtitle">
                @if(($registration['payment_amount'] ?? 0) > 0)
                    Terima kasih! Registrasi Anda telah berhasil disubmit. Silakan tunggu verifikasi pembayaran dari tim kami.
                @else
                    Selamat! Anda telah berhasil terdaftar untuk event ini.
                @endif
            </p>
        </div>

        <!-- Registration Details -->
        <div class="registration-details">
            <h3 class="section-title" style="margin-bottom: 20px;">
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