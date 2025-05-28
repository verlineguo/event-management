@extends('committee.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="d-flex justify-content-between mb-5">
            <h5 class="fw-bold mb-4">
                Event Management 
            </h5>
            <a href="{{ route('committee.event.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Create Event
            </a>
        </div>

        <div class="card">
            <h5 class="card-header">Table Events</h5>
            <div class="text-nowrap p-6">
                <table id="events-table" class="p-0 table table-responsive">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Fee</th>
                            <th>Max Participants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($events as $event)
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
                                    <strong>{{ $event['name'] }}</strong>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($event['date'])->format('d M Y') }}</td>
                                <td>{{ $event['time'] }}</td>
                        
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
                                    @switch($event['status'])
                                        @case('open')
                                            <span class="badge bg-success">{{ ucfirst($event['status']) }}</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-danger">{{ ucfirst($event['status']) }}</span>
                                            @break
                                        @case('ongoing')
                                            <span class="badge bg-warning">{{ ucfirst($event['status']) }}</span>
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
                                </td>
                                <td>
                                    <a  href="{{ route('committee.event.show', $event['_id']) }}" class="btn btn-sm btn-info">
                                                    <i class="bx bx-show" style="font-size: 18px"></i>
                                                </a>
                                    <a href="{{ route('committee.event.edit', $event['_id']) }}" 
                                        class="btn btn-sm btn-warning"><i class="bx bx-edit" style="font-size: 18px"></i></a>
                                    <form action="{{ route('committee.event.destroy', $event['_id']) }}" method="POST"
                                        style="display: inline-block" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $event['_id'] }}">
                                            <i class="bx bx-trash" style="font-size: 18px"></i>
                                        </button>
                                    </form>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#events-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 7] // Poster and Actions columns
                }],
                "order": [[2, "desc"]], // Order by date descending
                "responsive": true,
                "scrollX": true,
            });

            // SweetAlert for delete confirmation
            $('.delete-btn').on('click', function() {
                const form = $(this).closest('form');
                const eventId = $(this).data('id');
                
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

        // Function to show image in modal
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
    </style>
@endsection