@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
  
@endsection

@section('content')
    <div class="registration-container">
        <!-- QR Header -->
        <div class="header">
            <h1 class="title">
                <i class="bx bx-qr"></i>
                QR Code Check-in
            </h1>
            <p class="subtitle">Tunjukkan QR code ini saat check-in di setiap sesi event</p>
        </div>

        <!-- Event Information -->
        <div class="registration-details">
            <h3>
                <i class="bx bx-info-circle"></i>
                Informasi Event
            </h3>
            
            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-calendar-check"></i>
                    Nama Event
                </span>
                <span class="detail-value">{{ $qrData['registration']['event_id']['name'] ?? 'Event Name' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-hash"></i>
                    ID Registrasi
                </span>
                <span class="detail-value">{{ $qrData['registration']['_id'] ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bx-user"></i>
                    Nama Peserta
                </span>
                <span class="detail-value">{{ $qrData['registration']['user_id']['name'] ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">
                    <i class="bx bxs-layer-plus"></i>
                    Total Sesi
                </span>
                <span class="detail-value">{{ count($qrData['qr_codes'] ?? []) }} sesi</span>
            </div>
        </div>

        <!-- Usage Instructions -->
        <div class="usage-instructions">
            <h4>
                <i class="bx bx-info-circle"></i>
                Petunjuk Penggunaan QR Code
            </h4>
            <ul>
                <li>Tunjukkan QR code yang sesuai dengan sesi yang akan Anda hadiri</li>
                <li>Datang 15 menit sebelum sesi dimulai untuk proses check-in</li>
                <li>Pastikan layar ponsel Anda terang agar QR code mudah terbaca</li>
                <li>Setiap QR code hanya dapat digunakan sekali per sesi</li>
                <li>Simpan screenshot QR code sebagai backup</li>
            </ul>
        </div>

        <!-- QR Codes Grid -->
        @if(isset($qrData['qr_codes']) && count($qrData['qr_codes']) > 0)
            <div class="qr-grid">
                @foreach($qrData['qr_codes'] as $qrCode)
                    <div class="qr-card">
                        <!-- Session Header -->
                        <div class="qr-card-header">
                            <h4 class="session-title">{{ $qrCode['session']['title'] ?? 'Session Title' }}</h4>
                            <div class="session-details">
                                <div class="session-detail">
                                    <i class="bx bx-calendar"></i>
                                    <span>{{ isset($qrCode['session']['date']) ? \Carbon\Carbon::parse($qrCode['session']['date'])->format('d M Y') : 'Date' }}</span>
                                </div>
                                <div class="session-detail">
                                    <i class="bx bx-time"></i>
                                    <span>{{ $qrCode['session']['start_time'] ?? 'Start' }} - {{ $qrCode['session']['end_time'] ?? 'End' }}</span>
                                </div>
                                <div class="session-detail">
                                    <i class="bx bx-location-plus"></i>
                                    <span>{{ $qrCode['session']['location'] ?? 'Location' }}</span>
                                </div>
                                @if(isset($qrCode['session']['speaker']))
                                    <div class="session-detail">
                                        <i class="bx bx-user-voice"></i>
                                        <span>{{ $qrCode['session']['speaker'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- QR Code Body -->
                        <div class="qr-card-body">
                            <div class="qr-code-container">
                                <img src="{{ $qrCode['qr_code'] }}" alt="QR Code" class="qr-code-image">
                            </div>
                            
                            <div class="qr-status {{ $qrCode['qr_used'] ? 'qr-used' : 'qr-unused' }}">
                                @if($qrCode['qr_used'])
                                    <i class="bx bx-check-circle"></i>
                                    Sudah Digunakan
                                    @if(isset($qrCode['used_at']))
                                        <br><small>{{ \Carbon\Carbon::parse($qrCode['used_at'])->format('d M Y, H:i') }}</small>
                                    @endif
                                @else
                                    <i class="bx bx-time-five"></i>
                                    Siap Digunakan
                                @endif
                            </div>

                            <!-- QR Actions -->
                            <div class="qr-actions">
                                <button onclick="downloadQR('{{ $qrCode['qr_code'] }}', '{{ $qrCode['session']['title'] ?? 'session' }}')" class="btn btn-download">
                                    <i class="bx bx-download"></i>
                                    Download
                                </button>
                                <button onclick="printQR('qr-card-{{ $loop->index }}')" class="btn btn-print">
                                    <i class="bx bx-printer"></i>
                                    Print
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bx bx-qr-code"></i>
                </div>
                <h3 class="empty-title">QR Code Belum Tersedia</h3>
                <p class="empty-description">
                    QR Code akan tersedia setelah registrasi Anda dikonfirmasi.
                </p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('member.myRegistrations.show', $qrData['registration']['_id']) }}" class="btn btn-primary">
                <i class="bx bx-eye"></i>
                Lihat Detail Registrasi
            </a>
            
            <a href="{{ route('member.myRegistrations.index') }}" class="btn btn-outline">
                <i class="bx bx-list"></i>
                Semua Registrasi
            </a>
            
            <button onclick="window.print()" class="btn btn-outline">
                <i class="bx bx-printer"></i>
                Print Semua QR
            </button>
        </div>
    </div>

    <script>
        // Function to download QR code
        function downloadQR(qrCodeDataUrl, sessionTitle) {
            const link = document.createElement('a');
            link.href = qrCodeDataUrl;
            link.download = `QR-${sessionTitle.replace(/[^a-z0-9]/gi, '_')}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Function to print specific QR card
        function printQR(cardId) {
            const printContent = document.getElementById(cardId);
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent.outerHTML;
            window.print();
            document.body.innerHTML = originalContent;
            
            // Reload to restore event listeners
            location.reload();
        }

        // Add IDs to QR cards for printing
        document.addEventListener('DOMContentLoaded', function() {
            const qrCards = document.querySelectorAll('.qr-card');
            qrCards.forEach((card, index) => {
                card.id = `qr-card-${index}`;
            });
        });
    </script>
@endsection