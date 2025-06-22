@extends('finance.layouts.app')
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
            <div class="col-xxl-8 mb-6 order-0">
                <div class="card">
                    <div class="d-flex align-items-start row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">Selamat Datang Finance! 💰</h5>
                                <p class="mb-6">
                                    Dashboard finance untuk verifikasi pembayaran dan pengelolaan keuangan.<br />
                                    {{ number_format($dashboardData['stats']['pendingPayments']) }} pembayaran menunggu verifikasi.
                                </p>
                                <a href="{{ route('finance.payment.index') }}" class="btn btn-sm btn-outline-primary me-2">Verifikasi Pembayaran</a>
                                <a href="{{ route('finance.payment.index') }}" class="btn btn-sm btn-outline-warning">Riwayat Pembayaran</a>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-6">
                                <img src="../assets/img/illustrations/man-with-laptop.png" height="175"
                                    alt="Finance Dashboard" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="pending payments"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                            <a class="dropdown-item" href="#">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Pending Payments</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['pendingPayments']) }}</h4>
                                <small class="text-warning fw-medium">
                                    <i class="icon-base bx bx-time"></i>
                                    Menunggu verifikasi
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="approved payments"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                            <a class="dropdown-item" href="#">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Approved Payments</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['approvedPayments']) }}</h4>
                                <small class="text-{{ $dashboardData['stats']['paymentGrowthPercentage'] >= 0 ? 'success' : 'danger' }} fw-medium">
                                    <i class="icon-base bx bx-{{ $dashboardData['stats']['paymentGrowthPercentage'] >= 0 ? 'up' : 'down' }}-arrow-alt"></i>
                                    {{ number_format(abs($dashboardData['stats']['paymentGrowthPercentage']), 1) }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Payment Statistics -->
            <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
                <div class="card">
                    <div class="row row-bordered g-0">
                        <div class="col-lg-8">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">Statistik Pembayaran</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentStatsChart" height="300"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                                <div class="text-center mb-6">
                                    <h6 class="mb-2">Payment Status</h6>
                                    <div class="d-flex justify-content-center">
                                        <canvas id="paymentStatusChart" width="150" height="150"></canvas>
                                    </div>
                                </div>

                                <div class="d-flex gap-11 justify-content-between w-100 flex-column">
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-warning">
                                                <i class="icon-base bx bx-time icon-lg text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Pending</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['pendingPayments']) }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-success">
                                                <i class="icon-base bx bx-check icon-lg text-success"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Approved</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['approvedPayments']) }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-danger">
                                                <i class="icon-base bx bx-x icon-lg text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Rejected</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['rejectedPayments']) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Stats -->
            <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
                <div class="row">
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/paypal.png" alt="revenue"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                            <a class="dropdown-item" href="#">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Total Revenue</p>
                                <h4 class="card-title mb-3">Rp {{ number_format($dashboardData['stats']['totalRevenue']) }}</h4>
                                <small class="text-success fw-medium">
                                    Semua pembayaran
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/cc-primary.png" alt="monthly revenue"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                            <a class="dropdown-item" href="#">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Monthly Revenue</p>
                                <h4 class="card-title mb-3">Rp {{ number_format($dashboardData['stats']['monthlyRevenue']) }}</h4>
                                <small class="text-info fw-medium">
                                    Bulan ini
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="today pending"
                                            class="rounded" />
                                    </div>
                                </div>
                                <p class="mb-1">Today's Pending</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['todayPendingPayments']) }}</h4>
                                <small class="text-warning fw-medium">
                                    Hari ini
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="today processed"
                                            class="rounded" />
                                    </div>
                                </div>
                                <p class="mb-1">Today's Processed</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['todayProcessedPayments']) }}</h4>
                                <small class="text-success fw-medium">
                                    Hari ini
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Urgent Pending Payments -->
            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Pembayaran Mendesak</h5>
                            <p class="card-subtitle">{{ number_format($dashboardData['stats']['pendingPayments']) }} Total Pending</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @forelse($dashboardData['recentActivity']['urgentPendingPayments'] as $payment)
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="icon-base bx bx-time"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $payment['user_id']['name'] }}</h6>
                                        <small class="d-block text-muted">{{ $payment['event_id']['name'] }}</small>
                                        <small class="d-block">Rp {{ number_format($payment['payment_amount']) }}</small>
                                    </div>
                                    <div class="user-progress text-end">
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($payment['createdAt'])->diffForHumans() }}
                                        </small>
                                        <div class="mt-1">
                                            <a href="#" class="btn btn-xs btn-outline-primary">
                                                Verifikasi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-4">
                                <p class="text-muted">Tidak ada pembayaran pending</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Payment Activities -->
            <div class="col-md-6 col-lg-8 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Aktivitas Pembayaran Terbaru</h5>
                        <div class="dropdown">
                            <button class="btn text-body-secondary p-0" type="button" id="recentPayments"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentPayments">
                                <a class="dropdown-item" href="#">Hari Ini</a>
                                <a class="dropdown-item" href="#">7 Hari Terakhir</a>
                                <a class="dropdown-item" href="#">30 Hari Terakhir</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse($dashboardData['recentActivity']['recentPayments'] as $payment)
                            <li class="d-flex align-items-center mb-6">
                                <div class="avatar flex-shrink-0 me-3">
                                    @if($payment['payment_status'] == 'pending')
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="icon-base bx bx-time"></i>
                                        </span>
                                    @elseif($payment['payment_status'] == 'approved')
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="icon-base bx bx-check"></i>
                                        </span>
                                    @else
                                        <span class="avatar-initial rounded bg-label-danger">
                                            <i class="icon-base bx bx-x"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="fw-normal mb-0">{{ $payment['user_id']['name'] }}</h6>
                                        <small class="d-block text-muted">{{ $payment['event_id']['name'] }}</small>
                                        <small class="d-block">Rp {{ number_format($payment['payment_amount']) }}</small>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2 flex-column">
                                        @if($payment['payment_status'] == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($payment['payment_status'] == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                            <small class="text-muted">
                                                by {{ $payment['payment_verified_by']['name'] ?? 'System' }}
                                            </small>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                            @if($payment['rejection_reason'])
                                                <small class="text-muted">{{ $payment['rejection_reason'] }}</small>
                                            @endif
                                        @endif
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($payment['createdAt'])->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-4">
                                <p class="text-muted">Tidak ada aktivitas pembayaran</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events with Most Pending Payments -->
        <div class="row">
            <div class="col-12 mb-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Event dengan Pembayaran Pending Terbanyak</h5>
                        <div class="dropdown">
                            <button class="btn text-body-secondary p-0" type="button" id="eventsPending"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="eventsPending">
                                <a class="dropdown-item" href="#">Lihat Semua</a>
                                <a class="dropdown-item" href="#">Export Data</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Pending Count</th>
                                        <th>Total Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dashboardData['charts']['eventsPendingPayments'] as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="icon-base bx bx-calendar"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $event['eventName'] }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ number_format($event['pendingCount']) }}</span>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($event['totalAmount']) }}</strong>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                Verifikasi
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted">Tidak ada event dengan pending payments</p>
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
    </div>

