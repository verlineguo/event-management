@extends('admin.layouts.app')
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
                                <h5 class="card-title text-primary mb-3">Selamat Datang Admin! 🎉</h5>
                                <p class="mb-6">
                                    Dashboard admin untuk mengelola user, role, dan kategori event.<br />
                                    Total {{ number_format($dashboardData['stats']['totalUsers']) }} pengguna terdaftar dalam sistem.
                                </p>
                                <a href="{{ route('admin.role.index') }}" class="btn btn-sm btn-outline-primary me-2">Kelola Role</a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">Kelola User</a>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-6">
                                <img src="../assets/img/illustrations/man-with-laptop.png" height="175"
                                    alt="Admin Dashboard" />
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
                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="users"
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
                                <p class="mb-1">Total Users</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalUsers']) }}</h4>
                                <small class="text-{{ $dashboardData['stats']['userGrowthPercentage'] >= 0 ? 'success' : 'danger' }} fw-medium">
                                    <i class="icon-base bx bx-{{ $dashboardData['stats']['userGrowthPercentage'] >= 0 ? 'up' : 'down' }}-arrow-alt"></i>
                                    {{ number_format(abs($dashboardData['stats']['userGrowthPercentage']), 1) }}%
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="active users"
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
                                <p class="mb-1">Active Users</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['activeUsers']) }}</h4>
                                <small class="text-info fw-medium">
                                    {{ $dashboardData['stats']['totalUsers'] > 0 ? number_format(($dashboardData['stats']['activeUsers'] / $dashboardData['stats']['totalUsers']) * 100, 1) : 0 }}% dari total
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Statistics Chart -->
            <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
                <div class="card">
                    <div class="row row-bordered g-0">
                        <div class="col-lg-8">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">Statistik Pengguna</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="userRegistrationChart" height="300"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                                <div class="text-center mb-6">
                                    <h6 class="mb-2">User Status</h6>
                                    <div class="d-flex justify-content-center">
                                        <canvas id="userStatusChart" width="150" height="150"></canvas>
                                    </div>
                                </div>

                                <div class="d-flex gap-11 justify-content-between w-100">
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-success">
                                                <i class="icon-base bx bx-user-check icon-lg text-success"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Active</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['activeUsers']) }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-2 bg-label-danger">
                                                <i class="icon-base bx bx-user-x icon-lg text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <small>Inactive</small>
                                            <h6 class="mb-0">{{ number_format($dashboardData['stats']['inactiveUsers']) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Management Stats -->
            <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
                <div class="row">
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/paypal.png" alt="roles"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                            <a class="dropdown-item" href="{{ route('admin.role.index') }}">Kelola Role</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Total Roles</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalRoles']) }}</h4>
                                <small class="text-success fw-medium">
                                    {{ number_format($dashboardData['stats']['activeRoles']) }} aktif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0">
                                        <img src="../assets/img/icons/unicons/cc-primary.png" alt="categories"
                                            class="rounded" />
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                            <a class="dropdown-item" href="#">Kelola Kategori</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-1">Categories</p>
                                <h4 class="card-title mb-3">{{ number_format($dashboardData['stats']['totalCategories']) }}</h4>
                                <small class="text-success fw-medium">
                                    {{ number_format($dashboardData['stats']['activeCategories']) }} aktif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- User by Role Distribution -->
            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Distribusi User by Role</h5>
                            <p class="card-subtitle">{{ number_format($dashboardData['stats']['totalUsers']) }} Total Users</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <h3 class="mb-1">{{ number_format($dashboardData['stats']['totalUsers']) }}</h3>
                                <small>Total Users</small>
                            </div>
                            <div>
                                <canvas id="roleDistributionChart" width="100" height="100"></canvas>
                            </div>
                        </div>
                        <ul class="p-0 m-0">
                            @foreach($dashboardData['charts']['usersByRole'] as $roleData)
                            <li class="d-flex align-items-center mb-5">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="icon-base bx bx-user"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $roleData['_id'] ?? 'No Role' }}</h6>
                                        <small>Role</small>
                                    </div>
                                    <div class="user-progress">
                                        <h6 class="mb-0">{{ number_format($roleData['count']) }}</h6>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-md-6 col-lg-8 order-1 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Pengguna Terbaru</h5>
                        <div class="dropdown">
                            <button class="btn text-body-secondary p-0" type="button" id="recentUsers"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentUsers">
                                <a class="dropdown-item" href="#">7 Hari Terakhir</a>
                                <a class="dropdown-item" href="#">30 Hari Terakhir</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <ul class="p-0 m-0">
                            @forelse($dashboardData['recentActivity']['recentUsers'] as $user)
                            <li class="d-flex align-items-center mb-6">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $user['status'] ? 'success' : 'secondary' }}">
                                        {{ strtoupper(substr($user['name'], 0, 2)) }}
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="fw-normal mb-0">{{ $user['name'] }}</h6>
                                        <small class="d-block text-muted">{{ $user['email'] }}</small>
                                        <small class="d-block">{{ $user['role_id']['name'] ?? 'No Role' }}</small>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2 flex-column">
                                        <span class="badge bg-{{ $user['status'] ? 'success' : 'secondary' }}">
                                            {{ $user['status'] ? 'Active' : 'Inactive' }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($user['createdAt'])->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-4">
                                <p class="text-muted">Tidak ada pengguna baru dalam 7 hari terakhir</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // User Registration Chart
        const monthlyData = @json($dashboardData['charts']['monthlyUserRegistrations']);
        const labels = monthlyData.map(item => {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[item._id.month - 1] + ' ' + item._id.year;
        });
        const data = monthlyData.map(item => item.count);

        new Chart(document.getElementById('userRegistrationChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'User Registrations',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // User Status Chart
        new Chart(document.getElementById('userStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [{{ $dashboardData['stats']['activeUsers'] }}, {{ $dashboardData['stats']['inactiveUsers'] }}],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Role Distribution Chart
        const roleData = @json($dashboardData['charts']['usersByRole']);
        new Chart(document.getElementById('roleDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: roleData.map(item => item._id || 'No Role'),
                datasets: [{
                    data: roleData.map(item => item.count),
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endsection