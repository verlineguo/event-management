@extends('finance.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('finance.payment.index') }}">Payment Management</a> /</span>
            Payment Details
        </h5>

        <div class="row">
            <!-- Payment Proof Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Proof</h5>
                    </div>
                    <div class="card-body">
                        @if (isset($registration['payment_proof_url']) && !empty($registration['payment_proof_url']))
                            <div class="text-center">
                                <img src="{{ asset('storage/' .$registration['payment_proof_url']) }}" class="img-fluid rounded"
                                    alt="Payment Proof"
                                    style="max-height: 400px; object-fit: contain; cursor: pointer; max-width: 100%;"
                                    onclick="showImageModal(this.src)">
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No payment proof uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Details Card -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment Information</h5>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if ($registration['payment_status'] === 'pending')
                                    <li>
                                        <button class="dropdown-item text-success"
                                            onclick="updatePaymentStatus('{{ $registration['_id'] }}', 'approved')">
                                            <i class="bx bx-check me-2"></i>Approve Payment
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger"
                                            onclick="showRejectModal('{{ $registration['_id'] }}')">
                                            <i class="bx bx-x me-2"></i>Reject Payment
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h4 class="mb-6">{{ $registration['event_id']['name'] ?? 'N/A' }}</h4>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-user text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Participant Name</small>
                                                <span class="fw-bold">{{ $registration['user_id']['name'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-money text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Payment Amount</small>
                                                <span class="fw-bold">
                                                    @if (($registration['payment_amount'] ?? 0) == 0)
                                                        <span class="badge bg-success">FREE</span>
                                                    @else
                                                        Rp
                                                        {{ number_format($registration['payment_amount'] ?? 0, 0, ',', '.') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-envelope text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Email</small>
                                                <span>{{ $registration['user_id']['email'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-phone text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Phone</small>
                                                <span>{{ $registration['user_id']['phone'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-calendar text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Registration Date</small>
                                                <span>{{ isset($registration['createdAt']) ? \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y, H:i') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-time text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Last Updated</small>
                                                <span>{{ isset($registration['updatedAt']) ? \Carbon\Carbon::parse($registration['updatedAt'])->format('d M Y, H:i') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-info-circle text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Payment Status</small>
                                                @php
                                                    $paymentStatusClasses = [
                                                        'pending' => 'bg-warning',
                                                        'approved' => 'bg-success',
                                                        'rejected' => 'bg-danger',
                                                    ];
                                                    $paymentStatusClass =
                                                        $paymentStatusClasses[$registration['payment_status']] ??
                                                        'bg-secondary';
                                                @endphp
                                                <span
                                                    class="badge {{ $paymentStatusClass }}">{{ ucfirst($registration['payment_status']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-check-circle text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Registration Status</small>
                                                @php
                                                    $regStatusClasses = [
                                                        'draft' => 'bg-secondary',
                                                        'registered' => 'bg-info',
                                                        'confirmed' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                    ];
                                                    $regStatusClass =
                                                        $regStatusClasses[$registration['registration_status']] ??
                                                        'bg-secondary';
                                                @endphp
                                                <span
                                                    class="badge {{ $regStatusClass }}">{{ ucfirst($registration['registration_status']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($registration['session_count']) && $registration['session_count'] > 0)
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-list-ul text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Sessions Registered</small>
                                                    <span class="fw-bold">{{ $registration['session_count'] ?? 0 }} Session(s)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-calculator text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Total Session Fees</small>
                                                    <span class="fw-bold">
                                                        @if (($registration['total_session_fees'] ?? 0) == 0)
                                                            <span class="badge bg-success">FREE</span>
                                                        @else
                                                            Rp {{ number_format($registration['total_session_fees'] ?? 0, 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($registration['payment_verified_by']) && $registration['payment_verified_by'])
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-user-check text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Verified By</small>
                                                    <span>{{ $registration['payment_verified_by']['name'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-time text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Verified At</small>
                                                    <span>{{ isset($registration['payment_verified_at']) ? \Carbon\Carbon::parse($registration['payment_verified_at'])->format('d M Y, H:i') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($registration['rejection_reason']) && !empty($registration['rejection_reason']))
                                    <div class="mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="bx bx-message-square-error text-danger me-2 mt-1"></i>
                                            <div>
                                                <small class="text-muted d-block">Rejection Reason</small>
                                                <div class="alert alert-danger mt-1 mb-0">
                                                    {{ $registration['rejection_reason'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Details -->
        @if (isset($registration['sessions']) && count($registration['sessions']) > 0)
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bx bx-calendar-event me-2"></i>Registered Sessions
                                <span class="badge bg-primary ms-2">{{ count($registration['sessions']) }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($registration['sessions'] as $index => $session)
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1 text-primary">{{ $session['title'] ?? 'N/A' }}</h6>
                                                <span class="badge bg-light text-dark">
                                                    Rp {{ number_format($session['session_fee'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            
                                            @if (!empty($session['description']))
                                                <p class="text-muted small mb-2">{{ $session['description'] }}</p>
                                            @endif
                                            
                                            <div class="row g-2 small">
                                                <div class="col-12">
                                                    <i class="bx bx-calendar text-muted me-1"></i>
                                                    <span>{{ isset($session['date']) ? \Carbon\Carbon::parse($session['date'])->format('d M Y') : 'N/A' }}</span>
                                                </div>
                                                <div class="col-12">
                                                    <i class="bx bx-time text-muted me-1"></i>
                                                    <span>{{ $session['start_time'] ?? 'N/A' }} - {{ $session['end_time'] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-12">
                                                    <i class="bx bx-map text-muted me-1"></i>
                                                    <span>{{ $session['location'] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-12">
                                                    <i class="bx bx-user text-muted me-1"></i>
                                                    <span>{{ $session['speaker'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>

                                            @if (isset($registration['session_registrations'][$index]))
                                                @php
                                                    $sessionReg = $registration['session_registrations'][$index];
                                                    $sessionStatusClasses = [
                                                        'registered' => 'bg-info',
                                                        'confirmed' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                    ];
                                                    $sessionStatusClass = $sessionStatusClasses[$sessionReg['status']] ?? 'bg-secondary';
                                                @endphp
                                                
                                                <div class="mt-2 pt-2 border-top">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge {{ $sessionStatusClass }}">
                                                            {{ ucfirst($sessionReg['status']) }}
                                                        </span>
                                                        @if (isset($sessionReg['qr_used']) && $sessionReg['qr_used'])
                                                            <small class="text-success">
                                                                <i class="bx bx-check-circle me-1"></i>QR Used
                                                            </small>
                                                        @else
                                                            <small class="text-muted">
                                                                <i class="bx bx-qr me-1"></i>QR Not Used
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Actions -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            @if ($registration['payment_status'] === 'pending')
                                <button class="btn btn-success"
                                    onclick="updatePaymentStatus('{{ $registration['_id'] }}', 'approved')">
                                    <i class="bx bx-check me-1"></i>Approve Payment
                                </button>
                                <button class="btn btn-danger" onclick="showRejectModal('{{ $registration['_id'] }}')">
                                    <i class="bx bx-x me-1"></i>Reject Payment
                                </button>
                            @endif
                            <a href="{{ route('finance.payment.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            <button class="btn btn-info" onclick="printPayment()">
                                <i class="bx bx-printer me-1"></i>Print Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Payment Proof">
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4"
                                placeholder="Please provide a reason for rejecting this payment..." required></textarea>
                        </div>
                        <input type="hidden" name="payment_status" value="rejected">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for success message from session
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            // Check for error message from session
            @if (session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });

        // Show image in modal
        function showImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Update payment status (approve)
        function updatePaymentStatus(registrationId, status) {
            const statusText = status === 'approved' ? 'approve' : 'update';

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to ${statusText} this payment?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${statusText} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('finance/payment') }}/${registrationId}/status`;

                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Add method override
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);

                    // Add payment status
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'payment_status';
                    statusInput.value = status;
                    form.appendChild(statusInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Show reject modal
        function showRejectModal(registrationId) {
            const form = document.getElementById('rejectForm');
            form.action = `{{ url('finance/payment') }}/${registrationId}/status`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        // Print payment details
        function printPayment() {
            const eventName = '{{ $registration['event_id']['name'] ?? 'N/A' }}';
            const participantName = '{{ $registration['user_id']['name'] ?? 'N/A' }}';
            const email = '{{ $registration['user_id']['email'] ?? 'N/A' }}';
            const phone = '{{ $registration['user_id']['phone'] ?? 'N/A' }}';
            const paymentAmount =
                '{{ ($registration['payment_amount'] ?? 0) == 0 ? 'FREE' : 'Rp ' . number_format($registration['payment_amount'] ?? 0, 0, ',', '.') }}';
            const paymentStatus = '{{ ucfirst($registration['payment_status']) }}';
            const registrationStatus = '{{ ucfirst($registration['registration_status']) }}';
            const sessionCount = '{{ $registration['session_count'] ?? 0 }}';
            const totalSessionFees = '{{ ($registration['total_session_fees'] ?? 0) == 0 ? 'FREE' : 'Rp ' . number_format($registration['total_session_fees'] ?? 0, 0, ',', '.') }}';

            // Build sessions info for print
            let sessionsInfo = '';
            @if (isset($registration['sessions']) && count($registration['sessions']) > 0)
                sessionsInfo = '<h3>Registered Sessions:</h3>';
                @foreach ($registration['sessions'] as $session)
                    sessionsInfo += `
                        <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd;">
                            <strong>{{ $session['title'] ?? 'N/A' }}</strong><br>
                            Date: {{ isset($session['date']) ? \Carbon\Carbon::parse($session['date'])->format('d M Y') : 'N/A' }}<br>
                            Time: {{ $session['start_time'] ?? 'N/A' }} - {{ $session['end_time'] ?? 'N/A' }}<br>
                            Location: {{ $session['location'] ?? 'N/A' }}<br>
                            Speaker: {{ $session['speaker'] ?? 'N/A' }}<br>
                            Fee: Rp {{ number_format($session['session_fee'] ?? 0, 0, ',', '.') }}
                        </div>
                    `;
                @endforeach
            @endif

            const printContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h1 style="text-align: center; color: #333;">Payment Details</h1>
                    <hr>
                    <table style="width: 100%; margin-top: 20px;">
                        <tr><td style="padding: 10px; font-weight: bold;">Event Name:</td><td style="padding: 10px;">${eventName}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Participant Name:</td><td style="padding: 10px;">${participantName}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Email:</td><td style="padding: 10px;">${email}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Phone:</td><td style="padding: 10px;">${phone}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Payment Amount:</td><td style="padding: 10px;">${paymentAmount}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Sessions Registered:</td><td style="padding: 10px;">${sessionCount}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Total Session Fees:</td><td style="padding: 10px;">${totalSessionFees}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Payment Status:</td><td style="padding: 10px;">${paymentStatus}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Registration Status:</td><td style="padding: 10px;">${registrationStatus}</td></tr>
                    </table>
                    <br>
                    ${sessionsInfo}
                </div>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endsection