@endsection
@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Payment Statistics Chart (Daily Trend)
        const dailyData = @json($dashboardData['charts']['dailyPaymentStats']);
        
        // Process daily data for chart
        const last7Days = [];
        const today = new Date();
        for (let i = 6; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            last7Days.push(date.toISOString().split('T')[0]);
        }

        const approvedData = last7Days.map(date => {
            const found = dailyData.find(item => item._id.date === date && item._id.status === 'approved');
            return found ? found.count : 0;
        });

        const rejectedData = last7Days.map(date => {
            const found = dailyData.find(item => item._id.date === date && item._id.status === 'rejected');
            return found ? found.count : 0;
        });

        const chartLabels = last7Days.map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
        });

        new Chart(document.getElementById('paymentStatsChart'), {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Approved',
                        data: approvedData,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Rejected',
                        data: rejectedData,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Payment Status Pie Chart
        const statusData = @json($dashboardData['charts']['paymentStatusStats']);
        
        new Chart(document.getElementById('paymentStatusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.name),
                datasets: [{
                    data: statusData.map(item => item.value),
                    backgroundColor: statusData.map(item => item.color),
                    borderWidth: 2,
                    borderColor: '#fff'
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
                cutout: '60%'
            }
        });

        // Update numbers with animation effect
        function animateNumbers() {
            const elements = document.querySelectorAll('.card-title');
            elements.forEach(element => {
                if (element.textContent.match(/^\d/)) {
                    const finalValue = parseInt(element.textContent.replace(/,/g, ''));
                    let currentValue = 0;
                    const increment = finalValue / 50;
                    
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            element.textContent = finalValue.toLocaleString();
                            clearInterval(timer);
                        } else {
                            element.textContent = Math.floor(currentValue).toLocaleString();
                        }
                    }, 30);
                }
            });
        }

        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Add some delay for better visual effect
            setTimeout(animateNumbers, 500);
        });

        // Auto refresh data every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes

       

        // Format currency on hover for better readability
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
            new bootstrap.Tooltip(element);
        });
    </script>

@endsection