@extends('committee.layouts.app')

@section('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/qr.css') }}">

@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">QR Code Scanner</h4>
                        <p class="text-muted mb-0">Event: <strong>{{ $data['event']['name'] }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('committee.event.participants', $data['event']['_id']) }}"
                            class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                        <button class="btn btn-primary" id="switchCamera">
                            <i class="bx bx-refresh me-1"></i>Switch Camera
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Scanner Section -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-qr-scan me-2"></i>QR Code Scanner
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="scanner-container">
                            <!-- Scanner Status -->
                            <div class="alert alert-info text-center mb-3" id="scannerStatus">
                                <i class="bx bx-info-circle me-2"></i>
                                Click "Start Scanner" to begin scanning QR codes
                            </div>

                            <!-- Scanner Controls -->
                            <div class="text-center mb-3 scanner-controls">
                                <button class="btn btn-success btn-lg" id="startScanner">
                                    <i class="bx bx-play me-2"></i>Start Scanner
                                </button>
                                <button class="btn btn-danger btn-lg d-none" id="stopScanner">
                                    <i class="bx bx-stop me-2"></i>Stop Scanner
                                </button>
                            </div>

                            <!-- QR Reader Container dengan Preview -->
                            <div class="qr-reader-wrapper" id="qrReaderWrapper">
                                <div id="qr-reader" class="qr-reader d-none">
                                    <!-- QR Frame Overlay -->
                                    <div class="qr-frame-overlay">
                                        <div class="qr-frame">
                                            <div class="qr-corner top-left"></div>
                                            <div class="qr-corner top-right"></div>
                                            <div class="qr-corner bottom-left"></div>
                                            <div class="qr-corner bottom-right"></div>
                                        </div>
                                        <div class="qr-instruction">
                                            <p>Arahkan QR code ke dalam frame</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Entry Alternative -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-center">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#manualEntry">
                                        <i class="bx bx-edit me-1"></i>Manual Entry
                                    </button>
                                </div>
                                <div class="collapse mt-3" id="manualEntry">
                                    <div class="card">
                                        <div class="card-body">
                                            <form id="manualScanForm">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="manualQRInput"
                                                        name="qr_token" placeholder="Enter QR code token manually..."
                                                        required>
                                                    <input type="hidden" name="event_id" value="{{ $data['event']['_id'] }}">
                                                    <button class="btn btn-primary" type="submit">
                                                        <i class="bx bx-check"></i>Submit
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="col-lg-4">
                <!-- Current Scan Result -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-check-circle me-2"></i>Scan Result
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="scanResult" class="text-center text-muted py-3">
                            <i class="bx bx-qr-scan" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">No scan yet</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-success mb-1" id="successCount">0</h4>
                                <small class="text-muted">Successful</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger mb-1" id="errorCount">0</h4>
                                <small class="text-muted">Errors</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Scans -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-history me-2"></i>Recent Scans
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentScans" class="recent-scans">
                            <div class="text-center text-muted py-3">
                                <i class="bx bx-time" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No recent scans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-check-circle me-2"></i>Check-in Successful
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <div id="successModalContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Scanning</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-x-circle me-2"></i>Scan Error
                    </h5>
                </div>
                <div class="modal-body">
                    <div id="errorModalContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Try Again</button>
                </div>
            </div>
        </div>
    </div>
@endsection



