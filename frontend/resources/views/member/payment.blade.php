@extends('layouts.member')

@section('title', 'Pembayaran - ' . ($event['name'] ?? 'Event'))

@section('styles')

    <style>
        .registration-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .progress-bar-container {
            margin-bottom: 30px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step.active .step-number {
            background: #007bff;
            color: white;
        }

        .step.inactive .step-number {
            background: #e9ecef;
            color: #6c757d;
        }

        .step-title {
            font-size: 12px;
            text-align: center;
        }

        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #007bff;
            transition: width 0.3s ease;
        }

        .event-summary {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .event-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .event-meta {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #666;
        }

        .event-meta i {
            width: 20px;
            margin-right: 10px;
        }

        .registration-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

        .section-title i {
            margin-right: 10px;
            color: #007bff;
        }

        .payment-summary {
            margin-bottom: 30px;
        }

        .selected-session-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }

        .session-title {
            font-weight: 500;
            margin: 5px 0;
        }

        .session-details-small {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }

        .session-details-small span {
            display: flex;
            align-items: center;
        }

        .session-details-small i {
            margin-right: 5px;
        }

        .payment-details {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-row {
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 18px;
        }

        .payment-info {
            margin-bottom: 30px;
        }

        .bank-details {
            margin: 20px 0;
        }

        .bank-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .bank-item h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .account-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .account-number {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .copy-btn {
            background: #007bff;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .copy-btn:hover {
            background: #0056b3;
        }

        .account-name {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .payment-notes ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .payment-notes li {
            margin-bottom: 5px;
            color: #666;
        }

        .payment-upload {
            margin-bottom: 30px;
        }

        .upload-area {
            position: relative;
        }

        #payment_proof {
            display: none;
        }

        .upload-label {
            display: block;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-label:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .upload-content i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 15px;
        }

        .upload-content p {
            margin: 5px 0;
        }

        .upload-note {
            font-size: 14px;
            color: #666;
        }

        .file-preview {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }

        .preview-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-content i {
            font-size: 24px;
            color: #007bff;
        }

        .file-name {
            flex: 1;
            font-weight: 500;
        }

        .remove-file {
            background: #dc3545;
            border: none;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
        }

        .free-event-message {
            margin-bottom: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: space-between;
        }

        .btn-back, .btn-continue {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
        }

        .btn-back:hover {
            background: #545b62;
            color: white;
        }

        .btn-continue {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-continue:hover {
            background: #218838;
        }

        .btn-continue:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .registration-container {
                padding: 15px;
            }

            .session-details-small {
                flex-direction: column;
                gap: 5px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .account-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
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
                <i class="fas fa-calendar-alt"></i>
                <span>{{ $event['date_range'] ?? 'Date Range' }}</span>
            </div>
            <div class="event-meta">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $event['display_location'] ?? 'Location' }}</span>
            </div>
        </div>

        <form id="paymentForm" method="POST" action="{{ route('member.events.process-payment', $event['_id']) }}" enctype="multipart/form-data">
            @csrf

            <div class="registration-card">
                <!-- Registration Summary -->
                <div class="payment-summary">
                    <h3 class="section-title">
                        <i class="fas fa-receipt"></i>
                        Ringkasan Pendaftaran
                    </h3>
                    
                    <div class="summary-content">
                        <div class="selected-sessions">
                            <h4 style="margin-bottom: 15px;">Sesi yang Dipilih:</h4>
                            @if(session('registration_data.selected_sessions'))
                                @foreach($event['sessions'] as $session)
                                    @if(in_array($session['_id'], session('registration_data.selected_sessions')))
                                        <div class="selected-session-item">
                                            <div class="session-info">
                                                <h5>Session {{ $session['session_order'] ?? $loop->iteration }}</h5>
                                                @if(isset($session['title']) && $session['title'])
                                                    <p class="session-title">{{ $session['title'] }}</p>
                                                @endif
                                                <div class="session-details-small">
                                                    <span><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}</span>
                                                    <span><i class="fas fa-clock"></i> {{ $session['start_time'] }} - {{ $session['end_time'] }}</span>
                                                    <span><i class="fas fa-map-marker-alt"></i> {{ $session['location'] }}</span>
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
                                    @if((session('registration_data.payment_amount', 0)) == 0)
                                        GRATIS
                                    @else
                                        Rp {{ number_format(session('registration_data.payment_amount', 0)) }}
                                    @endif
                                </span>
                            </div>
                            <div class="summary-row total-row">
                                <span><strong>Total Pembayaran:</strong></span>
                                <span class="total-amount">
                                    @if((session('registration_data.payment_amount', 0)) == 0)
                                        <strong>GRATIS</strong>
                                    @else
                                        <strong>Rp {{ number_format(session('registration_data.payment_amount', 0)) }}</strong>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if((session('registration_data.payment_amount', 0)) > 0)
                    <!-- Payment Information -->
                    <div class="payment-info">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i>
                            Informasi Pembayaran
                        </h3>
                        
                        <div class="bank-info">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Silakan lakukan pembayaran ke rekening berikut:</strong>
                            </div>
                            
                            <div class="bank-details">
                                <div class="bank-item">
                                    <h4>Bank BCA</h4>
                                    <div class="account-info">
                                        <span class="account-number">1234567890</span>
                                        <button type="button" class="copy-btn" onclick="copyToClipboard('1234567890')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <p class="account-name">a.n. Panitia Event</p>
                                </div>
                                
                                <div class="bank-item">
                                    <h4>Bank Mandiri</h4>
                                    <div class="account-info">
                                        <span class="account-number">0987654321</span>
                                        <button type="button" class="copy-btn" onclick="copyToClipboard('0987654321')">
                                            <i class="fas fa-copy"></i>
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
                            <i class="fas fa-upload"></i>
                            Upload Bukti Pembayaran
                        </h3>
                        
                        <div class="upload-area">
                            <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf" required>
                            <label for="payment_proof" class="upload-label">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Klik untuk upload bukti pembayaran</p>
                                    <p class="upload-note">Format: JPG, PNG, PDF (Max: 2MB)</p>
                                </div>
                            </label>
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <div class="preview-content">
                                    <i class="fas fa-file"></i>
                                    <span class="file-name"></span>
                                    <button type="button" class="remove-file" onclick="removeFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        @error('payment_proof')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                @else
                    <!-- Free Event Message -->
                    <div class="free-event-message">
                        <div class="alert alert-success">
                            <i class="fas fa-gift"></i>
                            <strong>Event Gratis!</strong> Anda tidak perlu melakukan pembayaran untuk event ini.
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('member.events.register', $event['_id']) }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                    <button type="submit" class="btn-continue" id="submitBtn">
                        <i class="fas fa-check"></i>
                        @if((session('registration_data.payment_amount', 0)) > 0)
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
        document.getElementById('payment_proof').addEventListener('change', function(e) {
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

        function removeFile() {
            document.getElementById('payment_proof').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.querySelector('.upload-label').style.display = 'block';
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show temporary success message
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
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
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses Pembayaran...';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyelesaikan Pendaftaran...';
            }
            submitBtn.disabled = true;
        });
    </script>
@endsection