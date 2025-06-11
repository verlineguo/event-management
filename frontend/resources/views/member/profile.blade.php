@extends('member.layouts.app')

@section('title', 'Profile' . (Auth::user()->name ?? 'Guest'))

@push('styles')
    <style>
        .profile-card-content {
            padding-top: 5rem;
        }
        .avatar-container {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
    </style>
@endpush

@section('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    {{-- Success and Error Alerts --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        </script>
    @endif
    
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    showConfirmButton: true
                });
            });
        </script>
    @endif
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="card-title mb-0 text-center">Profil Mahasiswa</h4>
                </div>
                
                <div class="card-body p-0">
                    <ul class="nav nav-pills nav-fill p-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center justify-content-center" 
                                    id="view-profile-tab" data-bs-toggle="tab" data-bs-target="#view-profile" 
                                    type="button" role="tab" aria-selected="true">
                                <i class="fas fa-user-circle me-2"></i> Lihat Profil
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center" 
                                    id="edit-profile-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" 
                                    type="button" role="tab" aria-selected="false">
                                <i class="fas fa-edit me-2"></i> Edit Profil
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center" 
                                    id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" 
                                    type="button" role="tab" aria-selected="false">
                                <i class="fas fa-key me-2"></i> Ganti Password
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-4" id="profileTabsContent">
                        <!-- Lihat Profil -->
                        <div class="tab-pane fade show active" id="view-profile" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4 mb-4 mb-md-0">
                                    <div class="text-center">
                                        <div class="position-relative d-inline-block mb-3">
                                            @if($user->profile)
                                                <img src="{{ asset('storage/' . $user->profile) }}?v={{ time() }}" 
                                                    alt="Profile Picture" 
                                                    class="img-thumbnail rounded-circle" 
                                                    width="180" height="180" 
                                                    style="object-fit: cover; aspect-ratio: 1 / 1;">
                                            @else
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                    style="width: 180px; height: 180px;">
                                                    <i class="fas fa-user-circle fa-5x text-secondary"></i>
                                                </div>
                                            @endif
                                            
                                            <div class="badge bg-success position-absolute bottom-0 end-0 p-2 rounded-circle">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </div>
                                        
                                        <h5 class="mb-1">{{ $user->name }}</h5>
                                        <p class="text-muted">{{ $user->role->name }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card h-100 border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <i class="fas fa-id-card me-2"></i>Nomor Induk
                                                    </h6>
                                                    <p class="card-text fw-bold">{{ $user->nomor_induk }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card h-100 border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <i class="fas fa-envelope me-2"></i>Email
                                                    </h6>
                                                    <p class="card-text fw-bold">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card h-100 border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <i class="fas fa-phone me-2"></i>Telepon
                                                    </h6>
                                                    <p class="card-text fw-bold">
                                                        {{ $user->phone ?: 'Belum diisi' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card h-100 border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <i class="fas fa-user-tag me-2"></i>Role
                                                    </h6>
                                                    <p class="card-text">
                                                        <span class="badge bg-info">{{ $user->role->name }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="card h-100 border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        <i class="fas fa-map-marker-alt me-2"></i>Alamat
                                                    </h6>
                                                    <p class="card-text">
                                                        {{ $user->address ?: 'Belum diisi' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
@endsection