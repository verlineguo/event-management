@extends('committee.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('committee.event.index') }}">Event Management </a>/</span>
            Event Details
        </h5>

        <div class="row">
            <!-- Event Poster Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Event Poster</h5>
                    </div>
                    <div class="card-body">
                        @if (isset($event['poster']) && !empty($event['poster']))
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $event['poster']) }}" class="img-fluid rounded"
                                    alt="Event Poster"
                                    style="max-height: 500px; object-fit: contain; cursor: pointer; max-width: 100%;"
                                    onclick="showImageModal(this.src)">
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No poster available</p>
                            </div>
                        @endif
                    </div>
                </div>


            </div>
            <!-- Event Details Card -->
            <div class="col-md-6 mb-4">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Event Details</h5>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('committee.event.edit', $event['_id']) }}">
                                            <i class="bx bx-edit-alt me-2"></i>Edit Event
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger"
                                            onclick="deleteEvent('{{ $event['_id'] }}')">
                                            <i class="bx bx-trash me-2"></i>Delete Event
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="mb-3">{{ $event['name'] }}</h4>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-calendar text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Date</small>
                                                    <span>{{ \Carbon\Carbon::parse($event['date'])->format('l, d F Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-time text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Time</small>
                                                    <span>{{ \Carbon\Carbon::parse($event['time'])->format('H:i') }}
                                                        WIB</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-map text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Location</small>
                                                    <span>{{ $event['location'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-microphone text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Speaker</small>
                                                    <span>{{ $event['speaker'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-money text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Registration Fee</small>
                                                    <span class="fw-bold">
                                                        @if ($event['registration_fee'] == 0)
                                                            <span class="badge bg-success">FREE</span>
                                                        @else
                                                            Rp {{ number_format($event['registration_fee'], 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-group text-primary me-2"></i>
                                                <div>
                                                    <small class="text-muted d-block">Max Participants</small>
                                                    <span>{{ $event['max_participants'] ?? 'Unlimited' }}
                                                        participants</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-info-circle text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Status</small>
                                                @php
                                                    $statusClasses = [
                                                        'open' => 'bg-success',
                                                        'closed' => 'bg-secondary',
                                                        'ongoing' => 'bg-warning',
                                                        'cancelled' => 'bg-danger',
                                                        'completed' => 'bg-info',
                                                    ];
                                                    $statusClass = $statusClasses[$event['status']] ?? 'bg-secondary';
                                                @endphp
                                                <span
                                                    class="badge {{ $statusClass }}">{{ ucfirst($event['status']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Registration Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="mb-1 text-primary">{{ $registrationCount ?? 0 }}</h4>
                                        <small class="text-muted">Registered</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-1 text-success">
                                        @if ($event['max_participants'])
                                            {{ $event['max_participants'] - ($registrationCount ?? 0) }}
                                        @else
                                            ∞
                                        @endif
                                    </h4>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>

                            @if ($event['max_participants'])
                                <div class="mt-3">
                                    @php
                                        $percentage = (($registrationCount ?? 0) / $event['max_participants']) * 100;
                                    @endphp
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"
                                            aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($percentage, 1) }}% filled</small>
                                </div>
                            @endif
                        </div>
                    </div>
            </div>
            <!-- Registration Statistics -->



        </div>

        <!-- Event Actions -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('committee.event.edit', $event['_id']) }}" class="btn btn-primary">
                                <i class="bx bx-edit-alt me-1"></i>Edit Event
                            </a>
                            <a href="{{ route('committee.event.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            <button class="btn btn-info" onclick="printEvent()">
                                <i class="bx bx-printer me-1"></i>Print Details
                            </button>
                            <button class="btn btn-success" onclick="shareEvent()">
                                <i class="bx bx-share me-1"></i>Share Event
                            </button>
                            <button class="btn btn-danger" onclick="deleteEvent('{{ $event['_id'] }}')">
                                <i class="bx bx-trash me-1"></i>Delete Event
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
                    <h5 class="modal-title">Event Poster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Event Poster">
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" action="{{ route('committee.event.destroy', $event['_id']) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>
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

        // Delete event function
        function deleteEvent(eventId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        // Print event details
        function printEvent() {
            const eventName = '{{ $event['name'] }}';
            const eventDate = '{{ \Carbon\Carbon::parse($event['date'])->format('l, d F Y') }}';
            const eventTime = '{{ \Carbon\Carbon::parse($event['time'])->format('H:i') }}';
            const eventLocation = '{{ $event['location'] }}';
            const eventSpeaker = '{{ $event['speaker'] }}';
            const eventFee =
                '{{ $event['registration_fee'] == 0 ? 'FREE' : 'Rp ' . number_format($event['registration_fee'], 0, ',', '.') }}';

            const printContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h1 style="text-align: center; color: #333;">${eventName}</h1>
                    <hr>
                    <table style="width: 100%; margin-top: 20px;">
                        <tr><td style="padding: 10px; font-weight: bold;">Date:</td><td style="padding: 10px;">${eventDate}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Time:</td><td style="padding: 10px;">${eventTime} WIB</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Location:</td><td style="padding: 10px;">${eventLocation}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Speaker:</td><td style="padding: 10px;">${eventSpeaker}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Registration Fee:</td><td style="padding: 10px;">${eventFee}</td></tr>
                    </table>
                </div>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }

        // Share event (simple copy to clipboard)
        function shareEvent() {
            const eventUrl = window.location.href;
            const eventName = '{{ $event['name'] }}';
            const shareText = `Check out this event: ${eventName}\n${eventUrl}`;

            if (navigator.share) {
                navigator.share({
                    title: eventName,
                    text: shareText,
                    url: eventUrl
                });
            } else {
                // Fallback to clipboard
                navigator.clipboard.writeText(shareText).then(() => {
                    Swal.fire({
                        title: 'Copied!',
                        text: 'Event link copied to clipboard',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        }
    </script>
@endsection
