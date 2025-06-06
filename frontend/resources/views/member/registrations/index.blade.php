@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
    <style>
        .registrations-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .registration-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .registration-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .card-header .event-date {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .registration-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: #666;
        }
        
        .meta-item i {
            font-size: 1.1rem;
            margin-right: 0.5rem;
            color: #667eea;
            width: 20px;
        }
        
        .meta-value {
            font-weight: 500;
            color: #333;
        }
        
        .sessions-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .sessions-summary h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.95rem;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .sessions-summary h4 i {
            margin-right: 0.5rem;
            color: #667eea;
        }
        
        .sessions-count {
            font-size: 0.85rem;
            color: #666;
        }
        
        .card-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-sm i {
            margin-right: 0.4rem;
            font-size: 0.9rem;
        }
        
        .btn-primary-sm {
            background: #667eea;
            color: white;
        }
        
        .btn-primary-sm:hover {
            background: #5a6fd8;
            color: white;
        }
        
        .btn-success-sm {
            background: #28a745;
            color: white;
        }
        
        .btn-success-sm:hover {
            background: #218838;
            color: white;
        }
        
        .btn-outline-sm {
            background: transparent;
            color: #667eea;
            border: 1px solid #667eea;
        }
        
        .btn-outline-sm:hover {
            background: #667eea;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .filter-tab {
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .filter-tab.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .filter-tab:hover:not(.active) {
            background: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <div class="registration-container">
        <!-- Page Header -->
        <div class="header">
            <div class="icon">
                <i class="bx bx-receipt"></i>
            </div>
            <h1 class="title">Registrasi Saya</h1>
            <p class="subtitle">Kelola semua registrasi event Anda</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-status="all">
                <i class="bx bx-list"></i>
                Semua
            </button>
            <button class="filter-tab" data-status="confirmed">
                <i class="bx bx-check-circle"></i>
                Terkonfirmasi
            </button>
            <button class="filter-tab" data-status="registered">
                <i class="bx bx-time"></i>
                Menunggu Verifikasi
            </button>
            <button class="filter-tab" data-status="draft">
                <i class="bx bx-edit"></i>
                Draft
            </button>
        </div>

        <!-- Registrations Grid -->
        @if(isset($registrations) && count($registrations) > 0)
            <div class="registrations-grid">
                @foreach($registrations as $registration)
                    <div class="registration-card" data-status="{{ $registration['registration_status'] ?? 'registered' }}">
                        <!-- Card Header -->
                        <div class="card-header">
                            <h3>{{ $registration['event_id']['name'] ?? 'Event Name' }}</h3>
                            <div class="event-date">
                                <i class="bx bx-calendar"></i>
                                {{ $registration['event_id']['date_range'] ?? 'Date Range' }}
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <!-- Registration Meta -->
                            <div class="registration-meta">
                                <div class="meta-item">
                                    <i class="bx bx-hash"></i>
                                    <span>ID: <span class="meta-value">{{ substr($registration['_id'] ?? 'N/A', -8) }}</span></span>
                                </div>

                                <div class="meta-item">
                                    <i class="bx bx-credit-card"></i>
                                    <span>Biaya: 
                                        <span class="meta-value">
                                            @if(($registration['payment_amount'] ?? 0) == 0)
                                                <strong style="color: #28a745;">GRATIS</strong>
                                            @else
                                                <strong>Rp {{ number_format($registration['payment_amount'] ?? 0) }}</strong>
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
                                    <i class="bx bxs-layer-plus"></i>
                                    Sesi Terdaftar
                                </h4>
                                <div class="sessions-count">
                                    {{ count($registration['session_registrations'] ?? []) }} sesi dipilih
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card-actions">
                                <a href="{{ route('member.myRegistrations.show', $registration['_id']) }}" class="btn-sm btn-primary-sm">
                                    <i class="bx bx-eye"></i>
                                    Detail
                                </a>

                                @if(($registration['registration_status'] ?? 'registered') === 'confirmed' || ($registration['payment_amount'] ?? 0) == 0)
                                    <a href="{{ route('member.myRegistrations.qr-codes', $registration['_id']) }}" class="btn-sm btn-success-sm">
                                        <i class="bx bx-qrcode"></i>
                                        QR Code
                                    </a>
                                @endif

                                @if(($registration['registration_status'] ?? 'registered') === 'draft')
                                    <a href="{{ route('member.events.register', $registration['event_id']['_id']) }}" class="btn-sm btn-outline-sm">
                                        <i class="bx bx-edit"></i>
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
                <i class="bx bx-calendar"></i>
                Lihat Event Lainnya
            </a>
        </div>
    </div>

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            const registrationCards = document.querySelectorAll('.registration-card');

            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    filterTabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');

                    const filterStatus = this.dataset.status;

                    registrationCards.forEach(card => {
                        const cardStatus = card.dataset.status;
                        
                        if (filterStatus === 'all') {
                            card.style.display = 'block';
                        } else {
                            if (cardStatus === filterStatus) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });
                });
            });
        });
    </script>
@endsection