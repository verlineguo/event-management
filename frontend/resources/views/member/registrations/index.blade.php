@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/eventdetail.css') }}">
@endsection

@section('content')

    <div class="registration-container">
        <!-- Page Header -->
        <div class="container mb-5">
            <div class="section-title ftco-animate">
                <h2><span>Your</span> Events</h2>
                <p>Events you have previously registered for</p>

            </div>
        </div>


        <div class="search-bar" style="margin-bottom: 1.5rem;">
            <input type="text" id="registration-search" class="form-control" placeholder="Cari event, status, atau ID...">
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-status="all">
                <i class="bx bx-list align-middle"></i>
                Semua
            </button>
            <button class="filter-tab" data-status="confirmed">
                <i class="bx bx-check-circle align-middle"></i>
                Terkonfirmasi
            </button>
            <button class="filter-tab" data-status="registered">
                <i class="bx bx-timer align-middle"></i>
                Menunggu Verifikasi
            </button>
            <button class="filter-tab" data-status="draft">
                <i class="bx bx-edit align-middle"></i>
                Draft
            </button>
        </div>



        <!-- Registrations Grid -->
        @if (isset($registrations) && count($registrations) > 0)
            <div class="registrations-grid">
                @foreach ($registrations as $registration)
                    <div class="registration-card" data-status="{{ $registration['registration_status'] ?? 'registered' }}">
                        <!-- Card Header -->
                        <div class="card-header">
                            <h3>{{ $registration['event_id']['name'] ?? 'Event Name' }}</h3>
                            <div class="event-date">
                                <i class="bx bx-calendar align-middle"></i>
                                {{ $registration['event_id']['date_range'] ?? 'Date Range' }}
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <!-- Registration Meta -->
                            <div class="registration-meta">
                                <div class="meta-item">
                                    <i class="bx bx-hashtag align-middle"></i>
                                    <span>ID: <span
                                            class="meta-value">{{ substr($registration['_id'] ?? 'N/A', -8) }}</span></span>
                                </div>

                                <div class="meta-item">
                                    <i class="bx bx-credit-card"></i>
                                    <span>Biaya:
                                        <span class="meta-value">
                                            @if (($registration['payment_amount'] ?? 0) == 0)
                                                <strong style="color: #28a745;">GRATIS</strong>
                                            @else
                                                <strong>Rp
                                                    {{ number_format($registration['payment_amount'] ?? 0) }}</strong>
                                            @endif
                                        </span>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <i class="bx bx-info-circle"></i>
                                    <span>Status:
                                        @php
                                            $status = $registration['registration_status'] ?? 'registered';
                                            $statusClass = 'status-pending';
                                            $statusText = 'Menunggu Verifikasi';

                                            if ($status === 'confirmed') {
                                                $statusClass = 'status-confirmed';
                                                $statusText = 'Terkonfirmasi';
                                            } elseif ($status === 'cancelled') {
                                                $statusClass = 'status-cancelled';
                                                $statusText = 'Dibatalkan';
                                            } elseif ($status === 'draft') {
                                                $statusClass = 'status-draft';
                                                $statusText = 'Draft';
                                            }
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <i class="bx bx-timer"></i>
                                    <span>Terdaftar:
                                        <span class="meta-value">
                                            {{ isset($registration['createdAt']) ? \Carbon\Carbon::parse($registration['createdAt'])->format('d M Y') : 'N/A' }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Sessions Summary -->
                            <div class="sessions-summary">
                                <h4>
                                    <i class="bx bx-list"></i>
                                    Sesi Terdaftar
                                </h4>
                                <div class="sessions-count">
                                    {{ count($registration['session_registrations'] ?? []) }} sesi dipilih
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card-actions">
                                <a href="{{ route('member.myRegistrations.show', $registration['_id']) }}"
                                    class="btn-sm btn-primary-sm">
                                    <i class="bx bx-eye align-middle"></i>
                                    Detail
                                </a>

                                @if (
                                    ($registration['registration_status'] ?? 'registered') === 'confirmed' ||
                                        ($registration['payment_amount'] ?? 0) == 0)
                                    <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}"
                                        class="btn-sm btn-success-sm">
                                        <i class="bx bx-qr align-middle"></i>
                                        QR Code
                                    </a>
                                @endif

                                @if (($registration['registration_status'] ?? 'registered') === 'draft')
                                    <a href="{{ route('member.events.register', $registration['event_id']['_id']) }}"
                                        class="btn-sm btn-outline-sm">
                                        <i class="bx bx-edit align-middle"></i>
                                        Lanjutkan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bx bx-receipt"></i>
                <h3>Belum Ada Registrasi</h3>
                <p>Anda belum mendaftar untuk event apapun</p>
                <a href="{{ route('member.events.index') }}" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="bx bx-calendar"></i>
                    Lihat Event Tersedia
                </a>
            </div>
        @endif

        <!-- Back to Events -->
        <div class="action-buttons" style="justify-content: center; margin-top: 2rem;">
            <a href="{{ route('member.events.index') }}" class="btn btn-outline">
                <i class="bx bx-calendar align-middle"></i>
                Lihat Event Lainnya
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            const registrationCards = document.querySelectorAll('.registration-card');
            const searchInput = document.getElementById('registration-search');

            // Filter tab logic (sudah ada)
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    filterRegistrations();
                });
            });

            // Search logic
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterRegistrations();
                });
            }

            function filterRegistrations() {
                const filterStatus = document.querySelector('.filter-tab.active').dataset.status;
                const keyword = searchInput ? searchInput.value.toLowerCase() : '';

                registrationCards.forEach(card => {
                    const cardStatus = card.dataset.status;
                    const cardText = card.innerText.toLowerCase();

                    const statusMatch = (filterStatus === 'all') || (cardStatus === filterStatus);
                    const searchMatch = !keyword || cardText.includes(keyword);

                    card.style.display = (statusMatch && searchMatch) ? 'block' : 'none';
                });
            }
        });
    </script>
@endsection
