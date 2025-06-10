@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <style>
        /* ==================== EVENT DETAIL PAGE CSS ==================== */

        /* General Variables */
        :root {
            --primary-color: #6b76ff;
            --primary-dark: #5865e8;
            --primary-light: #8892ff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 20px rgba(107, 118, 255, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        /* ==================== PAGE CONTAINER ==================== */
        .page-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }

        /* ==================== EVENT HEADER ==================== */
        .event-detail-header {
            background: linear-gradient(135deg, var(--primary-light) 0%);
            color: white;
            padding: 2rem 0 3rem;
            position: relative;
            overflow: hidden;
        }

        .event-detail-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .back-navigation {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .back-btn {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .event-header-content {
            position: relative;
            z-index: 2;
        }

        .event-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .event-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .event-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .event-meta i {
            width: 18px;
            text-align: center;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);

        }

        .event-meta span {
            font-size: 1.5rem;
        }

        .event-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .event-status.status-open {
            background: rgba(40, 167, 69, 0.5);
            color: #94dd94;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .event-status.status-closed {
            background: rgba(220, 53, 69, 0.2);
            color: #ffb3ba;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .event-status.status-full {
            background: rgba(255, 193, 7, 0.2);
            color: #fff3cd;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        /* ==================== ACTION BUTTONS ==================== */
        .event-actions-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .btn-register-main {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            font-size: 1.1rem;
        }

        .btn-register-main:hover:not(:disabled) {
            background: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            color: var(--primary-dark);
        }

        .btn-register-main:disabled,
        .btn-register-main.full {
            background: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.7);
            cursor: not-allowed;
        }

        .registration-status.registered {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: rgba(40, 167, 69, 0.5);
            color: #90ee90;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        /* ==================== EVENT CONTENT ==================== */
        .event-detail-content {
            padding: 3rem 0;
        }

        /* ==================== POSTER SECTION ==================== */
        .event-poster-section {
            margin-bottom: 3rem;
        }

        .poster-container {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            cursor: pointer;
            transition: var(--transition);
        }


        .event-poster-detail {
            width: 100%;
            height: auto;
            display: block;
            transition: var(--transition);
        }



        .poster-container:hover .poster-overlay {
            opacity: 1;
        }

        .poster-overlay i {
            font-size: 2rem;
        }

        /* ==================== SECTION TITLES ==================== */
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary-color);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-light);
        }

        .section-title i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .sessions-count {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            margin-left: auto;
        }

        /* ==================== DESCRIPTION SECTION ==================== */
        .event-description-section {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 3rem;
        }

        .event-description-content {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #495057;
        }

        /* ==================== SESSIONS SECTION ==================== */
        .event-sessions-section {
            margin-bottom: 3rem;
        }

        .sessions-list-detail {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .session-detail-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .session-detail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(107, 118, 255, 0.15);
        }

        .session-detail-card.full {
            border-left-color: var(--danger-color);
            background: #fff5f5;
        }

        .session-header-detail {
            margin-bottom: 1.5rem;
        }

        .session-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .session-title-row h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .session-subtitle {
            font-size: 1rem;
            color: #6c757d;
            margin: 0;
            font-style: italic;
        }

        .session-fee-detail .fee-amount {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .session-fee-detail .fee-free {
            background: var(--success-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-danger {
            background: var(--danger-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: #856404;
        }

        /* ==================== SESSION INFO GRID ==================== */
        .session-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .session-info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            transition: var(--transition);
        }

        .session-info-item:hover {
            background: #e8f0fe;
            transform: translateY(-1px);
        }

        .session-info-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 0.2rem;
            width: 20px;
            text-align: center;
        }

        .session-info-item div {
            flex: 1;
        }

        .session-info-item strong {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .session-info-item span {
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* ==================== SESSION DESCRIPTION ==================== */
        .session-description {
            background: #f8f9ff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 3px solid var(--primary-color);
        }

        .session-description h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .session-description p {
            color: #495057;
            line-height: 1.6;
            margin: 0;
        }

        /* ==================== CAPACITY INFO ==================== */
        .capacity-info-detail {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .capacity-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .capacity-text {
            color: #2c3e50;
            font-weight: 600;
        }

        .available-text {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .capacity-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .capacity-fill {
            height: 100%;
            transition: var(--transition);
            border-radius: 4px;
        }

        .capacity-fill.low {
            background: var(--success-color);
        }

        .capacity-fill.medium {
            background: var(--info-color);
        }

        .capacity-fill.high {
            background: var(--warning-color);
        }

        .capacity-fill.full {
            background: var(--danger-color);
        }

        /* ==================== SIDEBAR ==================== */
        .event-sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            position: sticky;
            top: 2rem;
        }

        /* ==================== SIDEBAR CARDS ==================== */
        .event-summary-card,
        .registration-status-card,
        .registration-cta-card,
        .share-event-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .event-summary-card:hover,
        .registration-status-card:hover,
        .registration-cta-card:hover,
        .share-event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(107, 118, 255, 0.15);
        }

        .event-summary-card h4,
        .registration-status-card h4,
        .registration-cta-card h4,
        .share-event-card h4 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ==================== SUMMARY INFO ==================== */
        .summary-info {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .summary-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            transition: var(--transition);
        }

        .summary-item:hover {
            background: #e8f0fe;
        }

        .summary-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-top: 0.2rem;
            width: 18px;
            text-align: center;
        }

        .summary-item div {
            flex: 1;
        }

        .summary-item strong {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .summary-item span {
            color: #6c757d;
        }

        /* ==================== REGISTRATION CARDS ==================== */
        .registration-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .status-badge.registered {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .registration-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .btn-register-sidebar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
            margin-bottom: 1rem;
        }

        .btn-register-sidebar:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
        }

        .urgency-notice,
        .full-notice,
        .closed-notice {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .urgency-notice {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .full-notice {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .closed-notice {
            background: rgba(108, 117, 125, 0.1);
            color: #495057;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }

        /* ==================== SHARE BUTTONS ==================== */
        .share-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .share-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .share-btn.whatsapp {
            background: #25d366;
            color: white;
        }

        .share-btn.whatsapp:hover {
            background: #1ebe5a;
            color: white;
        }

        .share-btn.twitter {
            background: #1da1f2;
            color: white;
        }

        .share-btn.twitter:hover {
            background: #0d8bd9;
            color: white;
        }

        .share-btn.facebook {
            background: #4267b2;
            color: white;
        }

        .share-btn.facebook:hover {
            background: #365899;
            color: white;
        }

        .share-btn.copy {
            background: var(--primary-color);
            color: white;
        }

        .share-btn.copy:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 768px) {
            .event-title {
                font-size: 2rem;
            }

            .event-meta-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .event-actions-header {
                align-items: stretch;
                margin-top: 1.5rem;
            }

            .session-title-row {
                flex-direction: column;
                gap: 1rem;
            }

            .session-info-grid {
                grid-template-columns: 1fr;
            }

            .capacity-stats {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .share-buttons {
                grid-template-columns: 1fr;
            }

            .event-sidebar {
                position: static;
                margin-top: 2rem;
            }
        }

        @media (max-width: 576px) {
            .event-detail-header {
                padding: 1.5rem 0 2rem;
            }

            .event-title {
                font-size: 1.75rem;
            }

            .event-detail-content {
                padding: 2rem 0;
            }

            .session-detail-card,
            .event-summary-card,
            .registration-status-card,
            .registration-cta-card,
            .share-event-card {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.25rem;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
@endsection

@section('content')
    <div class="page-container">
        <!-- Event Header -->
        <div class="event-detail-header">
            <div class="container">
                <div class="back-navigation">
                    <a href="{{ route('member.events.index') }}" class="back-btn">
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
                            @if (isset($userRegistration) && $userRegistration)
                                <div class="registration-status-card">
                                    <h4>Your Registration</h4>
                                    <div class="registration-details">
                                        <div class="status-badge registered">
                                            <i class="bx bx-check-circle"></i>
                                            Registered
                                        </div>
            

                                        <a href="{{ route('member.myRegistrations.index')}}"
                                            class="btn btn-primary">
                                            <i class="bx bx-eye"></i>
                                            Lihat Detail Registrasi
                                        </a>
                                        </p>
                                    </div>
                                </div>
                            @else
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
                            @endif

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
