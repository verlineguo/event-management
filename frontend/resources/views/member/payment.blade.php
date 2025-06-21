@extends('member.layouts.app')



@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">

@endsection

@section('content')
    <div class="registration-container">
        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-number">✓</div>
                    <div class="step-title">Pilih Sesi</div>
                    <div class="step-connector"></div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-title">Pembayaran</div>
                    <div class="step-connector"></div>
                </div>
                <div class="step inactive">
                    <div class="step-number">3</div>
                    <div class="step-title">Konfirmasi</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 66.66%"></div>
            </div>
        </div>

        <!-- Event Summary -->
        <div class="event-summary">
            @if (isset($event['poster']) && !empty($event['poster']))
                <div style="text-align:center; margin-bottom:20px;">
                    <img src="{{ asset('storage/' . $event['poster']) }}" alt="Poster Event"
                        style="max-width:300px; width:100%; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                </div>
            @endif
            <div class="event-title">{{ $event['name'] ?? 'Event Name' }}</div>
            <div class="event-meta">
                <i class="bx bx-calendar-alt"></i>
                <span>{{ $event['date_range'] ?? 'Date Range' }}</span>
            </div>
            <div class="event-meta">
                <i class="bx bx-location-alt-2"></i> 
                <span>{{ $event['display_location'] ?? 'Location' }}</span>
            </div>
        </div>

        <form id="paymentForm" method="POST" action="{{ route('member.events.process-payment', $event['_id']) }}"
            enctype="multipart/form-data">
            @csrf

            <div class="registration-card">
                <!-- Registration Summary -->
                <div class="payment-summary">
                    <h3 class="section-title">
                        <i class="bx bx-receipt"></i>
                        Ringkasan Pendaftaran
                    </h3>

                    <div class="summary-content">
                        <div class="selected-sessions">
                            <h4 style="margin-bottom: 15px;">Sesi yang Dipilih:</h4>
                            @if (session('registration_data.selected_sessions'))
                                @foreach ($event['sessions'] as $session)
                                    @if (in_array($session['_id'], session('registration_data.selected_sessions')))
                                        <div class="selected-session-item">
                                            <div class="session-info">
                                                <h5>Session {{ $session['session_order'] ?? $loop->iteration }}</h5>
                                                @if (isset($session['title']) && $session['title'])
                                                    <p class="session-title">{{ $session['title'] }}</p>
                                                @endif
                                                <div class="session-details-small">
                                                    <span><i class="bx bx-calendar"></i>
                                                        {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}</span>
                                                    <span><i class="bx bx-clock"></i> {{ $session['start_time'] }} -
                                                        {{ $session['end_time'] }}</span>
                                                    <span><i class="bx bx-map-marker-alt"></i>
                                                        {{ $session['location'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="payment-details">
                            <div class="summary-row">
                                <span>Jumlah Sesi:</span>
                                <span>{{ count(session('registration_data.selected_sessions', [])) }} sesi</span>
                            </div>
                            <div class="summary-row">
                                <span>Biaya Pendaftaran:</span>
                                <span class="fee-amount">
                                    @if (session('registration_data.payment_amount', 0) == 0)
                                        GRATIS
                                    @else
                                        Rp {{ number_format(session('registration_data.payment_amount', 0)) }}
                                    @endif
                                </span>
                            </div>
                            <div class="summary-row total-row">
                                <span><strong>Total Pembayaran:</strong></span>
                                <span class="total-amount">
                                    @if (session('registration_data.payment_amount', 0) == 0)
                                        <strong>GRATIS</strong>
                                    @else
                                        <strong>Rp
                                            {{ number_format(session('registration_data.payment_amount', 0)) }}</strong>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('registration_data.payment_amount', 0) > 0)
                    <!-- Payment Information -->
                    <div class="payment-info">
                        <h3 class="section-title">
                            <i class="bx bx-credit-card"></i>
                            Informasi Pembayaran
                        </h3>

                        <div class="bank-info">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle"></i>
                                <strong>Silakan lakukan pembayaran ke rekening berikut:</strong>
                            </div>

                            <div class="bank-details">
                                <div class="bank-item">
                                    <h4>Bank BCA</h4>
                                    <div class="account-info">
                                        <span class="account-number">1234567890</span>
                                        <button type="button" class="copy-btn" onclick="copyToClipboard('1234567890')">
                                            <i class="bx bx-copy"></i>
                                        </button>
                                    </div>
                                    <p class="account-name">a.n. Panitia Event</p>
                                </div>

                                <div class="bank-item">
                                    <h4>Bank Mandiri</h4>
                                    <div class="account-info">
                                        <span class="account-number">0987654321</span>
                                        <button type="button" class="copy-btn" onclick="copyToClipboard('0987654321')">
                                            <i class="bx bx-copy"></i>
                                        </button>
                                    </div>
                                    <p class="account-name">a.n. Panitia Event</p>
                                </div>
                            </div>

                            <div class="payment-notes">
                                <h4>Catatan Pembayaran:</h4>
                                <ul>
                                    <li>Pastikan nominal pembayaran sesuai dengan total yang tertera</li>
                                    <li>Simpan bukti transfer untuk diunggah di bawah</li>
                                    <li>Pembayaran akan diverifikasi dalam 1x24 jam</li>
                                    <li>Jika ada kendala, hubungi panitia melalui WhatsApp</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proof Upload -->
                    <div class="payment-upload">
                        <h3 class="section-title">
                            <i class="bx bx-upload"></i>
                            Upload Bukti Pembayaran
                        </h3>

                        <div class="upload-area">
                            <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf"
                                required>
                            <label for="payment_proof" class="upload-label">
                                <div class="upload-content">
                                    <i class="bx bx-cloud-upload-alt"></i>
                                    <p>Klik untuk upload bukti pembayaran</p>
                                    <p class="upload-note">Format: JPG, PNG, PDF (Max: 2MB)</p>
                                </div>
                            </label>
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <div class="preview-content">
                                    <i class="bx bx-file"></i>
                                    <span class="file-name"></span>
                                    <button type="button" class="remove-file" onclick="removeFile()">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('payment_proof')
                            <div class="error-message">
                                <i class="bx bx-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                @else
                    <!-- Free Event Message -->
                    <div class="free-event-message">
                        <div class="alert alert-success">
                            <i class="bx bx-gift"></i>
                            <strong>Event Gratis!</strong> Anda tidak perlu melakukan pembayaran untuk event ini.
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('member.events.register', $event['_id']) }}" class="btn-back">
                        <i class="bx bx-arrow-left"></i>
                        Kembali
                    </a>
                    <button type="submit" class="btn-continue" id="submitBtn">
                        <i class="bx bx-check"></i>
                        @if (session('registration_data.payment_amount', 0) > 0)
                            Konfirmasi Pembayaran
                        @else
                            Selesaikan Pendaftaran
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>


    <script>
       const paymentProofElement = document.getElementById('payment_proof');
    if (paymentProofElement) {
        paymentProofElement.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            const fileName = preview.querySelector('.file-name');
            const uploadLabel = document.querySelector('.upload-label');

            if (file) {
                fileName.textContent = file.name;
                preview.style.display = 'block';
                uploadLabel.style.display = 'none';
            }
        });
    }

        function removeFile() {
        const paymentProofElement = document.getElementById('payment_proof');
        if (paymentProofElement) {
            paymentProofElement.value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.querySelector('.upload-label').style.display = 'block';
        }
    }
    
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show temporary success message
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-check"></i>';
                btn.style.background = '#28a745';

                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.background = '#007bff';
                }, 1000);
            });
        }

        // Form submission handler
        document.getElementById('paymentForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const paymentAmount = {{ session('registration_data.payment_amount', 0) }};

            if (paymentAmount > 0) {
                submitBtn.innerHTML = '<i class="bx bx-spinner fa-spin"></i> Memproses Pembayaran...';
            } else {
                submitBtn.innerHTML = '<i class="bx bx-spinner fa-spin"></i> Menyelesaikan Pendaftaran...';
            }
            submitBtn.disabled = true;
        });
    </script>
@endsection
