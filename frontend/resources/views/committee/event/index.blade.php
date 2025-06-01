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
                                       placeholder="Search by name or description..." 
                                       value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                            <th>Fee</th>
                            <th>Max Participants</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($events as $event)
                            <tr>
                                <td>
                                    @if(isset($event['poster']) && !empty($event['poster']))
                                        <img src="{{ asset('storage/' . $event['poster']) }}" 
                                            alt="Event Poster" 
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
                                        @if(isset($event['description']) && !empty($event['description']))
                                            <br><small class="text-muted">{{ Str::limit($event['description'], 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if(isset($event['category_id']['name']))
                                        <span class="badge" style="background-color: {{ $event['category_id']['color'] ?? '#6c757d' }}">
                                            {{ $event['category_id']['name'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($event['sessions']) && count($event['sessions']) > 0)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ count($event['sessions']) }} Session(s)
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach($event['sessions'] as $session)
                                                    <li>
                                                        <div class="dropdown-item-text">
                                                            <strong>{{ $session['title'] }}</strong><br>
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }} 
                                                                {{ $session['start_time'] }} - {{ $session['end_time'] }}
                                                            </small><br>
                                                            <small class="text-muted">{{ $session['location'] }}</small>
                                                        </div>
                                                    </li>
                                                    @if(!$loop->last)
                                                        <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted">No sessions</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($event['registration_fee']) && $event['registration_fee'] > 0)
                                        <span class="badge bg-info">Rp {{ number_format($event['registration_fee'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-success">Free</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($event['max_participants']) && $event['max_participants'] > 0)
                                        {{ $event['max_participants'] }} people
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @switch($event['status'])
                                            @case('open')
                                                <span class="badge bg-success">{{ ucfirst($event['status']) }}</span>
                                                @break
                                            @case('closed')
                                                <span class="badge bg-danger">{{ ucfirst($event['status']) }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">{{ ucfirst($event['status']) }}</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-info">{{ ucfirst($event['status']) }}</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($event['status']) }}</span>
                                        @endswitch
                                        
                                        <!-- Quick Status Update -->
                                        <div class="dropdown ms-2">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item status-update" href="#" 
                                                       data-id="{{ $event['_id'] }}" data-status="open">Open</a></li>
                                                <li><a class="dropdown-item status-update" href="#" 
                                                       data-id="{{ $event['_id'] }}" data-status="closed">Closed</a></li>
                                                <li><a class="dropdown-item status-update" href="#" 
                                                       data-id="{{ $event['_id'] }}" data-status="cancelled">Cancelled</a></li>
                                                <li><a class="dropdown-item status-update" href="#" 
                                                       data-id="{{ $event['_id'] }}" data-status="completed">Completed</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(isset($event['createdAt']))
                                        {{ \Carbon\Carbon::parse($event['createdAt'])->format('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- QR Code -->
                                        <button type="button" class="btn btn-sm btn-secondary qr-btn" 
                                                data-id="{{ $event['_id'] }}" title="Show QR Code">
                                            <i class="bx bx-qr"></i>
                                        </button>
                                        
                                        <!-- Show -->
                                        <a href="{{ route('committee.event.show', $event['_id']) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        
                                        <!-- Edit -->
                                        <a href="{{ route('committee.event.edit', $event['_id']) }}" 
                                           class="btn btn-sm btn-warning" title="Edit Event">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        
                                        <!-- Delete -->
                                        <form action="{{ route('committee.event.destroy', $event['_id']) }}" method="POST"
                                              style="display: inline-block" class="delete-form">
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
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-calendar-x text-muted" style="font-size: 48px;"></i>
                                        <h6 class="text-muted mt-2">No events found</h6>
                                        <p class="text-muted">Create your first event to get started</p>
                                        <a href="{{ route('committee.event.create') }}" class="btn btn-primary">
                                            <i class="bx bx-plus me-1"></i>Create Event
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
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

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Event QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrCodeContainer">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
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
                "order": [[7, "desc"]], // Order by created date descending
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

            // QR Code handler
            $('.qr-btn').on('click', function() {
                const eventId = $(this).data('id');
                showQRCode(eventId);
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
        });

        // Load categories for filter dropdown
        function loadCategories() {
            // You'll need to create an endpoint to get categories
            // For now, this is a placeholder
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

        // Show QR Code
        function showQRCode(eventId) {
            $('#qrCodeContainer').html(`
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `);
            
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();

            $.ajax({
                url: `/committee/events/${eventId}/qr-code`,
                type: 'GET',
                success: function(response) {
                    $('#qrCodeContainer').html(`
                        <img src="${response.qr_code_url}" alt="QR Code" class="img-fluid">
                        <p class="mt-3"><strong>QR Code:</strong> ${response.qr_code}</p>
                    `);
                },
                error: function(xhr) {
                    $('#qrCodeContainer').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error"></i> Failed to load QR Code
                        </div>
                    `);
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

    <style>
        /* Custom styles for better table layout */
        #events-table th:first-child {
            width: 80px;
        }
        
        #events-table td:first-child {
            text-align: center;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        /* Hover effect for poster images */
        td img:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease-in-out;
        }
        
        /* Modal image styling */
        #modalImage {
            max-height: 70vh;
            object-fit: contain;
        }

        /* Badge styling for categories */
        .badge {
            font-size: 0.75em;
        }

        /* Dropdown styling */
        .dropdown-item-text {
            white-space: normal;
            max-width: 250px;
        }

        /* Empty state styling */
        .text-center.py-4 {
            padding: 3rem 1rem !important;
        }
    </style>
@endsection