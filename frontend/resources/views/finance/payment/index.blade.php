@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between mb-4">
            <h5 class="fw-bold mb-4">Registration Management</h5>
            <div>
                <a href="{{ route('admin.registrations.pending') }}" class="btn btn-warning me-2">
                    <i class="bx bx-time"></i> Pending Payments
                </a>
                <a href="{{ route('admin.registrations.statistics') }}" class="btn btn-info">
                    <i class="bx bx-chart"></i> Statistics
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.registrations.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('payment_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('payment_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Registration Table Card -->
        <div class="card">
            <h5 class="card-header">Registration List</h5>
            <div class="text-nowrap p-3">
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Registration #</th>
                            <th>Participant</th>
                            <th>Event</th>
                            <th>Payment Amount</th>
                            <th>Payment Status</th>
                            <th>Registration Status</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($registrations as $registration)
                            <tr>
                                <td>
                                    <strong>{{ $registration['registration_number'] ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $registration['user_id']['name'] }}</strong><br>
                                        <small class="text-muted">{{ $registration['user_id']['email'] }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $registration['event_id']['name'] }}</strong><br>
                                        <small class="text-muted">Fee: Rp {{ number_format($registration['event_id']['registration_fee'], 0, ',', '.') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($registration['payment_amount'])
                                        <span class="fw-bold">Rp {{ number_format($registration['payment_amount'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($registration['payment_status']) {
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($registration['payment_status']) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $regStatusClass = match($registration['registration_status']) {
                                            'draft' => 'secondary',
                                            'registered' => 'info',
                                            'confirmed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $regStatusClass }}">
                                        {{ ucfirst($registration['registration_status']) }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y H:i') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.registrations.show', $registration['_id']) }}">
                                                <i class="bx bx-show me-1"></i> View Details
                                            </a>
                                            @if($registration['payment_status'] === 'pending' && $registration['payment_proof_url'])
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item" onclick="approvePayment('{{ $registration['_id'] }}')">
                                                    <i class="bx bx-check me-1"></i> Approve Payment
                                                </button>
                                                <button class="dropdown-item" onclick="rejectPayment('{{ $registration['_id'] }}')">
                                                    <i class="bx bx-x me-1"></i> Reject Payment
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        No registrations found
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($pagination['total_pages'] > 1)
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            @if($pagination['current_page'] > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}">Previous</a>
                                </li>
                            @endif

                            @for($i = 1; $i <= $pagination['total_pages']; $i++)
                                <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if($pagination['current_page'] < $pagination['total_pages'])
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}">Next</a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide reason for rejection..." required></textarea>
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
        function approvePayment(registrationId) {
            Swal.fire({
                title: 'Approve Payment?',
                text: "This will confirm the participant's payment.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/registrations/${registrationId}/payment-status`;
                    
                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    // Add method spoofing
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                    
                    // Add payment status
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'payment_status';
                    statusInput.value = 'approved';
                    form.appendChild(statusInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectPayment(registrationId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/registrations/${registrationId}/payment-status`;
            
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }

        // Success/Error alerts
        @if(session('success'))
            Swal.fire({
                title: 'Success!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
@endsection