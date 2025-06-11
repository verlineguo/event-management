@extends('committee.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-1">Event Participants</h5>
                <p class="text-muted mb-0">{{ $data['event']['name'] }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('committee.event.scan-qr', $data['event']['_id']) }}" class="btn btn-success">
                    <i class="bx bx-qr-scan me-1"></i>Scan QR Code
                </a>
                <a href="{{ route('committee.event.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Events
                </a>
                
                {{-- Session Selection Dropdown for Attendance --}}
                @if(isset($data['event']['sessions']) && count($data['event']['sessions']) > 0)
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-list-check me-1"></i>View Attendance
                        </button>
                        <ul class="dropdown-menu">
                            @foreach($data['event']['sessions'] as $session)
                                <li>
                                    <a class="dropdown-item" 
                                       href="{{ route('committee.attendance.session', $session['_id']) }}">
                                        {{ $session['title'] }}
                                        <small class="text-muted d-block">
                                            {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }} • 
                                            {{ $session['start_time'] }}
                                        </small>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Event Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="card-title">{{ $data['event']['name'] }}</h6>
                        <p class="text-muted mb-2">{{ $data['event']['description'] ?? 'No description' }}</p>
                        <div class="d-flex gap-3">
                            <small class="text-muted">
                                <i class="bx bx-category me-1"></i>
                                {{ $data['event']['category_id']['name'] ?? 'No category' }}
                            </small>
                            <small class="text-muted">
                                <i class="bx bx-group me-1"></i>
                                Max: {{ $data['event']['max_participants'] ?? 'Unlimited' }} participants
                            </small>
                            <small class="text-muted">
                                <i class="bx bx-calendar me-1"></i>
                                {{ count($data['event']['sessions'] ?? []) }} sessions
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span
                            class="badge bg-{{ $data['event']['status'] === 'open' ? 'success' : ($data['event']['status'] === 'closed' ? 'danger' : ($data['event']['status'] === 'cancelled' ? 'dark' : 'info')) }} fs-6">
                            {{ ucfirst($data['event']['status']) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions List (if any) -->
        @if(isset($data['event']['sessions']) && count($data['event']['sessions']) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Event Sessions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($data['event']['sessions'] as $session)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">{{ $session['title'] }}</h6>
                                                <p class="text-muted small mb-2">
                                                    <i class="bx bx-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}
                                                </p>
                                                <p class="text-muted small mb-2">
                                                    <i class="bx bx-time me-1"></i>
                                                    {{ $session['start_time'] }} - {{ $session['end_time'] }}
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="bx bx-map me-1"></i>
                                                    {{ $session['location'] }}
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $session['status'] === 'completed' ? 'success' : ($session['status'] === 'ongoing' ? 'info' : ($session['status'] === 'cancelled' ? 'danger' : 'warning')) }}">
                                                    {{ ucfirst($session['status']) }}
                                                </span>
                                                <br>
                                                <a href="{{ route('committee.attendance.session', $session['_id']) }}" 
                                                   class="btn btn-sm btn-primary mt-2">
                                                    <i class="bx bx-list-check me-1"></i>Attendance
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bx bx-user-check text-success" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0">{{ $stats['total_participants'] }}</h4>
                        <small class="text-muted">Total Participants</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bx bx-time text-warning" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0">{{ $stats['pending_payments'] }}</h4>
                        <small class="text-muted">Pending Payments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bx bx-check-circle text-info" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0">{{ $stats['confirmed_registrations'] }}</h4>
                        <small class="text-muted">Confirmed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bx bx-qr-scan text-primary" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0">{{ $stats['total_attendances'] }}</h4>
                        <small class="text-muted">Check-ins</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" name="payment_status" onchange="this.form.submit()">
                                <option value="">All Payments</option>
                                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="approved" {{ request('payment_status') === 'approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Registration Status</label>
                            <select class="form-select" name="registration_status" onchange="this.form.submit()">
                                <option value="">All Registrations</option>
                                <option value="draft" {{ request('registration_status') === 'draft' ? 'selected' : '' }}>
                                    Draft</option>
                                <option value="registered"
                                    {{ request('registration_status') === 'registered' ? 'selected' : '' }}>Registered
                                </option>
                                <option value="confirmed"
                                    {{ request('registration_status') === 'confirmed' ? 'selected' : '' }}>Confirmed
                                </option>
                                <option value="cancelled"
                                    {{ request('registration_status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search Participant</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Name or email..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <a href="{{ route('committee.event.participants', $data['event']['_id']) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Participants Table -->
        <div class="card">
            <h5 class="card-header">Participants List</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Registration</th>
                            <th>Payment</th>
                            <th>Sessions</th>
                            <th>Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($participant['user_id']['name'], 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $participant['user_id']['name'] }}</strong>
                                            <br><small class="text-muted">{{ $participant['user_id']['email'] }}</small>
                                            @if (isset($participant['user_id']['phone']))
                                                <br><small
                                                    class="text-muted">{{ $participant['user_id']['phone'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $participant['registration_status'] === 'confirmed' ? 'success' : ($participant['registration_status'] === 'registered' ? 'info' : ($participant['registration_status'] === 'cancelled' ? 'danger' : 'warning')) }}">
                                        {{ ucfirst($participant['registration_status']) }}
                                    </span>
                                    <br><small class="text-muted">
                                        {{ \Carbon\Carbon::parse($participant['createdAt'])->format('d M Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $participant['payment_status'] === 'approved' ? 'success' : ($participant['payment_status'] === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($participant['payment_status']) }}
                                    </span>
                                    @if ($participant['payment_amount'])
                                        <br><small class="text-muted">Rp
                                            {{ number_format($participant['payment_amount'], 0, ',', '.') }}</small>
                                    @endif
                                    @if ($participant['payment_proof_url'])
                                        <br><a href="{{ asset('storage/' . $participant['payment_proof_url']) }}"
                                            target="_blank" class="text-primary">
                                            <small><i class="bx bx-file me-1"></i>View Proof</small>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($participant['session_registrations']) && count($participant['session_registrations']) > 0)
                                        <button class="btn btn-sm btn-outline-info session-details-btn"
                                            data-participant-id="{{ $participant['_id'] }}"
                                            data-participant-name="{{ $participant['user_id']['name'] }}"
                                            data-sessions="{{ json_encode($participant['session_registrations']) }}">
                                            <i class="bx bx-calendar me-1"></i>
                                            {{ count($participant['session_registrations']) }} Sessions
                                        </button>
                                    @else
                                        <span class="text-muted">No sessions</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $attendedCount = 0;
                                        $totalSessions = count($participant['session_registrations'] ?? []);

                                        foreach ($participant['session_registrations'] ?? [] as $sessionReg) {
                                            if (
                                                isset($sessionReg['attendance']) &&
                                                $sessionReg['attendance']['attended']
                                            ) {
                                                $attendedCount++;
                                            }
                                        }
                                    @endphp

                                    @if ($totalSessions > 0)
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 6px;">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ ($attendedCount / $totalSessions) * 100 }}%">
                                                </div>
                                            </div>
                                            <small>{{ $attendedCount }}/{{ $totalSessions }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info participant-details-btn"
                                            data-id="{{ $participant['_id'] }}" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-user-x text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No participants found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div class="modal fade" id="sessionDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sessionDetailsTitle">Session Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="sessionDetailsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Participant Details Modal -->
    <div class="modal fade" id="participantDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Participant Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="participantDetailsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Session details modal
            $('.session-details-btn').on('click', function() {
                const participantName = $(this).data('participant-name');
                const sessions = $(this).data('sessions');

                $('#sessionDetailsTitle').text(`${participantName} - Sessions`);

                let html = '';
                if (sessions && sessions.length > 0) {
                    sessions.forEach(sessionReg => {
                        const session = sessionReg.session_id;
                        const attendance = sessionReg.attendance;

                        html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>${session.title}</h6>
                                    <p class="text-muted mb-1">
                                        <i class="bx bx-calendar me-1"></i>
                                        ${new Date(session.date).toLocaleDateString('en-GB')} • 
                                        ${session.start_time} - ${session.end_time}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="bx bx-map me-1"></i>${session.location}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    ${attendance && attendance.attended ? 
                                        `<span class="badge bg-success mb-2">Attended</span><br>
                                                 <small class="text-muted">Check-in: ${new Date(attendance.check_in_time).toLocaleString()}</small>` :
                                        `<span class="badge bg-warning">Not Attended</span>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    });
                } else {
                    html = '<p class="text-center text-muted">No sessions registered</p>';
                }

                $('#sessionDetailsContent').html(html);
                $('#sessionDetailsModal').modal('show');
            });

            // Success/Error messages
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error'
                });
            @endif
        });
    </script>
@endsection