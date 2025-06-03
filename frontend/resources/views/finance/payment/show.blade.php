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
                <div class="card mb-4">
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
                                <h4 class="mb-3">{{ $registration['event_id']['name'] ?? 'N/A' }}</h4>

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
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-phone text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Phone</small>
                                                <span>{{ $registration['user_id']['phone'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bx bx-calendar text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Registration Date</small>
                                                <span>{{ isset($registration['createdAt']) ? \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y, H:i') : 'N/A' }}</span>
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
                        <tr><td style="padding: 10px; font-weight: bold;">Payment Status:</td><td style="padding: 10px;">${paymentStatus}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Registration Status:</td><td style="padding: 10px;">${registrationStatus}</td></tr>
                    </table>
                </div>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endsection