@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        class QRScanner {
            constructor() {
                this.html5QrCode = null;
                this.isScanning = false;
                this.successCount = 0;
                this.errorCount = 0;
                this.currentCameraId = null;
                this.cameras = [];
                this.eventId = '{{ $data['event']['_id'] }}';

                this.initializeElements();
                this.bindEvents();
                this.loadCameras();
            }

            initializeElements() {
                this.startBtn = document.getElementById('startScanner');
                this.stopBtn = document.getElementById('stopScanner');
                this.switchBtn = document.getElementById('switchCamera');
                this.statusDiv = document.getElementById('scannerStatus');
                this.readerDiv = document.getElementById('qr-reader');
                this.resultDiv = document.getElementById('scanResult');
                this.recentScansDiv = document.getElementById('recentScans');
                this.successCountSpan = document.getElementById('successCount');
                this.errorCountSpan = document.getElementById('errorCount');
                this.manualForm = document.getElementById('manualScanForm');
                this.qrFrame = document.querySelector('.qr-frame');
            }

            bindEvents() {
                this.startBtn.addEventListener('click', () => this.startScanner());
                this.stopBtn.addEventListener('click', () => this.stopScanner());
                this.switchBtn.addEventListener('click', () => this.switchCamera());

                // Handle manual form submission
                this.manualForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitManualQR();
                });
            }

            async loadCameras() {
                try {
                    const devices = await Html5Qrcode.getCameras();
                    this.cameras = devices;

                    if (devices.length > 0) {
                        this.currentCameraId = devices[0].id;
                        this.switchBtn.disabled = devices.length <= 1;
                    } else {
                        this.updateStatus('No cameras found', 'warning');
                    }
                } catch (err) {
                    console.error('Error loading cameras:', err);
                    this.updateStatus('Error accessing cameras', 'danger');
                }
            }

            async startScanner() {
                if (this.isScanning) return;

                try {
                    this.html5QrCode = new Html5Qrcode("qr-reader");

                    const config = {
                        fps: 10,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            // Make qr box responsive but keep it within frame
                            let minEdgePercentage = 0.5;
                            let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                            return {
                                width: Math.min(qrboxSize, 250),
                                height: Math.min(qrboxSize, 250)
                            };
                        },
                        aspectRatio: 1.0,
                        // Show video feed
                        rememberLastUsedCamera: true,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                    };

                    await this.html5QrCode.start(
                        this.currentCameraId || { facingMode: "environment" },
                        config,
                        (decodedText, decodedResult) => {
                            this.onScanSuccess(decodedText, decodedResult);
                        },
                        (errorMessage) => {
                            // Ignore frequent scan errors - these are normal
                        }
                    );

                    this.isScanning = true;
                    this.startBtn.classList.add('d-none');
                    this.stopBtn.classList.remove('d-none');
                    this.readerDiv.classList.remove('d-none');
                    this.updateStatus('Scanner active - Arahkan QR code ke dalam frame', 'success');

                } catch (err) {
                    console.error('Error starting scanner:', err);
                    this.updateStatus('Error starting scanner: ' + err.message, 'danger');
                }
            }

            async stopScanner() {
                if (!this.isScanning || !this.html5QrCode) return;

                try {
                    await this.html5QrCode.stop();
                    this.html5QrCode.clear();
                    this.html5QrCode = null;

                    this.isScanning = false;
                    this.startBtn.classList.remove('d-none');
                    this.stopBtn.classList.add('d-none');
                    this.readerDiv.classList.add('d-none');
                    this.updateStatus('Scanner stopped', 'info');

                } catch (err) {
                    console.error('Error stopping scanner:', err);
                }
            }

            async switchCamera() {
                if (this.cameras.length <= 1) return;

                const currentIndex = this.cameras.findIndex(cam => cam.id === this.currentCameraId);
                const nextIndex = (currentIndex + 1) % this.cameras.length;
                this.currentCameraId = this.cameras[nextIndex].id;

                if (this.isScanning) {
                    await this.stopScanner();
                    setTimeout(() => this.startScanner(), 500);
                }
            }

            async onScanSuccess(decodedText, decodedResult) {
                console.log('QR Code scanned:', decodedText);

                // Add success flash effect
                if (this.qrFrame) {
                    this.qrFrame.classList.add('scan-success');
                    setTimeout(() => {
                        this.qrFrame.classList.remove('scan-success');
                    }, 500);
                }

                // Process the scanned QR code
                await this.processQRCode(decodedText);

                // Brief pause before continuing
                if (this.html5QrCode) {
                    await this.html5QrCode.pause(true);
                    setTimeout(() => {
                        if (this.html5QrCode && this.isScanning) {
                            this.html5QrCode.resume();
                        }
                    }, 2000);
                }
            }

            async processQRCode(qrToken) {
                try {
                    this.updateScanResult('Processing...', 'info');

                    // Send to Laravel controller
                    const formData = new FormData();
                    formData.append('qr_token', qrToken);
                    formData.append('event_id', this.eventId);
                    formData.append('_token', '{{ csrf_token() }}');

                    const response = await fetch('{{ route('committee.event.process-scan') }}', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.handleScanSuccess(result);
                    } else {
                        this.handleScanError(result.message || 'Scan failed');
                    }

                } catch (error) {
                    console.error('Error processing QR code:', error);
                    this.handleScanError('Network error occurred');
                }
            }

            async submitManualQR() {
                const formData = new FormData(this.manualForm);

                try {
                    this.updateScanResult('Processing manual entry...', 'info');

                    const response = await fetch('{{ route('committee.event.process-manual') }}', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.handleScanSuccess(result);
                        this.manualForm.reset();
                        // Collapse manual entry
                        const collapse = new bootstrap.Collapse(document.getElementById('manualEntry'));
                        collapse.hide();
                    } else {
                        this.handleScanError(result.message || 'Manual scan failed');
                    }

                } catch (error) {
                    console.error('Error processing manual QR:', error);
                    this.handleScanError('Network error occurred');
                }
            }

            handleScanSuccess(result) {
                this.successCount++;
                this.successCountSpan.textContent = this.successCount;

                const participant = result.participant;

                // Update scan result
                this.updateScanResult(`
                    <div class="text-success">
                        <i class="bx bx-check-circle" style="font-size: 3rem;"></i>
                        <h6 class="mt-2 mb-1">${participant.name}</h6>
                        <small class="text-muted">${participant.email}</small>
                    </div>
                `, 'success');

                // Add to recent scans
                this.addRecentScan({
                    name: participant.name,
                    email: participant.email,
                    session: participant.session.title,
                    time: new Date().toLocaleTimeString(),
                    status: 'success'
                });

                // Show success modal
                this.showSuccessModal(participant);

                // Show Laravel success message
                this.showAlert('success', 'Check-in berhasil untuk ' + participant.name);
            }

            handleScanError(errorMessage) {
                this.errorCount++;
                this.errorCountSpan.textContent = this.errorCount;

                // Update scan result
                this.updateScanResult(`
                    <div class="text-danger">
                        <i class="bx bx-x-circle" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">Scan Failed</p>
                        <small>${errorMessage}</small>
                    </div>
                `, 'danger');

                // Add to recent scans
                this.addRecentScan({
                    error: errorMessage,
                    time: new Date().toLocaleTimeString(),
                    status: 'error'
                });

                // Show error modal
                this.showErrorModal(errorMessage);

                // Show Laravel error message
                this.showAlert('error', errorMessage);
            }

            updateScanResult(content, type) {
                this.resultDiv.innerHTML = content;
                this.resultDiv.className = `scan-result text-center py-3 ${type === 'success' ? 'success-animation' : ''}`;
            }

            addRecentScan(scanData) {
                // Remove "no recent scans" message if present
                if (this.recentScansDiv.querySelector('.text-muted')) {
                    this.recentScansDiv.innerHTML = '';
                }

                const scanItem = document.createElement('div');
                scanItem.className = `scan-item p-3 mb-2 ${scanData.status === 'success' ? 'bg-light-success' : 'bg-light-danger'}`;

                if (scanData.status === 'success') {
                    scanItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${scanData.name}</h6>
                                <small class="text-muted">${scanData.email}</small><br>
                                <small class="text-info">${scanData.session}</small>
                            </div>
                            <div class="text-end">
                                <i class="bx bx-check-circle text-success"></i><br>
                                <small class="text-muted">${scanData.time}</small>
                            </div>
                        </div>
                    `;
                } else {
                    scanItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-danger">Scan Error</h6>
                                <small class="text-muted">${scanData.error}</small>
                            </div>
                            <div class="text-end">
                                <i class="bx bx-x-circle text-danger"></i><br>
                                <small class="text-muted">${scanData.time}</small>
                            </div>
                        </div>
                    `;
                }

                this.recentScansDiv.insertBefore(scanItem, this.recentScansDiv.firstChild);

                // Keep only last 10 scans
                const scanItems = this.recentScansDiv.querySelectorAll('.scan-item');
                if (scanItems.length > 10) {
                    scanItems[scanItems.length - 1].remove();
                }
            }

            showSuccessModal(participant) {
                const modalContent = document.getElementById('successModalContent');
                modalContent.innerHTML = `
                    <div class="mb-3">
                        <i class="bx bx-user-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5>${participant.name}</h5>
                    <p class="text-muted mb-3">${participant.email}</p>
                    <div class="row text-start">
                        <div class="col-4"><strong>Session:</strong></div>
                        <div class="col-8">${participant.session.title}</div>
                        <div class="col-4"><strong>Date:</strong></div>
                        <div class="col-8">${new Date(participant.session.date).toLocaleDateString()}</div>
                        <div class="col-4"><strong>Time:</strong></div>
                        <div class="col-8">${participant.session.start_time} - ${participant.session.end_time}</div>
                        <div class="col-4"><strong>Check-in:</strong></div>
                        <div class="col-8">${new Date(participant.checked_in_at).toLocaleString()}</div>
                    </div>
                `;

                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
            }

            showErrorModal(errorMessage) {
                const modalContent = document.getElementById('errorModalContent');
                modalContent.innerHTML = `
                    <div class="text-center mb-3">
                        <i class="bx bx-error text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> ${errorMessage}
                    </div>
                    <p class="text-muted">Please try scanning the QR code again or use manual entry.</p>
                `;

                const modal = new bootstrap.Modal(document.getElementById('errorModal'));
                modal.show();
            }

            updateStatus(message, type) {
                this.statusDiv.innerHTML = `<i class="bx bx-info-circle me-2"></i>${message}`;
                this.statusDiv.className = `alert alert-${type} text-center mb-3`;
            }

            showAlert(type, message) {
                // Create toast notification
                const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
                const toast = this.createToast(type, message);
                toastContainer.appendChild(toast);
                
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // Remove toast after it's hidden
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            }

            createToastContainer() {
                const container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '1060';
                document.body.appendChild(container);
                return container;
            }

            createToast(type, message) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.setAttribute('data-bs-delay', '3000');

                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                
                toast.innerHTML = `
                    <div class="toast-header ${bgClass} text-white">
                        <i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                `;
                
                return toast;
            }
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const scanner = new QRScanner();
        });
    </script>
@endsection