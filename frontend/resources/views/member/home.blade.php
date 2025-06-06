@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <style>
        /* Additional CSS for enhanced event cards */

        .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 3;
            box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
        }

        .quota-warning {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff9800;
            color: white;
            padding: 5px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 3;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .event-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .event-fee-section {
            margin: 15px 0;
        }

        .event-fee.free {
            background: linear-gradient(45deg, #4caf50, #8bc34a);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 2px 10px rgba(76, 175, 80, 0.3);
        }

        .event-fee:not(.free) {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .next-session-info {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 12px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }

        .next-session-info h4 {
            margin: 0 0 8px 0;
            color: #007bff;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .session-details {
            font-size: 0.85rem;
        }

        .session-details strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        .session-details small {
            color: #666;
            line-height: 1.4;
        }

        .availability-status {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 10px 0;
            text-align: center;
        }

        .availability-status.available {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .availability-status.almost-full {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .availability-status.full {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .availability-status.no-sessions {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .btn-register-event.urgent {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 10px #ff6b35;
            }

            to {
                box-shadow: 0 0 20px #f7931e, 0 0 30px #ff6b35;
            }
        }

        .btn-register-event.full {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-register-event.full:hover {
            background: #6c757d;
            transform: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .featured-badge,
            .quota-warning {
                font-size: 0.7rem;
                padding: 4px 8px;
            }

            .next-session-info {
                font-size: 0.8rem;
            }

            .event-description {
                font-size: 0.85rem;
            }
        }

        /* Enhanced hover effects */
        .event-card:hover .featured-badge {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        .event-card:hover .quota-warning {
            animation-duration: 1s;
        }

        /* Statistics section (optional addition) */
        .event-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-top: 1px solid #eee;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #666;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-item i {
            color: #007bff;
        }
    </style>
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-wrap js-fullheight" style="background-image: url('/guest/images/bg_1.jpg');"
        data-stellar-background-ratio="0.5">
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

            <!-- Event Card Section (Replace the existing event card loop) -->
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

                                <!-- Featured Badge -->
                                @if ($event['is_featured'])
                                    <div class="featured-badge">
                                        <i class="fas fa-star"></i> Featured
                                    </div>
                                @endif

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
                                        <i class="fas fa-layer-group"></i> {{ $event['sessions_count'] }} Sessions
                                    </div>
                                @endif

                                <!-- Quota Status -->
                                @if ($event['quota_percentage'] >= 80 && $event['quota_percentage'] < 100)
                                    <div class="quota-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Almost Full
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
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $event['date_range'] }}</span>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fas fa-tag"></i>
                                        <span>{{ $event['category'] }}</span>
                                    </div>

                                    @if ($event['sessions_count'] > 0)
                                        <div class="event-meta">
                                            <i class="fas fa-layer-group"></i>
                                            <span>{{ $event['sessions_count'] }}
                                                {{ $event['sessions_count'] > 1 ? 'Sessions' : 'Session' }}</span>
                                        </div>
                                    @endif

                                    @if ($event['speaker_info'])
                                        <div class="event-meta">
                                            <i class="fas fa-microphone"></i>
                                            <span>{{ $event['speaker_info'] }}</span>
                                        </div>
                                    @endif

                                    @if ($event['location_info'])
                                        <div class="event-meta">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $event['location_info'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Fee Information -->
                                <div class="event-fee-section">
                                    @if ($event['is_free'])
                                        <div class="event-fee free">
                                            <i class="fas fa-gift"></i> FREE EVENT
                                        </div>
                                    @else
                                        <div class="event-fee">
                                            <i class="fas fa-money-bill-wave"></i>
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
                                            <strong>{{ $event['next_session']['title'] }}</strong><br>
                                            <small>
                                                <i class="fas fa-clock"></i>
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
                                                <i class="fas fa-user"></i> {{ $event['next_session']['speaker'] }}
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <!-- Availability Status -->
                                @if ($event['availability_status'] === 'full')
                                    <div class="availability-status full">
                                        <i class="fas fa-users"></i> Event Full
                                        ({{ $event['registered_count'] }}/{{ $event['max_participants'] }})
                                    </div>
                                @elseif($event['availability_status'] === 'almost_full')
                                    <div class="availability-status almost-full">
                                        <i class="fas fa-users"></i> Almost Full
                                        ({{ $event['registered_count'] }}/{{ $event['max_participants'] }})
                                    </div>
                                @elseif($event['availability_status'] === 'no_sessions')
                                    <div class="availability-status no-sessions">
                                        <i class="fas fa-calendar-times"></i> No Sessions Available
                                    </div>
                                @else
                                    <div class="availability-status available">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $event['max_participants'] - $event['registered_count'] }} spots available
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="event-actions">
                                    @if ($event['status'] === 'open' && $event['availability_status'] === 'available')
                                        <a href="{{ route('member.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event">
                                            <i class="fas fa-ticket-alt"></i> Register Now
                                        </a>
                                    @elseif($event['availability_status'] === 'almost_full')
                                        <a href="{{ route('member.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event urgent">
                                            <i class="fas fa-bolt"></i> Register Now!
                                        </a>
                                    @elseif($event['availability_status'] === 'full')
                                        <a href="{{ route('member.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <button class="btn-register-event full" disabled>
                                            <i class="fas fa-users"></i> Event Full
                                        </button>
                                    @else
                                        <a href="{{ route('member.events.show', $event['id']) }}"
                                            class="btn-view-details">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <button class="btn-register-event" disabled>
                                            <i class="fas fa-times-circle"></i> {{ ucfirst($event['status']) }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-events ftco-animate">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Featured Events Available</h3>
                    <p>Stay tuned! Amazing events are coming soon.</p>
                    <a href="{{ route('member.events.index') }}" class="btn-view-all">
                        <i class="fas fa-search"></i> Browse All Events
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
                                <a href="{{ route('member.events.index') }}" class="btn-register-custom">Browse
                                    Events</a>
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
                    <a href="{{ asset('guest/images/image_1.jpg') }}"
                        class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_1.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_2.jpg') }}"
                        class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_2.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_3.jpg') }}"
                        class="gallery image-popup img d-flex align-items-center"
                        style="background-image: url({{ asset('guest/images/image_3.jpg') }});">
                        <div class="icon mb-4 d-flex align-items-center justify-content-center">
                            <span class="icon-instagram"></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 ftco-animate">
                    <a href="{{ asset('guest/images/image_4.jpg') }}"
                        class="gallery image-popup img d-flex align-items-center"
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
