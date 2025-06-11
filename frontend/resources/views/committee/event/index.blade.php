@extends('committee.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header with filters -->
        <div class="d-flex justify-content-between mb-3">
            <h5 class="fw-bold mb-4">
                Event Management
            </h5>
            <a href="{{ route('committee.event.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Create Event
            </a>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('committee.event.search') }}" id="searchForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search" class="form-label">Search Events</label>
                                <input type="text" class="form-control" id="search" name="q"
                                    placeholder="Search by name or description..." value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['_id'] }}"
                                            {{ old('category_id') == $category['_id'] ? 'selected' : '' }}>
                                            {{ $category['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    <a href="{{ route('committee.event.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Events Table -->
        <div class="card">
            <h5 class="card-header">Table Events</h5>
            <div class="text-nowrap p-6">
                <table id="events-table" class="p-0 table table-responsive">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Sessions</th>
                            <th>Session Fees</th>
                            <th>Max Participants</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    @if (isset($event['poster']) && !empty($event['poster']))
                                        <img src="{{ asset('storage/' . $event['poster']) }}" alt="Event Poster"
                                            class="rounded"
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                            onclick="showImageModal('{{ asset('storage/' . $event['poster']) }}', '{{ $event['name'] }}')">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light rounded"
                                            style="width: 60px; height: 60px;">
                                            <i class="bx bx-image text-muted" style="font-size: 24px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $event['name'] }}</strong>
                                        @if (isset($event['description']) && !empty($event['description']))
                                            <br><small
                                                class="text-muted">{{ Str::limit($event['description'], 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if (isset($event['category_id']['name']))
                                        <span class="badge"
                                            style="background-color: {{ $event['category_id']['color'] ?? '#6c757d' }}">
                                            {{ $event['category_id']['name'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($event['sessions']) && count($event['sessions']) > 0)
                                        <button class="btn btn-sm btn-outline-primary sessions-btn"
                                            data-event-id="{{ $event['_id'] }}" data-event-name="{{ $event['name'] }}"
                                            data-sessions="{{ json_encode($event['sessions']) }}">
                                            <i class="bx bx-calendar me-1"></i>
                                            {{ count($event['sessions']) }} Session(s)
                                        </button>
                                    @else
                                        <span class="text-muted">No sessions</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($event['sessions']) && count($event['sessions']) > 0)
                                        @php
                                            $sessionFees = collect($event['sessions'])->pluck('session_fee')->filter();
                                            $minFee = $sessionFees->min();
                                            $maxFee = $sessionFees->max();
                                        @endphp
                                        @if ($sessionFees->count() > 0)
                                            @if ($minFee == $maxFee)
                                                <span class="badge bg-info">Rp
                                                    {{ number_format($minFee, 0, ',', '.') }}</span>
                                            @else
                                                <span class="badge bg-info">Rp {{ number_format($minFee, 0, ',', '.') }} -
                                                    {{ number_format($maxFee, 0, ',', '.') }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-success">Free</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($event['max_participants']) && $event['max_participants'] > 0)
                                        {{ $event['max_participants'] }} people
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="badge bg-{{ $event['status'] === 'open' ? 'success' : ($event['status'] === 'closed' ? 'danger' : ($event['status'] === 'cancelled' ? 'dark' : 'info')) }}">
                                            {{ ucfirst($event['status']) }}
                                        </span>

                                        <button class="btn btn-sm btn-outline-secondary ms-2" data-bs-toggle="modal"
                                            data-bs-target="#statusModal{{ $event['_id'] }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                    </div>

                                    <!-- Modal untuk update status -->
                                    <div class="modal fade" id="statusModal{{ $event['_id'] }}" tabindex="-1">
                                        <div class="modal-dialog modal-sm modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">Update Status</h6>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('committee.event.status.update', $event['_id']) }}"
                                                    method="POST">
                                                    @csrf @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="form-label">Select Status:</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="open"
                                                                    {{ $event['status'] === 'open' ? 'selected' : '' }}>
                                                                    Open</option>
                                                                <option value="closed"
                                                                    {{ $event['status'] === 'closed' ? 'selected' : '' }}>
                                                                    Closed</option>
                                                                <option value="cancelled"
                                                                    {{ $event['status'] === 'cancelled' ? 'selected' : '' }}>
                                                                    Cancelled</option>
                                                                <option value="completed"
                                                                    {{ $event['status'] === 'completed' ? 'selected' : '' }}>
                                                                    Completed</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update
                                                            Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if (isset($event['createdAt']))
                                        {{ \Carbon\Carbon::parse($event['createdAt'])->format('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">

                                        <!-- Edit -->
                                        <a href="{{ route('committee.event.participants', $event['_id']) }}"
                                            class="btn btn-sm btn-success" title="Event Participants">
                                            <i class="bx bx-user"></i>
                                        </a>
                                        <a href="{{ route('committee.event.scan-qr', $event['_id']) }}"
                                            class="btn btn-sm btn-secondary" title="Event Participants">
                                            <i class="bx bx-qr-scan"></i>
                                        </a>

                                        <!-- Show -->
                                        <a href="{{ route('committee.event.show', $event['_id']) }}"
                                            class="btn btn-sm btn-info" title="Scan QR">
                                            <i class="bx bx-show"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('committee.event.edit', $event['_id']) }}"
                                            class="btn btn-sm btn-warning" title="Edit Event">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('committee.event.destroy', $event['_id']) }}"
                                            method="POST" style="display: inline-block" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                data-id="{{ $event['_id'] }}" title="Delete Event">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Event Poster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Event Poster" class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Modal -->
    <div class="modal fade" id="sessionsModal" tabindex="-1" aria-labelledby="sessionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sessionsModalLabel">Event Sessions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="sessionsContainer">
                        <!-- Sessions will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#events-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 8] // Poster and Actions columns
                }],
                "order": [
                    [7, "desc"]
                ], // Order by created date descending
                "responsive": true,
                "scrollX": true,
            });

            // Load categories for filter
            loadCategories();

            // Status update handler
            $('.status-update').on('click', function(e) {
                e.preventDefault();
                const eventId = $(this).data('id');
                const newStatus = $(this).data('status');
                updateEventStatus(eventId, newStatus);
            });

            // Sessions modal handler
            $('.sessions-btn').on('click', function() {
                const eventName = $(this).data('event-name');
                const sessions = $(this).data('sessions');
                showSessionsModal(eventName, sessions);
            });



            // Delete confirmation
            $(document).on('click', '.delete-btn', function() {
                const form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Success/Error messages
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });

        // Load categories for filter dropdown
        function loadCategories() {
            // You'll need to create an endpoint to get categories
            // For now, this is a placeholder
        }

        // Show sessions modal
        function showSessionsModal(eventName, sessions) {
            $('#sessionsModalLabel').text(`${eventName} - Sessions`);

            let sessionsHtml = '';
            if (sessions && sessions.length > 0) {
                sessions.forEach((session, index) => {
                    const sessionDate = new Date(session.date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

                    const sessionFee = session.session_fee ?
                        `Rp ${new Intl.NumberFormat('id-ID').format(session.session_fee)}` : 'Free';

                    sessionsHtml += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="card-title mb-2">
                                            <i class="bx bx-calendar me-2"></i>${session.title}
                                        </h6>
                                        <p class="card-text text-muted mb-2">
                                            <i class="bx bx-time me-2"></i>
                                            ${sessionDate} • ${session.start_time} - ${session.end_time}
                                        </p>
                                        <p class="card-text text-muted mb-0">
                                            <i class="bx bx-map me-2"></i>${session.location}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="d-flex justify-content-end align-items-center h-100">
                                            <span class="badge ${session.session_fee && session.session_fee > 0 ? 'bg-info' : 'bg-success'} fs-6">
                                                ${sessionFee}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                sessionsHtml = `
                    <div class="text-center py-4">
                        <i class="bx bx-calendar-x text-muted" style="font-size: 48px;"></i>
                        <h6 class="text-muted mt-2">No sessions available</h6>
                    </div>
                `;
            }

            $('#sessionsContainer').html(sessionsHtml);

            const modal = new bootstrap.Modal(document.getElementById('sessionsModal'));
            modal.show();
        }

        // Update event status
        function updateEventStatus(eventId, status) {
            $.ajax({
                url: `/committee/events/${eventId}/status`,
                type: 'PATCH',
                data: {
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Event status updated successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update event status',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }



        // Show image modal
        function showImageModal(imageSrc, eventName) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModalLabel').textContent = eventName + ' - Poster';
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }
    </script>
@endsection
