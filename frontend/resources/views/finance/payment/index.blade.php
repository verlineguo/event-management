@extends('finance.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Payment Management</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" id="bulk-approve-btn" disabled>
                <i class="bx bx-check-circle me-1"></i>
                Bulk Approve
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.payment.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Event</label>
                    <select name="event_id" class="form-select">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event['_id'] }}" {{ request('event_id') == $event['_id'] ? 'selected' : '' }}>
                                {{ $event['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('finance.payment.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table Card -->
    <div class="card">
        <h5 class="card-header">
            Payment Registrations
            <span class="badge bg-warning ms-2">{{ count(array_filter($registrations, fn($r) => $r['payment_status'] === 'pending')) }} Pending</span>
        </h5>
        <div class="text-nowrap p-4">
            <form id="bulk-approve-form" action="{{ route('finance.payment.bulk-approve') }}" method="POST">
                @csrf
                <table id="payments-table" class="table table-responsive">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Participant</th>
                            <th>Event</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Verified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($registrations as $registration)
                            <tr>
                                <td>
                                    @if($registration['payment_status'] === 'pending')
                                        <input type="checkbox" name="registration_ids[]" 
                                               value="{{ $registration['_id'] }}" 
                                               class="form-check-input registration-checkbox">
                                    @endif
                                </td>
                           
                                <td>
                                    <div>
                                        <strong>{{ $registration['user_id']['name'] }}</strong><br>
                                        <small class="text-muted">{{ $registration['user_id']['email'] }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        {{ $registration['event_id']['name'] }}<br>
                                        <small class="text-muted">Fee: Rp {{ number_format($registration['event_id']['registration_fee']) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($registration['payment_amount'])
                                        <strong>Rp {{ number_format($registration['payment_amount']) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'bg-warning', 'text' => 'Pending'],
                                            'approved' => ['class' => 'bg-success', 'text' => 'Approved'],
                                            'rejected' => ['class' => 'bg-danger', 'text' => 'Rejected']
                                        ];
                                        $status = $statusConfig[$registration['payment_status']] ?? ['class' => 'bg-secondary', 'text' => 'Unknown'];
                                    @endphp
                                    <span class="badge {{ $status['class'] }}">{{ $status['text'] }}</span>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    @if(isset($registration['payment_verified_by']))
                                        <small>
                                            {{ $registration['payment_verified_by']['name'] }}<br>
                                            {{ \Carbon\Carbon::parse($registration['payment_verified_at'])->format('d M Y H:i') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('finance.payment.show', $registration['_id']) }}">
                                                <i class="bx bx-show me-1"></i>View Details
                                            </a>
                                            @if($registration['payment_proof_url'])
                                                <a class="dropdown-item" href="{{ asset('storage/' . $registration['payment_proof_url']) }}" target="_blank">
                                                    <i class="bx bx-file me-1"></i>View Proof
                                                </a>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            @if($registration['payment_status'] === 'pending')
                                                <button type="button" class="dropdown-item text-success approve-btn" 
                                                        data-id="{{ $registration['_id'] }}">
                                                    <i class="bx bx-check me-1"></i>Approve
                                                </button>
                                                <button type="button" class="dropdown-item text-danger reject-btn" 
                                                        data-id="{{ $registration['_id'] }}">
                                                    <i class="bx bx-x me-1"></i>Reject
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="bx bx-info-circle display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No payment registrations found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reject-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="payment_status" value="rejected">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" 
                                  placeholder="Please provide reason for rejection..." required></textarea>
                    </div>
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
$(document).ready(function() {
    // Initialize DataTable
    $('#payments-table').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "order": [[6, "desc"]], // Sort by submitted date
        "columnDefs": [{
            "orderable": false,
            "targets": [0, 7] // Checkbox and Actions columns
        }]
    });

    // Select all functionality
    $('#select-all').on('change', function() {
        $('.registration-checkbox').prop('checked', this.checked);
        toggleBulkApproveButton();
    });

    // Individual checkbox change
    $(document).on('change', '.registration-checkbox', function() {
        toggleBulkApproveButton();
        
        // Update select all checkbox
        const totalCheckboxes = $('.registration-checkbox').length;
        const checkedCheckboxes = $('.registration-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#select-all').prop('checked', checkedCheckboxes === totalCheckboxes);
    });

    // Toggle bulk approve button
    function toggleBulkApproveButton() {
        const checkedCount = $('.registration-checkbox:checked').length;
        $('#bulk-approve-btn').prop('disabled', checkedCount === 0);
    }

    // Bulk approve
    $('#bulk-approve-btn').on('click', function() {
        const checkedCount = $('.registration-checkbox:checked').length;
        
        Swal.fire({
            title: 'Bulk Approve Payments',
            text: `Are you sure you want to approve ${checkedCount} selected payments?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#bulk-approve-form').submit();
            }
        });
    });

    // Individual approve
    $('.approve-btn').on('click', function() {
        const registrationId = $(this).data('id');
        
        Swal.fire({
            title: 'Approve Payment',
            text: 'Are you sure you want to approve this payment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = $('<form>', {
                    method: 'POST',
                    action: `{{ url('finance/payment') }}/${registrationId}/status`
                });
                form.append('@csrf');
                form.append('@method("PUT")');
                form.append($('<input>', { name: 'payment_status', value: 'approved', type: 'hidden' }));
                $('body').append(form);
                form.submit();
            }
        });
    });

    // Individual reject
    $('.reject-btn').on('click', function() {
        const registrationId = $(this).data('id');
        const actionUrl = `{{ url('finance/payment') }}/${registrationId}/status`;
        
        $('#reject-form').attr('action', actionUrl);
        $('#rejectModal').modal('show');
    });

    // Success/Error messages
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('error') || $errors->any())
        Swal.fire({
            title: 'Error!',
            text: '{{ session('error') ?? $errors->first() }}',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    @endif
});
</script>
@endsection