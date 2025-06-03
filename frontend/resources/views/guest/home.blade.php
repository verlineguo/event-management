@extends('guest.layouts.app')

@section('styles')
    <style>
        .btn-primary-custom {
            background: #6b76ff;
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 10px rgba(107, 118, 255, 0.3);
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(107, 118, 255, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 2px solid #6b76ff;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            color: #6b76ff;
            text-decoration: none;
            display: inline-block;
            margin-left: 15px;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: #6b76ff;
            border: 2px solid #6b76ff;
            color: white;
            transform: translateY(-3px);
            text-decoration: none;
        }

        .btn-register-custom {
            background: transparent;
            border: 2px solid white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-left: 15px;
            transition: all 0.3s ease;
        }

        .btn-register-custom:hover {
            background: white;
            border: 2px solid white;
            color: #6b76ff;
            text-decoration: none;
            transform: translateY(-3px);
        }

        /* Events Section Styles - FIXED VERSION */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: auto;
            /* Changed from 100% to auto */
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(107, 118, 255, 0.15);
        }

        .event-poster {
            height: 250px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .event-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-poster img {
            transform: scale(1.05);
        }

        .event-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .event-card:hover .event-overlay {
            opacity: 1;
        }

        .event-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 10;
        }

        .status-open {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .status-closed {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .status-ongoing {
            background: rgba(255, 193, 7, 0.9);
            color: #212529;
        }

        .status-completed {
            background: rgba(108, 117, 125, 0.9);
            color: white;
        }

        .status-cancelled {
            background: rgba(253, 126, 20, 0.9);
            color: white;
        }

        .event-date-badge {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(107, 118, 255, 0.95);
            color: white;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            min-width: 70px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 10;
        }

        .event-date-badge .day {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
            display: block;
        }

        .event-date-badge .month {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .event-content {
            padding: 25px;
            position: relative;
        }

        .event-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
            color: #2d3748;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .event-meta {
            display: flex;
            align-items: center;
            color: #718096;
            font-size: 13px;
            font-weight: 500;
        }

        .event-meta i {
            margin-right: 8px;
            color: #6b76ff;
            width: 14px;
            font-size: 13px;
        }

        .event-meta-full {
            grid-column: 1 / -1;
        }

        .event-speaker {
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            padding: 12px;
            border-radius: 10px;
            margin: 12px 0;
            border-left: 3px solid #6b76ff;
        }

        .event-speaker .speaker-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .event-speaker .speaker-title {
            font-size: 12px;
            color: #718096;
        }

        .event-fee {
            background: linear-gradient(135deg, #6b76ff, #8b5cf6);
            color: white;
            padding: 10px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin: 12px 0;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(107, 118, 255, 0.3);
            letter-spacing: 0.3px;
        }

        .event-fee.free {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .event-fee i {
            margin-right: 6px;
        }

        .btn-register-event {
            background: linear-gradient(135deg, #6b76ff, #8b5cf6);
            color: white !important;
            border: none;
            padding: 12px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none !important;
            display: block;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            font-size: 14px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            cursor: pointer;
        }

        .btn-register-event:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-register-event:hover:before {
            left: 100%;
        }

        .btn-register-event:hover {
            transform: translateY(-2px);
            color: white !important;
            text-decoration: none !important;
            box-shadow: 0 8px 25px rgba(107, 118, 255, 0.4);
        }

        .btn-register-event:disabled {
            background: linear-gradient(135deg, #a0aec0, #718096) !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .no-events {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            border-radius: 20px;
            margin: 40px 0;
        }

        .no-events i {
            font-size: 60px;
            color: #cbd5e0;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #6b76ff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .no-events h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #2d3748;
        }

        .no-events p {
            font-size: 16px;
            color: #718096;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title .subheading {
            color: #6b76ff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 13px;
            margin-bottom: 8px;
            display: block;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 18px;
            line-height: 1.2;
        }

        .section-title h2 span {
            background: linear-gradient(135deg, #6b76ff, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title p {
            font-size: 16px;
            color: #718096;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                margin-top: 30px;
            }

            .event-content {
                padding: 20px;
            }

            .event-meta-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .section-title h2 {
                font-size: 32px;
            }

            .btn-primary-custom,
            .btn-secondary-custom,
            .btn-register-custom {
                display: block;
                margin: 10px 0;
                text-align: center;
                margin-left: 0;
            }
        }

        @media (max-width: 480px) {
            .event-poster {
                height: 200px;
            }

            .event-title {
                font-size: 18px;
            }

            .section-title h2 {
                font-size: 26px;
            }

            .events-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
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
                                @if (isset($event['poster']) && $event['poster'])
                                    <img src="{{ asset('storage/' . $event['poster']) }}"
                                        alt="{{ $event['name'] ?? 'Event Poster' }}">
                                @else
                                    <img src="{{ asset('images/default-event.jpg') }}" alt="Default Event Poster">
                                @endif
                                <div class="event-overlay"></div>

                                <div class="event-status status-{{ $event['status'] ?? 'open' }}">
                                    {{ ucfirst($event['status'] ?? 'Open') }}
                                </div>

                                <div class="event-date-badge">
                                    <div class="day">{{ \Carbon\Carbon::parse($event['date'])->format('d') }}</div>
                                    <div class="month">{{ \Carbon\Carbon::parse($event['date'])->format('M') }}</div>
                                </div>
                            </div>

                            <div class="event-content">
                                <h3 class="event-title">{{ $event['name'] ?? 'Event Name' }}</h3>

                                <div class="event-meta-grid">
                                    <div class="event-meta">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ \Carbon\Carbon::parse($event['date'])->format('M j, Y') }}</span>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $event['time'] ?? 'TBA' }}</span>
                                    </div>

                                    <div class="event-meta event-meta-full">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $event['location'] ?? 'Location TBA' }}</span>
                                    </div>

                                    <div class="event-meta">
                                        <i class="fas fa-users"></i>
                                        <span>{{ $event['available_slots'] ?? 0 }}/{{ number_format($event['max_participants'] ?? 0) }}</span>
                                    </div>
                                </div>

                                @if (isset($event['speaker']) && $event['speaker'] && $event['speaker'] !== 'Speaker TBA')
                                    <div class="event-speaker">
                                        <div class="speaker-name">
                                            <i class="fas fa-microphone"></i> {{ $event['speaker'] }}
                                        </div>
                                    </div>
                                @endif

                                <!-- Slot Information -->
                                @php
                                    $availableSlots = $event['available_slots'] ?? 0;
                                    $maxParticipants = $event['max_participants'] ?? 0;
                                    $slotsPercentage =
                                        $maxParticipants > 0
                                            ? (($maxParticipants - $availableSlots) / $maxParticipants) * 100
                                            : 0;
                                @endphp

                                @if ($availableSlots <= 0)
                                    <div class="slots-info full">
                                        <i class="fas fa-exclamation-triangle"></i> Event Full
                                    </div>
                                @elseif($slotsPercentage >= 80)
                                    <div class="slots-info almost-full">
                                        <i class="fas fa-clock"></i> Only {{ $availableSlots }} slots left!
                                    </div>
                                @else
                                    <div class="slots-info">
                                        <i class="fas fa-users"></i> {{ $availableSlots }} slots available
                                    </div>
                                @endif

                                <div class="event-fee {{ ($event['registration_fee'] ?? 0) == 0 ? 'free' : '' }}">
                                    <i class="fas fa-tag"></i>
                                    {{ ($event['registration_fee'] ?? 0) == 0 ? 'FREE EVENT' : 'Rp ' . number_format($event['registration_fee']) }}
                                </div>

                                @if (($event['status'] ?? 'open') === 'open' && $availableSlots > 0)
                                    <a href="{{ route('member.events.register', $event['id']) }}"
                                        class="btn-register-event">
                                        <i class="fas fa-ticket-alt"></i> Register Now
                                    </a>
                                @elseif($availableSlots <= 0)
                                    <button class="btn-register-event full" disabled>
                                        <i class="fas fa-users"></i> Event Full
                                    </button>
                                @else
                                    <button class="btn-register-event" disabled>
                                        <i class="fas fa-times-circle"></i> {{ ucfirst($event['status'] ?? 'Closed') }}
                                    </button>
                                @endif
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
