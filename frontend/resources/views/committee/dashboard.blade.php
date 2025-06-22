@extends('committee.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Welcome Card -->
            <div class="col-xxl-8 mb-6 order-0">
                <div class="card">
                    <div class="d-flex align-items-start row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">Selamat Datang Committee! 🎯</h5>
                                <p class="mb-6">
                                    Dashboard committee untuk mengelola event, session, dan aktivitas peserta.<br />
                                    Total {{ number_format($dashboardData['stats']['totalEvents']) }} event yang Anda kelola.
                                </p>
                                <a href="{{ route('committee.event.index') }}" class="btn btn-sm btn-outline-primary me-2">Kelola Event</a>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-6">
                                <img src="../assets/img/illustrations/man-with-laptop.png" height="175"
                                    alt="Committee Dashboard" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="events"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt1">
                                            <a class="dropdown-item" href="{{ route('committee.event.index') }}">Lihat Events</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Total Events</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalEvents']) }}</h4>
                                <small class="text-{{ $dashboardData['stats']['eventGrowthPercentage'] >= 0 ? 'success' : 'danger' }} fw-medium">
                                    <i class="icon-base bx bx-{{ $dashboardData['stats']['eventGrowthPercentage'] >= 0 ? 'up' : 'down' }}-arrow-alt"></i>
                                    {{ number_format(abs($dashboardData['stats']['eventGrowthPercentage']), 1) }}%
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="registrations"
                                            class="rounded" />
                                    </div>
                                    
                                </div>
                                <p class="mb-1">Total Registrations</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalRegistrations']) }}</h4>
                                <small class="text-info fw-medium">
                                    {{ number_format($dashboardData['stats']['pendingPayments']) }} pending payment
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Registration Statistics Chart -->
            <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
                <div class="card">
                    <div class="row row-bordered g-0">
                        <div class="col-lg-8">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">Statistik Registrasi</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="registrationChart" height="300"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                                <div class="text-center mb-6">
                                    <h6 class="mb-2">Event Status</h6>
                                    <div class="d-flex justify-content-center">
                                        <canvas id="eventStatusChart" width="150" height="150"></canvas>
                                    </div>
                                </div>

                                <div class="d-flex gap-11 justify-content-between w-100">
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-success">
                                                <i class="icon-base bx bx-check-circle icon-lg text-success"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Open</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['openEvents']) }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-primary">
                                                <i class="icon-base bx bx-calendar-check icon-lg text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Completed</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['completedEvents']) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Stats -->
            <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
                <div class="row">
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/paypal.png" alt="sessions"
                                            class="rounded" />
                                    </div>
                                    
                                </div>
                                <p class="mb-1">Total Sessions</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalSessions']) }}</h4>
                                <small class="text-success fw-medium">
                                    {{ number_format($dashboardData['stats']['scheduledSessions']) }} scheduled
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/cc-primary.png" alt="attendance"
                                            class="rounded" />
                                    </div>
                                   
                                </div>
                                <p class="mb-1">Total Attendance</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalAttendance']) }}</h4>
                                <small class="text-success fw-medium">
                                    {{ number_format($dashboardData['stats']['totalCertificates']) }} certificates
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Events Performance -->
            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Top Performing Events</h5>
                            <p class="card-subtitle">By Registration Rate</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @forelse($dashboardData['charts']['eventsPerformance'] as $event)
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $event['occupancyRate'] >= 80 ? 'success' : ($event['occupancyRate'] >= 50 ? 'warning' : 'danger') }}">
                                        <i class="icon-base bx bx-calendar-event"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $event['name'] }}</h6>
                                        <small>{{ $event['registrationCount'] }}/{{ $event['max_participants'] }} peserta</small>
                                    </div>
                                    <div class="user-progress">
                                        <h6 class="mb-0 text-{{ $event['occupancyRate'] >= 80 ? 'success' : ($event['occupancyRate'] >= 50 ? 'warning' : 'danger') }}">
                                            {{ number_format($event['occupancyRate'], 1) }}%
                                        </h6>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-4">
                                <p class="text-muted">Belum ada data event</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Upcoming Sessions -->
            <div class="col-md-6 col-lg-8 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Session Mendatang</h5>
                        
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse($dashboardData['recentActivity']['upcomingSessions'] as $session)
                            <li class="d-flex align-items-center mb-6">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="icon-base bx bx-time"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="fw-normal mb-0">{{ $session['title'] }}</h6>
                                        <small class="d-block text-muted">{{ $session['event_id']['name'] ?? 'N/A' }}</small>
                                        <small class="d-block">{{ $session['start_time'] }} - {{ $session['end_time'] }}</small>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2 flex-column">
                                        <span class="badge bg-primary">
                                            {{ ucfirst($session['status']) }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}
                                        </small>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-4">
                                <p class="text-muted">Tidak ada session mendatang</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12 mb-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Aktivitas Attendance Terbaru</h5>
                        
                    </div>
                    <div class="card-body pt-4">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Peserta</th>
                                        <th>Session</th>
                                        <th>Event</th>
                                        <th>Waktu Scan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dashboardData['recentActivity']['recentAttendance'] as $attendance)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-info">
                                                        {{ strtoupper(substr($attendance['user_id']['name'] ?? 'N', 0, 2)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $attendance['user_id']['name'] ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $attendance['user_id']['email'] ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $attendance['session_id']['title'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $attendance['session_id']['event_id']['name'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($attendance['createdAt'])->format('d M Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Hadir</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted">Belum ada aktivitas attendance</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="row">
            <div class="col-12 mb-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Event Terbaru</h5>
                        <div class="dropdown">
                            <button class="btn text-body-secondary p-0" type="button" id="recentEvents"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentEvents">
                                <a class="dropdown-item" href="{{ route('committee.event.index') }}">Lihat Semua Event</a>
                                <a class="dropdown-item" href="{{ route('committee.event.create') }}">Buat Event Baru</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            @forelse($dashboardData['recentActivity']['recentEvents'] as $event)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-calendar-event"></i>
                                                </span>
                                            </div>
                                            <span class="badge bg-{{ 
                                                $event['status'] === 'open' ? 'success' : 
                                                ($event['status'] === 'completed' ? 'info' : 
                                                ($event['status'] === 'closed' ? 'warning' : 'danger')) 
                                            }}">
                                                {{ ucfirst($event['status']) }}
                                            </span>
                                        </div>
                                        <h6 class="mb-2">{{ $event['name'] }}</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-muted">Kategori:</small>
                                            <small class="fw-medium">{{ $event['category_id']['name'] ?? 'N/A' }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-muted">Max Peserta:</small>
                                            <small class="fw-medium">{{ number_format($event['max_participants']) }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Dibuat:</small>
                                            <small class="fw-medium">{{ \Carbon\Carbon::parse($event['createdAt'])->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">Belum ada event yang dibuat</p>
                                <a href="{{ route('committee.event.create') }}" class="btn btn-primary">
                                    <i class="icon-base bx bx-plus me-1"></i>
                                    Buat Event Pertama
                                </a>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Registration Growth Chart
        const monthlyRegistrations = @json($dashboardData['charts']['monthlyRegistrations']);
        const registrationLabels = monthlyRegistrations.map(item => {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[item._id.month - 1] + ' ' + item._id.year;
        });
        const registrationData = monthlyRegistrations.map(item => item.count);

        new Chart(document.getElementById('registrationChart'), {
            type: 'line',
            data: {
                labels: registrationLabels,
                datasets: [{
                    label: 'Registrasi',
                    data: registrationData,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Event Status Chart
        new Chart(document.getElementById('eventStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Completed', 'Closed', 'Cancelled'],
                datasets: [{
                    data: [
                        {{ $dashboardData['stats']['openEvents'] }}, 
                        {{ $dashboardData['stats']['completedEvents'] }}, 
                        {{ $dashboardData['stats']['closedEvents'] }}, 
                        {{ $dashboardData['stats']['cancelledEvents'] }}
                    ],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });

        // Events by Category Chart (if needed)
        @if(count($dashboardData['charts']['eventsByCategory']) > 0)
        const categoryData = @json($dashboardData['charts']['eventsByCategory']);
        if (categoryData.length > 0) {
            // You can create additional charts here if needed
            console.log('Category data available:', categoryData);
        }
        @endif

        // Registration Status Chart (if needed)
        @if(count($dashboardData['charts']['registrationStatusStats']) > 0)
        const registrationStatusData = @json($dashboardData['charts']['registrationStatusStats']);
        if (registrationStatusData.length > 0) {
            // You can create additional charts here if needed
            console.log('Registration status data available:', registrationStatusData);
        }
        @endif

        // Auto refresh data every 5 minutes
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 300000);

        // Update timestamps
        function updateTimestamps() {
            const timestamps = document.querySelectorAll('[data-timestamp]');
            timestamps.forEach(function(element) {
                const timestamp = element.getAttribute('data-timestamp');
                const date = new Date(timestamp);
                const now = new Date();
                const diff = now - date;
                
                // Update relative time
                if (diff < 60000) {
                    element.textContent = 'Baru saja';
                } else if (diff < 3600000) {
                    element.textContent = Math.floor(diff / 60000) + ' menit lalu';
                } else if (diff < 86400000) {
                    element.textContent = Math.floor(diff / 3600000) + ' jam lalu';
                } else {
                    element.textContent = Math.floor(diff / 86400000) + ' hari lalu';
                }
            });
        }

        // Update timestamps every minute
        setInterval(updateTimestamps, 60000);
        updateTimestamps();
    </script>
@endsection