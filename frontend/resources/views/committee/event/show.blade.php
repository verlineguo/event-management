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
                                    style="max-height: 400px; object-fit: contain; cursor: pointer; max-width: 100%;"
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
                <div class="card h-100">
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
                                    <button class="dropdown-item text-danger" onclick="deleteEvent('{{ $event['_id'] }}')">
                                        <i class="bx bx-trash me-2"></i>Delete Event
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-9 mt-2">{{ $event['name'] }}</h4>

                        @if (isset($event['description']) && !empty($event['description']))
                            <div class="mb-6">
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-info-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Description</small>
                                        <span>{{ $event['description'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-6">
                            <div class="d-flex align-items-start">
                                <i class="bx bx-category text-primary me-3 mt-1"></i>
                                <div>
                                    <small class="text-muted d-block">Event Category</small>
                                    <span>{{ $event['category_id']['name'] }} </span>
                                </div>
                            </div>
                        </div>


                        <div class="mb-6">
                            <div class="d-flex align-items-start">
                                <i class="bx bx-calendar-event text-primary me-3 mt-1"></i>
                                <div>
                                    <small class="text-muted d-block">Total Sessions</small>
                                    <span>{{ isset($event['sessions']) ? count($event['sessions']) : 0 }} sessions</span>
                                </div>
                            </div>
                        </div>




                        <div class="mb-6">
                            <div class="d-flex align-items-start">
                                <i class="bx bx-info-circle text-primary me-3 mt-1"></i>
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
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($event['status']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Sessions -->
        @if (isset($event['sessions']) && count($event['sessions']) > 0)
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Event Sessions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($event['sessions'] as $index => $session)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 border-left-primary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $session['title'] }}</h6>
                                                    @php
                                                        $sessionStatusClasses = [
                                                            'scheduled' => 'bg-primary',
                                                            'ongoing' => 'bg-warning',
                                                            'completed' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                        ];
                                                        $sessionStatusClass =
                                                            $sessionStatusClasses[$session['status']] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $sessionStatusClass }} badge-sm">
                                                        {{ ucfirst($session['status']) }}
                                                    </span>
                                                </div>

                                                @if (isset($session['description']) && !empty($session['description']))
                                                    <p class="card-text text-muted small mb-3">
                                                        {{ $session['description'] }}</p>
                                                @endif

                                                <div class="session-details">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bx bx-calendar text-primary me-2 small"></i>
                                                        <small>{{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}</small>
                                                    </div>

                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bx bx-time text-primary me-2 small"></i>
                                                        <small>{{ $session['start_time'] }} - {{ $session['end_time'] }}
                                                            WIB</small>
                                                    </div>

                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bx bx-map text-primary me-2 small"></i>
                                                        <small>{{ $session['location'] }}</small>
                                                    </div>

                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bx bx-microphone text-primary me-2 small"></i>
                                                        <small>{{ $session['speaker'] }}</small>
                                                    </div>

                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bx bx-money text-primary me-2 small"></i>
                                                        <small>
                                                            @if ($session['session_fee'] == 0)
                                                                <span class="badge bg-success badge-sm">FREE</span>
                                                            @else
                                                                Rp
                                                                {{ number_format($session['session_fee'], 0, ',', '.') }}
                                                            @endif
                                                        </small>
                                                    </div>

                                                    <!-- Session Registration Statistics -->
                                                    <!-- Bagian dalam loop sessions - replace bagian Session Registration Statistics -->
                                                    @if (isset($session['max_participants']) && $session['max_participants'])
                                                        @php
                                                            // Ambil data registrasi yang sudah dihitung dari backend
                                                            $sessionRegistrationCount =
                                                                $sessionRegistrations[$session['_id']] ?? 0;
                                                            $maxParticipants = $session['max_participants'];
                                                            $availableSlots = max(
                                                                0,
                                                                $maxParticipants - $sessionRegistrationCount,
                                                            );
                                                            $percentage =
                                                                $maxParticipants > 0
                                                                    ? ($sessionRegistrationCount / $maxParticipants) *
                                                                        100
                                                                    : 0;
                                                        @endphp

                                                        <div class="mt-3 pt-3 border-top">
                                                            <small class="text-muted d-block mb-2">Registration
                                                                Status</small>

                                                            <div class="row text-center mb-2">
                                                                <div class="col-6">
                                                                    <div class="border-end">
                                                                        <strong
                                                                            class="text-primary d-block">{{ $sessionRegistrationCount }}</strong>
                                                                        <small class="text-muted">Registered</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <strong
                                                                        class="text-success d-block">{{ $availableSlots }}</strong>
                                                                    <small class="text-muted">Available</small>
                                                                </div>
                                                            </div>

                                                            <div class="progress mb-1" style="height: 6px;">
                                                                <div class="progress-bar {{ $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success') }}"
                                                                    role="progressbar" style="width: {{ $percentage }}%"
                                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                    aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ number_format($percentage, 1) }}%
                                                                filled</small>

                                                            @if ($availableSlots <= 0)
                                                                <div class="mt-2">
                                                                    <span class="badge bg-danger">FULL</span>
                                                                </div>
                                                            @elseif($percentage >= 90)
                                                                <div class="mt-2">
                                                                    <span class="badge bg-warning">Almost Full</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="mt-3 pt-3 border-top">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bx bx-group text-success me-2 small"></i>
                                                                <small class="text-success">Unlimited participants</small>
                                                            </div>
                                                            @php
                                                                $sessionRegistrationCount =
                                                                    $sessionRegistrations[$session['_id']] ?? 0;
                                                            @endphp
                                                            @if ($sessionRegistrationCount > 0)
                                                                <div class="mt-2">
                                                                    <strong
                                                                        class="text-primary">{{ $sessionRegistrationCount }}</strong>
                                                                    <small class="text-muted">registered</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
            const eventDescription = '{{ $event['description'] ?? '' }}';
            const eventStatus = '{{ ucfirst($event['status']) }}';

            let printContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h1 style="text-align: center; color: #333;">${eventName}</h1>
                    <hr>
                    <table style="width: 100%; margin-top: 20px;">
                        <tr><td style="padding: 10px; font-weight: bold;">Description:</td><td style="padding: 10px;">${eventDescription}</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Status:</td><td style="padding: 10px;">${eventStatus}</td></tr>
                    </table>
            `;

            @if (isset($event['sessions']) && count($event['sessions']) > 0)
                printContent += `
                    <h2 style="margin-top: 30px; color: #333;">Sessions</h2>
                    <hr>
                `;

                @foreach ($event['sessions'] as $session)
                    @php
                        $sessionRegistrationCount = $sessionRegistrations[$session['_id']] ?? 0;
                    @endphp
                    printContent += `
                        <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
                            <h3 style="color: #555;">{{ $session['title'] }}</h3>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}</p>
                            <p><strong>Time:</strong> {{ $session['start_time'] }} - {{ $session['end_time'] }} WIB</p>
                            <p><strong>Location:</strong> {{ $session['location'] }}</p>
                            <p><strong>Speaker:</strong> {{ $session['speaker'] }}</p>
                            <p><strong>Fee:</strong> {{ $session['session_fee'] == 0 ? 'FREE' : 'Rp ' . number_format($session['session_fee'], 0, ',', '.') }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($session['status']) }}</p>
                            <p><strong>Registered:</strong> {{ $sessionRegistrationCount }}{{ isset($session['max_participants']) && $session['max_participants'] ? ' / ' . $session['max_participants'] : '' }}</p>
                        </div>
                    `;
                @endforeach
            @endif

            printContent += `</div>`;

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
