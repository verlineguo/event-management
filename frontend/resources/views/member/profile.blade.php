@extends('member.layouts.app')
    @if(Auth::check())
        <script>console.log('User is logged in:', @json(Auth::user()->toArray()));</script>
    @else
        <script>console.log('User is NOT logged in.');</script>
    @endif

@section('title', 'Profile' . (Auth::user()->name ?? 'Guest'))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
    <style>
        .profile-card-content {
            padding-top: 5rem;
        }
    </style>
@endpush

@section('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

{{-- Success and Error Alerts --}}
@if(session('status') === 'password-updated')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your password has been updated successfully!',
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
@elseif(session('status') === 'profile-updated')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your profile has been updated successfully!',
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
@elseif(session('success'))
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
@elseif($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                text: 'Please check the form fields for errors.',
                showConfirmButton: true
            });
            const profileErrors = document.querySelectorAll('#edit-profile .is-invalid');
            const passwordErrors = document.querySelectorAll('#change-password .is-invalid');
            
            if (profileErrors.length > 0) {
                const triggerEl = document.querySelector('button[data-bs-target="#edit-profile"]');
                if(triggerEl) {
                    const tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            } else if (passwordErrors.length > 0) {
                const triggerEl = document.querySelector('button[data-bs-target="#change-password"]');
                if(triggerEl) {
                    const tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            }
        });
    </script>
@endif

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-[10px]">
    <div class="w-full bg-white rounded-lg shadow-md overflow-hidden">
        <hr class="my-8 border-gray-200">

        {{-- Navigation Tabs --}}
        <ul class="nav nav-pills nav-fill p-3" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center justify-content-center" 
                        id="view-profile-tab" data-bs-toggle="tab" data-bs-target="#view-profile" 
                        type="button" role="tab" aria-selected="true">
                    <i class="fas fa-user-circle me-2"></i> View Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center" 
                        id="edit-profile-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" 
                        type="button" role="tab" aria-selected="false">
                    <i class="fas fa-edit me-2"></i> Edit Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center" 
                        id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" 
                        type="button" role="tab" aria-selected="false">
                    <i class="fas fa-key me-2"></i> Change Password
                </button>
            </li>
        </ul>

        <div class="tab-content p-4" id="profileTabsContent">
            {{-- View Profile Tab --}}
            <div class="tab-pane fade show active" id="view-profile" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 text-center">
                                    <i class="fas fa-user-circle me-2"></i> Your Profile Details
                                </h5>
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-600">Full Name</p>
                                    {{-- Mengambil 'name' dari objek user yang sedang login --}}
                                    <p class="text-base text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-600">Email</p>
                                    {{-- Mengambil 'email' dari objek user yang sedang login --}}
                                    <p class="text-base text-blue-600">
                                        <a href="mailto:{{ Auth::user()->email ?? '' }}">{{ Auth::user()->email ?? 'N/A' }}</a>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-600">Gender</p>
                                    {{-- Mengambil 'gender' dari objek user yang sedang login --}}
                                    <p class="text-base text-gray-800">{{ (Auth::user()->gender ?? null) ? ucfirst(Auth::user()->gender) : 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-600">Phone Number</p>
                                    {{-- Mengambil 'phone' dari objek user yang sedang login (sesuai skema MongoDB Anda) --}}
                                    <p class="text-base text-gray-800">{{ Auth::user()->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NEW: Edit Profile Tab --}}
            <div class="tab-pane fade" id="edit-profile" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 text-center">
                                    <i class="fas fa-edit me-2"></i> Edit Your Profile
                                </h5>
                                <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                            id="name" name="name" value="{{ old('name', Auth::user()->name ?? '') }}" placeholder="Full Name" required>
                                        <label for="name">Full Name</label>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        {{-- Email biasanya tidak diubah langsung di sini tanpa proses verifikasi --}}
                                        <input type="email" class="form-control" id="email" value="{{ Auth::user()->email ?? '' }}" placeholder="Email" disabled>
                                        <label for="email">Email (Tidak dapat diubah di sini)</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ (old('gender', Auth::user()->gender ?? '') == 'male') ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ (old('gender', Auth::user()->gender ?? '') == 'female') ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ (old('gender', Auth::user()->gender ?? '') == 'other') ? 'selected' : '' }}>Other</option>
                                        </select>
                                        <label for="gender">Gender</label>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-4">
                                        {{-- Menggunakan 'phone' sebagai nama input --}}
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                            id="phone" name="phone" value="{{ old('phone', Auth::user()->phone ?? '') }}" placeholder="Phone Number">
                                        <label for="phone">Phone Number</label>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Profile Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Change Password Tab --}}
            <div class="tab-pane fade" id="change-password" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 text-center">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </h5>

                                <form action="{{ route('profile.password.update') }}" method="POST" id="passwordForm">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                                id="current_password" name="current_password" required placeholder="Current Password">
                                        <label for="current_password">Current Password</label>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                id="password" name="password" required placeholder="New Password">
                                        <label for="password">New Password</label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control" 
                                                id="password_confirmation" name="password_confirmation" required placeholder="Confirm New Password">
                                        <label for="password_confirmation">Confirm New Password</label>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save New Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault(); 
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure you want to save your profile changes?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Save Changes',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Saving...',
                            html: 'Please wait a moment',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        profileForm.submit(); 
                    }
                });
            });
        }

        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault(); 
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure you want to change your password?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Change Password',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Saving...',
                            html: 'Please wait a moment',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        passwordForm.submit(); 
                    }
                });
            });
        }
        
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
            if(triggerEl) {
                const activeTab = document.querySelector('#profileTabs button.nav-link.active');
                if (activeTab) {
                    new bootstrap.Tab(activeTab).hide();
                }
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }
    });
</script>
@endsection