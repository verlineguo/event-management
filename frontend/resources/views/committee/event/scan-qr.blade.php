<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner - Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        .scanner-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .qr-reader {
            border: 2px solid #007bff;
            border-radius: 10px;
            overflow: hidden;
        }

        .scan-result {
            transition: all 0.3s ease;
        }

        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .recent-scans {
            max-height: 400px;
            overflow-y: auto;
        }

        .scan-item {
            border-left: 4px solid #28a745;
            transition: all 0.2s ease;
        }

        .scan-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .scanner-controls {
            position: relative;
            z-index: 10;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1">QR Code Scanner</h4>
                                <p class="text-muted mb-0">Event: <strong>Workshop Web Development</strong></p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="history.back()">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </button>
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

                                    <!-- QR Reader Container -->
                                    <div id="qr-reader" class="qr-reader d-none"></div>

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
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="manualQRInput"
                                                            placeholder="Enter QR code token manually...">
                                                        <button class="btn btn-primary" id="submitManualQR">
                                                            <i class="bx bx-check"></i>Submit
                                                        </button>
                                                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        class QRScanner {
            constructor() {
                this.html5QrCode = null;
                this.isScanning = false;
                this.successCount = 0;
                this.errorCount = 0;
                this.currentCameraId = null;
                this.cameras = [];

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
                this.manualInput = document.getElementById('manualQRInput');
                this.submitManualBtn = document.getElementById('submitManualQR');
            }

            bindEvents() {
                this.startBtn.addEventListener('click', () => this.startScanner());
                this.stopBtn.addEventListener('click', () => this.stopScanner());
                this.switchBtn.addEventListener('click', () => this.switchCamera());
                this.submitManualBtn.addEventListener('click', () => this.submitManualQR());

                this.manualInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.submitManualQR();
                    }
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
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                        aspectRatio: 1.0
                    };

                    await this.html5QrCode.start(
                        this.currentCameraId || {
                            facingMode: "environment"
                        },
                        config,
                        (decodedText, decodedResult) => {
                            this.onScanSuccess(decodedText, decodedResult);
                        },
                        (errorMessage) => {
                            // Ignore frequent scan errors
                        }
                    );

                    this.isScanning = true;
                    this.startBtn.classList.add('d-none');
                    this.stopBtn.classList.remove('d-none');
                    this.readerDiv.classList.remove('d-none');
                    this.updateStatus('Scanner active - Point camera at QR code', 'success');

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

                    // Send QR token to backend for verification
                    const response = await fetch('/api/scan-qr', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` // Adjust based on your auth system
                        },
                        body: JSON.stringify({
                            qr_token: qrToken,
                            scanned_by: 'current_user_id' // You'll need to pass the actual scanner user ID
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.handleScanSuccess(result);
                    } else {
                        this.handleScanError(result.message || 'Scan failed');
                    }

                } catch (error) {
                    console.error('Error processing QR code:', error);
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

                // Play success sound (optional)
                this.playSuccessSound();
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

                // Play error sound (optional)
                this.playErrorSound();
            }

            updateScanResult(content, type) {
                this.resultDiv.innerHTML = content;
                this.resultDiv.className =
                    `scan-result text-center py-3 ${type === 'success' ? 'success-animation' : ''}`;
            }

            addRecentScan(scanData) {
                // Remove "no recent scans" message if present
                if (this.recentScansDiv.querySelector('.text-muted')) {
                    this.recentScansDiv.innerHTML = '';
                }

                const scanItem = document.createElement('div');
                scanItem.className =
                    `scan-item p-3 mb-2 ${scanData.status === 'success' ? 'bg-light-success' : 'bg-light-danger'}`;

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

            submitManualQR() {
                const qrToken = this.manualInput.value.trim();
                if (!qrToken) {
                    alert('Please enter a QR code token');
                    return;
                }

                this.processQRCode(qrToken);
                this.manualInput.value = '';
            }

            updateStatus(message, type) {
                this.statusDiv.innerHTML = `<i class="bx bx-info-circle me-2"></i>${message}`;
                this.statusDiv.className = `alert alert-${type} text-center mb-3`;
            }

            playSuccessSound() {
                // Optional: Play success sound
                const audio = new Audio(
                    'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvGMcBDSL0fPTgS4FJXzE7t+XRwwQWrjn6qNXFAhtm+vthUIJCWq29+mgpNR3NgQHgG63ABgqQPOGCIHatPMJEcJhCRG4cCIGBFyiOJG2NQQPAml4MMMWM0UoWDMsVGSGNQkPB1y0PpSkHQJLdOzgzRgqQAYHJGz0FjFMKQIYWDMsVGSGNQkPB1y0PpSkHQJLdOzgzRgqQAoFJYzl5KVNEQ9+kO3v3mccBx+GyfPj0E9OAghz'
                    );
                audio.play().catch(() => {}); // Ignore errors if audio fails
            }

            playErrorSound() {
                // Optional: Play error sound
                const audio = new Audio(
                    'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvGMcBDSL0fPTgS4FJXzE7t+XRwwQWrjn6qNXFAhtm+vthUIJCWq29+mgpNR3NgQHgG63ABgqQPOGCIHatPMJEcJhCRG4cCIGBFyiOJG2NQQPAml4MMMWM0UoWDMsVGSGNQkPB1y0PpSkHQJLdOzgzRgqQAYHJGz0FjFMKQIYWDMsVGSGNQkPB1y0PpSkHQJLdOzgzRgqQAoFJYzl5KVNEQ9+kO3v3mccBx+GyfPj0E9OAghz'
                    );
                audio.play().catch(() => {}); // Ignore errors if audio fails
            }
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const scanner = new QRScanner();
        });
    </script>
</body>

</html>
