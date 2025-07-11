@extends('guest.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
        <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-wrap js-fullheight" style="background-image: url('images/bg_1.jpg');" data-stellar-background-ratio="0.5">
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
                    <a href="{{ route('register') }}" class="btn-secondary-custom">
                        <i class="fas fa-user-plus me-2"></i>Register Now
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
                                <a href="{{ $event['poster'] }}" class="poster-link" data-lightbox="event-poster"
                                    data-title="{{ $event['name'] }}">
                                    <img src="{{ $event['poster'] }}" alt="{{ $event['name'] }}">
                                </a>
                                <div class="event-overlay"></div>

                                <!-- Status Badge -->
                                <div class="event-status status-{{ $event['status'] }}">
                                    {{ ucfirst($event['status']) }}
                                </div>

                          

                                <!-- Date Badge -->
                                <div class="event-date-badge">
                                    <div class="day">{{ \Carbon\Carbon::parse($event['display_date'])->format('d') }}
                                    </div>
                                    <div class="month">{{ \Carbon\Carbon::parse($event['display_date'])->format('M') }}
                                    </div>
                                </div>

                                <!-- Sessions Count -->
                                @if ($event['sessions_count'] > 1)
                                    <div class="sessions-badge">
                                        <i class="bx bx-layers"></i> {{ $event['sessions_count'] }} Sessions
                                    </div>
                                @endif

                                <!-- Quota Status -->
                                @if ($event['quota_percentage'] >= 80 && $event['quota_percentage'] < 100)
                                    <div class="quota-warning">
                                        <i class="bx bx-alert-triangle"></i> Almost Full
                                    </div>
                                @endif
                            </div>

                            <div class="event-content">
                                <h3 class="event-title">{{ $event['name'] }}</h3>

                                <!-- Description Preview -->
                                <p class="event-description">
                                    {{ Str::limit($event['description'], 100) }}
                                </p>

                                <!-- Enhanced Meta Information -->
                                <div class="event-meta-section">
                                    <div class="event-meta">
                                        <i class="bx bx-calendar-alt"></i>
                                        <span>{{ $event['date_range'] }}</span>
                                    </div>

                                    <div class="event-meta">
                                        <i class="bx bx-categories"></i>
                                        <span>{{ $event['category'] }}</span>
                                    </div>

                                    @if ($event['sessions_count'] > 0)
                                        <div class="event-meta">
                                            <i class="bx bx-layers"></i>
                                            <span>{{ $event['sessions_count'] }}
                                                {{ $event['sessions_count'] > 1 ? 'Sessions' : 'Session' }}</span>
                                        </div>
                                    @endif

                                    @if ($event['speaker_info'])
                                        <div class="event-meta">
                                            <i class="bx bx-microphone"></i>
                                            <span>{{ $event['speaker_info'] }}</span>
                                        </div>
                                    @endif

                                    @if ($event['location_info'])
                                        <div class="event-meta">
                                            <i class="bx bx-location-plus"></i>
                                            <span>{{ $event['location_info'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Fee Information -->
                                <div class="event-fee-section">
                                    @if ($event['is_free'])
                                        <div class="event-fee free">
                                            <i class="bx bx-gift"></i> FREE EVENT
                                        </div>
                                    @else
                                        <div class="event-fee">
                                            <i class="bx bx-money"></i>
                                            @if ($event['min_fee'] == $event['max_fee'])
                                                Rp {{ number_format($event['min_fee']) }}
                                            @else
                                                Rp {{ number_format($event['min_fee']) }} -
                                                {{ number_format($event['max_fee']) }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Next Session Info -->
                                @if ($event['next_session'])
                                    <div class="next-session-info">
                                        <h4>Next Session:</h4>
                                        <div class="session-details">
                                            <h6>{{ $event['next_session']['title'] }}</h6><br>
                                            <small>
                                                <i class="bx bx-timer"></i>
                                                @php
                                                    try {
                                                        $sessionDate = \Carbon\Carbon::parse(
                                                            $event['next_session']['date'],
                                                        )->format('M j, Y');
                                                    } catch (\Exception $e) {
                                                        $sessionDate = 'Date TBA';
                                                    }
                                                @endphp
                                                {{ $sessionDate }} at {{ $event['next_session']['time'] }}<br>
                                                <i class="bx bx-user"></i> {{ $event['next_session']['speaker'] }}
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <!-- Availability Status -->
                                @if ($event['availability_status'] === 'full')
                                    <div class="availability-status full">
                                        <i class="bx bx-users"></i> Event Full
                                        ({{ $event['registered_count'] }}/{{ $event['max_participants'] }})
                                    </div>
                                @elseif($event['availability_status'] === 'almost_full')
                                    <div class="availability-status almost-full">
                                        <i class="bx bx-users"></i> Almost Full
                                        ({{ $event['registered_count'] }}/{{ $event['max_participants'] }})
                                    </div>
                                @elseif($event['availability_status'] === 'no_sessions')
                                    <div class="availability-status no-sessions">
                                        <i class="bx bx-calendar-check"></i> No Sessions Available
                                    </div>
                                @else
                                    <div class="availability-status available">
                                        <i class="bx bx-check-circle"></i>
                                        {{ $event['max_participants'] - $event['registered_count'] }} spots available
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="event-actions">
                                    @if ($event['status'] === 'open' && $event['availability_status'] === 'available')
                                        <a href="{{ route('guest.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="bx bx-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event">
                                            <i class="bx bx-tickets"></i> Register Now
                                        </a>
                                    @elseif($event['availability_status'] === 'almost_full')
                                        <a href="{{ route('guest.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="bx bx-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event urgent">
                                            <i class="bx bx-bolt"></i> Register Now!
                                        </a>
                                    @elseif($event['availability_status'] === 'full')
                                        <a href="{{ route('guest.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="bx bx-eye"></i> View Details
                                        </a>
                                        <button class="btn-register-event full" disabled>
                                            <i class="bx bx-users"></i> Event Full
                                        </button>
                                    @else
                                        <a href="{{ route('guest.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="bx bx-eye"></i> View Details
                                        </a>
                                        <button class="btn-register-event" disabled>
                                            <i class="bx bx-timer"></i> {{ ucfirst($event['status']) }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-events ftco-animate">
                    <i class="bx bx-calendar-check"></i>
                    <h3>No Featured Events Available</h3>
                    <p>Stay tuned! Amazing events are coming soon.</p>
                    <a href="{{ route('guest.events') }}" class="btn-view-all">
                        <i class="bx bx-search"></i> Browse All Events
                    </a>
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
                                <a href="{{ route('register') }}" class="btn-register-custom">Register as member</a>
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
                    <a href="images/image_1.jpg" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url(images/image_1.jpg);">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="images/image_2.jpg" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url(images/image_2.jpg);">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="images/image_3.jpg" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url(images/image_3.jpg);">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="images/image_4.jpg" class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url(images/image_4.jpg);">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
