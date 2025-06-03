@extends('member.layouts.app')

@section('styles')
        <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">

@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-wrap js-fullheight" style="background-image: url('/guest/images/bg_1.jpg');" data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-start"
                data-scrollax-parent="true">
                <div class="col-xl-10 ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
                    <h1 class="mb-4" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"> Discover Amazing
                        <br><span>Events</span>
                    </h1>
                    <a href="#events" class="btn-primary-custom">
                        <i class="fas fa-calendar-check me-2"></i>View Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <section class="ftco-section services-section bg-light">
        <div class="container">
            <div class="row d-flex">
                <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-block">
                        <div class="icon"><span class="flaticon-placeholder"></span></div>
                        <div class="media-body">
                            <h3 class="heading mb-3">Venue</h3>
                            <p>203 Fake St. Mountain View, San Francisco, California, USA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-block">
                        <div class="icon"><span class="flaticon-world"></span></div>
                        <div class="media-body">
                            <h3 class="heading mb-3">Transport</h3>
                            <p>A small river named Duden flows by their place and supplies.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-block">
                        <div class="icon"><span class="flaticon-hotel"></span></div>
                        <div class="media-body">
                            <h3 class="heading mb-3">Hotel</h3>
                            <p>A small river named Duden flows by their place and supplies.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-block">
                        <div class="icon"><span class="flaticon-cooking"></span></div>
                        <div class="media-body">
                            <h3 class="heading mb-3">Restaurant</h3>
                            <p>A small river named Duden flows by their place and supplies.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="ftco-section bg-light">
        <div class="container">
            <div class="section-title ftco-animate">
                <span class="subheading">Discover</span>
                <h2><span>Upcoming</span> Events</h2>
                <p>Join our exciting events and expand your knowledge with industry experts</p>
            </div>

            @if (isset($transformedEvents) && count($transformedEvents) > 0)
                <div class="events-grid">
                    @foreach ($transformedEvents as $event)
                        <div class="event-card ftco-animate">
                            <div class="event-poster">
                                @if (isset($event['poster']) && $event['poster'])
                                    <a href="{{ $event['poster'] }}" class="poster-link" data-lightbox="event-poster" data-title="{{ $event['name'] ?? 'Event Poster' }}">
                                        <img src="{{ $event['poster'] }}" alt="{{ $event['name'] ?? 'Event Poster' }}">
                                    </a>
                                @else
                                    <a href="{{ asset('images/default-event.jpg') }}" class="poster-link" data-lightbox="event-poster" data-title="{{ $event['name'] ?? 'Event Poster' }}">
                                        <img src="{{ asset('images/default-event.jpg') }}" alt="Default Event Poster">
                                    </a>
                                @endif
                                <div class="event-overlay"></div>

                                <div class="event-status status-{{ $event['status'] ?? 'open' }}">
                                    {{ ucfirst($event['status'] ?? 'Open') }}
                                </div>

                                <div class="event-date-badge">
                                    <div class="day">{{ \Carbon\Carbon::parse($event['display_date'])->format('d') }}</div>
                                    <div class="month">{{ \Carbon\Carbon::parse($event['display_date'])->format('M') }}</div>
                                </div>

                                @if(($event['sessions_count'] ?? 0) > 1)
                                    <div class="sessions-badge">
                                        <i class="fas fa-layer-group"></i> {{ $event['sessions_count'] }} Sessions
                                    </div>
                                @endif
                            </div>

                            <div class="event-content">
                                <h3 class="event-title">{{ $event['name'] ?? 'Event Name' }}</h3>

                                <div class="event-meta-section">
                                    <div class="event-meta-grid">
                                        <div class="event-meta">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ $event['date_range'] ?? 'Date TBA' }}</span>
                                        </div>

                                        <div class="event-meta">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ $event['display_time'] ?? 'Time TBA' }}</span>
                                        </div>

                                        <div class="event-meta event-meta-full">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $event['display_location'] ?? 'Location TBA' }}</span>
                                        </div>

                                        <div class="event-meta">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $event['available_slots'] ?? 0 }}/{{ number_format($event['max_participants'] ?? 0) }} slots</span>
                                        </div>

                                        <div class="event-meta">
                                            <i class="fas fa-tag"></i>
                                            <span>{{ $event['category'] ?? 'General' }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if(isset($event['sessions']) && count($event['sessions']) > 0)
                                    <div class="sessions-info">
                                        <div class="sessions-title">
                                            <i class="fas fa-layer-group"></i>
                                            {{ count($event['sessions']) }} {{ count($event['sessions']) > 1 ? 'Sessions' : 'Session' }} Available
                                        </div>
                                        <div class="sessions-list">
                                            @foreach($event['sessions'] as $index => $session)
                                                @if($index < 3) {{-- Show max 3 sessions for home --}}
                                                    <div class="session-item">
                                                        <div class="session-header">
                                                            <strong>Session {{ $session['session_order'] ?? ($index + 1) }}:</strong>
                                                            {{ \Carbon\Carbon::parse($session['date'])->format('M j') }} at 
                                                            {{ $session['start_time'] ?? 'TBA' }}
                                                        </div>
                                                        
                                                        @if(isset($session['location']) && $session['location'] !== 'Location TBA')
                                                            <div class="session-location">
                                                                <i class="fas fa-map-marker-alt"></i> {{ Str::limit($session['location'], 30) }}
                                                            </div>
                                                        @endif
                                                        
                                                        @if(isset($session['speaker']) && $session['speaker'] !== 'Speaker TBA')
                                                            <div class="session-speaker">
                                                                <i class="fas fa-microphone"></i> {{ $session['speaker'] }}
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="session-capacity">
                                                            <i class="fas fa-users"></i> 
                                                            {{ $session['available_slots'] ?? 0 }}/{{ $session['max_participants'] ?? 0 }} available
                                                            @if(($session['available_slots'] ?? 0) <= 0)
                                                                <span class="text-danger">(Full)</span>
                                                            @elseif(($session['quota_percentage'] ?? 0) >= 80)
                                                                <span class="text-warning">(Almost Full)</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if(count($event['sessions']) > 3)
                                                <div class="session-item text-muted">
                                                    <i>... and {{ count($event['sessions']) - 3 }} more sessions</i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Enhanced Slot Information with Session Context -->
                                @php
                                    $availableSlots = $event['available_slots'] ?? 0;
                                    $maxParticipants = $event['max_participants'] ?? 0;
                                    $registeredCount = $event['registered_count'] ?? 0;
                                    $slotsPercentage = $maxParticipants > 0 ? ($registeredCount / $maxParticipants) * 100 : 0;
                                    $sessionsCount = isset($event['sessions']) ? count($event['sessions']) : 0;
                                    $hasAvailableSessions = $event['has_available_sessions'] ?? false;
                                @endphp

                                @if (!$hasAvailableSessions)
                                    <div class="slots-info full">
                                        <i class="fas fa-exclamation-triangle"></i> All Sessions Full
                                        @if($sessionsCount > 1)
                                            <div class="text-xs mt-1">All {{ $sessionsCount }} sessions are fully booked</div>
                                        @endif
                                    </div>
                                @elseif($slotsPercentage >= 80)
                                    <div class="slots-info almost-full">
                                        <i class="fas fa-clock"></i> Limited Slots Available!
                                        @if($sessionsCount > 1)
                                            <div class="text-xs mt-1">Check individual sessions for availability</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="slots-info">
                                        <i class="fas fa-users"></i> Sessions Available
                                        @if($sessionsCount > 1)
                                            <div class="text-xs mt-1">Multiple sessions with {{ $availableSlots }} total slots</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="event-fee {{ ($event['registration_fee'] ?? 0) == 0 ? 'free' : '' }}">
                                    <i class="fas fa-tag"></i>
                                    @if(($event['registration_fee'] ?? 0) == 0)
                                        FREE EVENT
                                        @if($sessionsCount > 1)
                                            <span class="text-xs">(All {{ $sessionsCount }} sessions)</span>
                                        @endif
                                    @else
                                        Rp {{ number_format($event['registration_fee']) }}
                                        @if($sessionsCount > 1)
                                            <span class="text-xs">(Access to all {{ $sessionsCount }} sessions)</span>
                                        @endif
                                    @endif
                                </div>

                                <div class="event-actions">
                                    @if (($event['status'] ?? 'open') === 'open' && $hasAvailableSessions)
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event">
                                            <i class="fas fa-ticket-alt"></i> 
                                            @if($sessionsCount > 1)
                                                Register for Sessions
                                            @else
                                                Register Now
                                            @endif
                                        </a>
                                    @elseif(!$hasAvailableSessions)
                                        <button class="btn-register-event full" disabled>
                                            <i class="fas fa-users"></i> All Sessions Full
                                        </button>
                                    @else
                                        <button class="btn-register-event" disabled>
                                            <i class="fas fa-times-circle"></i> {{ ucfirst($event['status'] ?? 'Closed') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- View All Events Button -->
                <div class="view-all-events ftco-animate">
                    <a href="{{ route('member.events.index') }}" class="btn-view-all">
                        <i class="fas fa-list"></i> View All Events
                    </a>
                </div>
            @else
                <div class="no-events ftco-animate">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Available</h3>
                    <p>Stay tuned! Amazing events are coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="ftco-section-parallax">
        <div class="parallax-img d-flex align-items-center">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-md-7 text-center heading-section heading-section-white ftco-animate">
                        <h2>Ready to Get Started?</h2>
                        <p>Join thousands of students and professionals who are already part of our community.</p>
                        <div class="row d-flex justify-content-center mt-4 mb-4">
                            <div class="col-md-8">
                                <a href="{{ route('member.events.index') }}" class="btn-register-custom">Browse Events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="ftco-gallery">
        <div class="container-wrap">
            <div class="row no-gutters">
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_1.jpg') }}" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_1.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_2.jpg') }}" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_2.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_3.jpg') }}" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_3.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_4.jpg') }}" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_4.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Lightbox Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
@endsection