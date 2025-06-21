@extends('guest.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/eventinfo.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
@endsection

@section('content')
    <div class="page-container">
        <!-- Event Header -->
        <div class="event-detail-header">
            <div class="container">
                <div class="back-navigation">
                    <a href="{{ route('guest.events') }}" class="back-btn">
                        <i class="bx bx-arrow-back"></i>
                        Back to Events
                    </a>
                </div>

                <div class="event-header-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="event-title-section">
                                <h1 class="event-title">{{ $event['name'] ?? 'Event Name' }}</h1>
                                <div class="event-meta-row">
                                    <div class="event-meta">
                                        <i class="bx bx-price-tag"></i>
                                        <span>{{ $event['category_id']['name'] ?? 'Category' }}</span>
                                    </div>

                                    <div class="event-status status-{{ $event['status'] ?? 'open' }}">
                                        <i class="bx bx-circle"></i>
                                        {{ ucfirst($event['status'] ?? 'Open') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="event-actions-header">
                                @if (isset($userRegistration) && $userRegistration)
                                    <div class="registration-status registered">
                                        <i class="bx bx-check-circle"></i>
                                        You're Registered
                                    </div>
                                @elseif(($event['status'] ?? 'open') === 'open' && !($event['is_full'] ?? false))
                                    <a href="{{ route('member.events.register', $event['_id']) }}"
                                        class="btn-register-main">
                                        <i class="bx bx-receipt"></i>
                                        Register Now
                                    </a>
                                @elseif($event['is_full'] ?? false)
                                    <button class="btn-register-main full" disabled>
                                        <i class="bx bx-group"></i>
                                        Event Full
                                    </button>
                                @else
                                    <button class="btn-register-main" disabled>
                                        <i class="bx bx-x-circle"></i>
                                        Registration {{ ucfirst($event['status'] ?? 'Closed') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Content -->
        <div class="event-detail-content">
            <div class="container">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Event Poster -->
                        @if (isset($event['poster']) && !empty($event['poster']))
                            <div class="event-poster-section">
                                <div class="poster-container">
                                    <!-- Remove the asset('storage/') wrapper since getPosterUrl already handles this -->
                                    <img src="{{ $event['poster'] }}" alt="{{ $event['name'] }}"
                                        class="event-poster-detail" onclick="showPosterModal(this.src)">
                                    <div class="poster-overlay">
                                        <i class="bx bx-expand-alt"></i>
                                        Click to enlarge
                                    </div>
                                </div>
                            </div>
                        @endif
                        <!-- Event Description -->
                        <div class="event-description-section">
                            <h3 class="section-title">
                                <i class="bx bx-info-circle"></i>
                                About This Event
                            </h3>
                            <div class="event-description-content">
                                {!! nl2br(e($event['description'] ?? 'No description available')) !!}
                            </div>
                        </div>

                        <!-- Event Sessions -->
                        @if (isset($event['sessions']) && count($event['sessions']) > 0)
                            <div class="event-sessions-section">
                                <h3 class="section-title">
                                    <i class="bx bx-layer"></i>
                                    Event Sessions
                                    <span class="sessions-count">({{ count($event['sessions']) }}
                                        {{ count($event['sessions']) > 1 ? 'Sessions' : 'Session' }})</span>
                                </h3>

                                <div class="sessions-list-detail">
                                    @foreach ($event['sessions'] as $index => $session)
                                        @php
                                            $availableSlots = $session['available_slots'] ?? 0;
                                            $maxParticipants = $session['max_participants'] ?? 0;
                                            $registeredCount = $session['registered_count'] ?? 0;
                                            $capacityPercentage =
                                                $maxParticipants > 0 ? ($registeredCount / $maxParticipants) * 100 : 0;
                                            $isFull = $session['is_full'] ?? false;
                                            $sessionFee = $session['session_fee'] ?? 0;

                                            $capacityClass = 'low';
                                            if ($capacityPercentage >= 100) {
                                                $capacityClass = 'full';
                                            } elseif ($capacityPercentage >= 80) {
                                                $capacityClass = 'high';
                                            } elseif ($capacityPercentage >= 50) {
                                                $capacityClass = 'medium';
                                            }
                                        @endphp

                                        <div class="session-detail-card {{ $isFull ? 'full' : '' }}">
                                            <div class="session-header-detail">
                                                <div class="session-title-row">
                                                    <h4>
                                                        Session {{ $session['session_order'] ?? $index + 1 }}
                                                        @if ($isFull)
                                                            <span class="badge badge-danger">FULL</span>
                                                        @elseif ($availableSlots <= 5)
                                                            <span class="badge badge-warning">{{ $availableSlots }} spots
                                                                left</span>
                                                        @endif
                                                    </h4>
                                                    <div class="session-fee-detail">
                                                        @if ($sessionFee > 0)
                                                            <span class="fee-amount">Rp
                                                                {{ number_format($sessionFee) }}</span>
                                                        @else
                                                            <span class="fee-free">FREE</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if (isset($session['title']) && $session['title'])
                                                    <p class="session-subtitle">{{ $session['title'] }}</p>
                                                @endif
                                            </div>

                                            <div class="session-info-grid">
                                                <div class="session-info-item">
                                                    <i class="bx bx-calendar"></i>
                                                    <div>
                                                        <strong>Date</strong>
                                                        <span>
                                                            {{ \Carbon\Carbon::parse($session['date_range'])->format('l, d F Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="session-info-item">
                                                    <i class="bx bx-timer"></i>
                                                    <div>
                                                        <strong>Time</strong>
                                                        <span>{{ $session['start_time'] ?? 'TBA' }} -
                                                            {{ $session['end_time'] ?? 'TBA' }}</span>
                                                    </div>
                                                </div>
                                                <div class="session-info-item">
                                                    <i class="bx bx-map"></i>
                                                    <div>
                                                        <strong>Location</strong>
                                                        <span>{{ $session['location'] ?? 'Location TBA' }}</span>
                                                    </div>
                                                </div>
                                                <div class="session-info-item">
                                                    <i class="bx bx-microphone"></i>
                                                    <div>
                                                        <strong>Speaker</strong>
                                                        <span>{{ $session['speaker'] ?? 'Speaker TBA' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if (isset($session['description']) && $session['description'])
                                                <div class="session-description">
                                                    <h5>Session Description</h5>
                                                    <p>{{ $session['description'] }}</p>
                                                </div>
                                            @endif

                                            <!-- Capacity Information -->
                                            <div class="capacity-info-detail">
                                                <div class="capacity-stats">
                                                    <span class="capacity-text">
                                                        <strong>{{ $registeredCount }}/{{ $maxParticipants }}</strong>
                                                        participants
                                                    </span>
                                                    <span class="available-text">
                                                        {{ $availableSlots }} spots available
                                                    </span>
                                                </div>
                                                <div class="capacity-bar">
                                                    <div class="capacity-fill {{ $capacityClass }}"
                                                        style="width: {{ min(100, $capacityPercentage) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="event-sidebar">
                            <!-- Event Summary Card -->
                            <div class="event-summary-card">
                                <h4>Event Information</h4>

                                <div class="summary-info">
                                    <div class="summary-item">
                                        <i class="bx bx-calendar-alt"></i>
                                        <div>
                                            <strong>Date</strong>
                                            <!-- Use the transformed date_range -->
                                            <span>{{ $event['date_range'] ?? 'Date TBA' }}</span>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <i class="bx bx-timer"></i>
                                        <div>
                                            <strong>Duration</strong>
                                            <!-- Use the transformed duration -->
                                            <span>{{ $event['duration'] ?? 'Duration TBA' }}</span>
                                        </div>
                                    </div>
                                    <div class="summary-item">
                                        <i class="bx bx-map"></i>
                                        <div>
                                            <strong>Location</strong>
                                            <!-- Use the transformed display_location -->
                                            <span>{{ $event['display_location'] ?? 'Location TBA' }}</span>
                                        </div>
                                    </div>


                                    <div class="summary-item">
                                        <i class="bx bx-group"></i>
                                        <div>
                                            <strong>Capacity</strong>
                                            <span>{{ $event['registered_count'] ?? 0 }}/{{ $event['max_participants'] ?? 0 }}
                                                participants</span>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <i class="bx bx-currency-notes"></i>
                                        <div>
                                            <strong>Fee</strong>
                                            <span>
                                                @if ($event['is_free'] ?? false)
                                                    FREE
                                                @elseif (($event['min_fee'] ?? 0) == ($event['max_fee'] ?? 0))
                                                    Rp {{ number_format($event['min_fee'] ?? 0) }}
                                                @else
                                                    Rp {{ number_format($event['min_fee'] ?? 0) }} -
                                                    {{ number_format($event['max_fee'] ?? 0) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Status -->
                           
                                <!-- Registration CTA -->
                                <div class="registration-cta-card">
                                    <h4>Ready to Join?</h4>
                                    <p>Don't miss out on this amazing event!</p>

                                    @if (($event['status'] ?? 'open') === 'open' && !($event['is_full'] ?? false))
                                        <a href="{{ route('member.events.register', $event['_id']) }}"
                                            class="btn-register-sidebar">
                                            <i class="bx bx-receipt"></i>
                                            Register Now
                                        </a>

                                        @if (($event['available_slots'] ?? 0) <= 10 && ($event['available_slots'] ?? 0) > 0)
                                            <div class="urgency-notice">
                                                <i class="bx bx-error"></i>
                                                Only {{ $event['available_slots'] }} spots left!
                                            </div>
                                        @endif
                                    @elseif($event['is_full'] ?? false)
                                        <div class="full-notice">
                                            <i class="bx bx-group"></i>
                                            This event is currently full
                                        </div>
                                    @else
                                        <div class="closed-notice">
                                            <i class="bx bx-x-circle"></i>
                                            Registration is {{ $event['status'] ?? 'closed' }}
                                        </div>
                                    @endif
                                </div>

                            <!-- Share Event -->
                            <div class="share-event-card">
                                <h4>Share This Event</h4>
                                <div class="share-buttons">
                                    <a href="#" onclick="shareToWhatsApp()" class="share-btn whatsapp">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                            <path
                                                d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                        </svg>
                                        WhatsApp
                                    </a>
                                    <a href="#" onclick="shareToTwitter()" class="share-btn twitter">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
                                            <path
                                                d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                                        </svg>
                                        Twitter
                                    </a>
                                    <a href="#" onclick="shareToFacebook()" class="share-btn facebook">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                            <path
                                                d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                                        </svg> Facebook
                                    </a>
                                    <a href="#" onclick="copyLink()" class="share-btn copy">
                                        <i class="bx bx-link"></i>
                                        Copy Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Poster Modal -->
    <div id="posterModal"
        style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); align-items:center; justify-content:center;">
        <span onclick="closePosterModal()"
            style="position:absolute;top:30px;right:40px;font-size:40px;color:white;cursor:pointer;z-index:10001;">&times;</span>
        <img id="posterModalImg" src=""
            style="max-width:90vw; max-height:90vh; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,0.4);">
    </div>

    <script>
        // Poster Modal Functions
        function showPosterModal(src) {
            const modal = document.getElementById('posterModal');
            const img = document.getElementById('posterModalImg');
            if (modal && img) {
                img.src = src;
                modal.style.display = 'flex';
            }
        }

        function closePosterModal() {
            const modal = document.getElementById('posterModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal on background click
        document.getElementById('posterModal')?.addEventListener('click', function(e) {
            if (e.target === this) closePosterModal();
        });

        // Share Functions
        function shareToWhatsApp() {
            const text = `Check out this event: {{ $event['name'] ?? 'Event' }} - {{ url()->current() }}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        function shareToTwitter() {
            const text = `Check out this event: {{ $event['name'] ?? 'Event' }}`;
            const url = `{{ url()->current() }}`;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
                '_blank');
        }

        function shareToFacebook() {
            const url = `{{ url()->current() }}`;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        }

        function copyLink() {
            const url = `{{ url()->current() }}`;
            navigator.clipboard.writeText(url).then(() => {
                // Show success message
                const btn = event.target.closest('.share-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-check"></i> Copied!';
                btn.style.background = '#28a745';

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);

                alert('Link copied to clipboard!');
            });
        }

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection
