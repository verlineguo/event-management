@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">

    <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
@endsection

@section('content')
    <!-- Page Header -->
    <div class="ftco-section bg-light">
        <div class="container">
            <div class="section-title ftco-animate">
                <h2><span>Discover</span> Events</h2>
                <p>Join our exciting events and expand your knowledge with industry experts</p>
            </div>
        </div>
    </div>



    <!-- Filter Section -->
    <div class="events-filter">
        <div class="container">
            <form method="GET" action="{{ route('member.event.search') }}" id="searchForm">
                <div class="filter-group">
                    <label for="search" class="form-label">Search Events</label>
                    <input type="text" class="form-control" id="search" name="q"
                        placeholder="Search by name or description..." value="{{ request('q') }}">
                </div>

                <div class="filter-group">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['_id'] }}"
                                {{ old('category_id') == $category['_id'] ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="fee_type" class="form-label">Fee Type</label>
                    <select id="fee_type" name="fee_type" class="form-select">
                        <option value="">All Events</option>
                        <option value="free" {{ request('fee_type') === 'free' ? 'selected' : '' }}>Free Events</option>
                        <option value="paid" {{ request('fee_type') === 'paid' ? 'selected' : '' }}>Paid Events</option>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i> Filter Events
                    </button>
                    <button type="button" class="clear-filter" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Container -->
    <section class="events-container">
        <div class="container">
            <!-- Results Info -->
            <div class="results-info ftco-animate">
                <div class="results-count">
                    <strong>{{ isset($transformedEvents) ? count($transformedEvents) : 0 }}</strong> events found
                </div>
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort" onchange="applySorting(this.value)">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name_asc">Name A-Z</option>
                        <option value="name_desc">Name Z-A</option>
                        <option value="date_asc">Date Ascending</option>
                        <option value="date_desc">Date Descending</option>
                    </select>
                </div>
            </div>

            <!-- Events Grid -->
            @if (isset($transformedEvents) && count($transformedEvents) > 0)
                <div class="events-grid" id="eventsGrid">
                    @foreach ($transformedEvents as $event)
                        <div class="event-card ftco-animate" data-category="{{ strtolower($event['category']) }}"
                            data-status="{{ $event['status'] }}"
                            data-fee-type="{{ $event['is_free'] ? 'free' : 'paid' }}">
                            <div class="event-poster">
                                <a href="{{ $event['poster'] }}" class="poster-link" data-lightbox="event-poster"
                                    data-title="{{ $event['name'] }}">
                                    <img src="{{ $event['poster'] }}" alt="{{ $event['name'] }}" loading="lazy">
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
                                        <i class="fas fa-layer-group"></i> {{ $event['sessions_count'] }} Sessions
                                    </div>
                                @endif
                            </div>

                            <div class="event-content">
                                <h3 class="event-title">{{ $event['name'] }}</h3>

                                <!-- Description Preview -->
                                <p class="event-description">
                                    {{ Str::limit($event['description'], 120) }}
                                </p>

                                <!-- Meta Information -->
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
                                @else
                                    <div class="availability-status available">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $event['max_participants'] - $event['registered_count'] }} spots available
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="event-actions">
                                    <a href="{{ route('member.events.show', $event['id']) }}" class="btn-view-details">
                                        <i class="fas fa-eye"></i> Details
                                    </a>

                                    @if ($event['status'] === 'open' && $event['availability_status'] === 'available')
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event">
                                            <i class="fas fa-ticket-alt"></i> Register
                                        </a>
                                    @elseif($event['availability_status'] === 'almost_full')
                                        <a href="{{ route('member.events.register', $event['id']) }}"
                                            class="btn-register-event urgent">
                                            <i class="fas fa-bolt"></i> Register Now!
                                        </a>
                                    @elseif($event['availability_status'] === 'full')
                                        <button class="btn-register-event full" disabled>
                                            <i class="fas fa-users"></i> Full
                                        </button>
                                    @else
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
                    <h3>No Events Found</h3>
                    <p>{{ request()->hasAny(['search', 'category', 'status', 'fee_type']) ? 'Try adjusting your filters to find more events.' : 'Check back soon for upcoming events!' }}
                    </p>
                    @if (request()->hasAny(['search', 'category', 'status', 'fee_type']))
                        <button onclick="clearFilters()" class="btn-create-event">
                            <i class="fas fa-refresh"></i> Clear Filters
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <script>
        // Filter Functions
        function clearFilters() {
            document.getElementById('eventFilterForm').reset();
            window.location.href = window.location.pathname;
        }

        // Live Search
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterEvents();
            }, 500);
        });

        // Filter Events (Client-side for better UX)
        function filterEvents() {
            const search = document.getElementById('search').value.toLowerCase();
            const category = document.getElementById('category').value.toLowerCase();
            const status = document.getElementById('status').value.toLowerCase();
            const feeType = document.getElementById('fee_type').value;

            const eventCards = document.querySelectorAll('.event-card');
            let visibleCount = 0;

            eventCards.forEach(card => {
                const title = card.querySelector('.event-title').textContent.toLowerCase();
                const description = card.querySelector('.event-description').textContent.toLowerCase();
                const cardCategory = card.dataset.category;
                const cardStatus = card.dataset.status;
                const cardFeeType = card.dataset.feeType;

                const matchesSearch = !search || title.includes(search) || description.includes(search);
                const matchesCategory = !category || cardCategory === category;
                const matchesStatus = !status || cardStatus === status;
                const matchesFeeType = !feeType || cardFeeType === feeType;

                if (matchesSearch && matchesCategory && matchesStatus && matchesFeeType) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update results count
            document.querySelector('.results-count').innerHTML = `<strong>${visibleCount}</strong> events found`;
        }

        // Sorting
        function applySorting(sortType) {
            const grid = document.getElementById('eventsGrid');
            const cards = Array.from(grid.children);

            cards.sort((a, b) => {
                const titleA = a.querySelector('.event-title').textContent;
                const titleB = b.querySelector('.event-title').textContent;

                switch (sortType) {
                    case 'name_asc':
                        return titleA.localeCompare(titleB);
                    case 'name_desc':
                        return titleB.localeCompare(titleA);
                    case 'newest':
                    case 'oldest':
                    case 'date_asc':
                    case 'date_desc':
                        // For now, keep original order
                        // You can implement date sorting based on your date format
                        return 0;
                    default:
                        return 0;
                }
            });

            // Clear and re-append sorted cards
            grid.innerHTML = '';
            cards.forEach(card => grid.appendChild(card));
        }

        // Initialize filters on page load
        document.addEventListener('DOMContentLoaded', function() {
            // If there are URL parameters, apply them
            if (window.location.search) {
                filterEvents();
            }
        });
    </script>
@endsection
