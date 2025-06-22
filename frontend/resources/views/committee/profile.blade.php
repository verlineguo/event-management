@extends('committee.layouts.app')

@section('title', 'Profile - ' . (session('user')['name'] ?? 'Guest'))

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
    <style>
        .profile-card-content {
            padding-top: 5rem;
        }
        .profile-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .profile-info-item:last-child {
            border-bottom: none;
        }
        .profile-info-label {
            font-weight: 600;
            color: #374151;
            flex: 0 0 120px;
        }
        .profile-info-value {
            color: #6b7280;
            flex: 1;
        }
    </style>
@endsection

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
            let errorMessages = [];
            @foreach ($errors->all() as $error)
                errorMessages.push("{{ $error }}");
            @endforeach
            
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                html: errorMessages.join('<br>'),
                showConfirmButton: true
            });
            
            // Show appropriate tab if there are validation errors  
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
    <div class="bg-white rounded-lg shadow-md overflow-hidden container-xxl flex-grow-1 container-p-y">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-user-circle me-2"></i>My Profile
            </h1>
            <p class="text-gray-600">Manage your personal information and account settings</p>
        </div>
        
        <hr class="border-gray-200">

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
                                
                                @php
                                    $user = session('user') ?? $user ?? [];
                                @endphp
                                
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Full Name:</div>
                                    <div class="profile-info-value">{{ $user['name'] ?? 'N/A' }}</div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Email:</div>
                                    <div class="profile-info-value">
                                        <a href="mailto:{{ $user['email'] ?? '' }}" class="text-blue-600">
                                            {{ $user['email'] ?? 'N/A' }}
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Gender:</div>
                                    <div class="profile-info-value">
                                        {{ $user['gender'] ? ucfirst($user['gender']) : 'Not specified' }}
                                    </div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Phone:</div>
                                    <div class="profile-info-value">
                                        {{ $user['phone'] ?? 'Not specified' }}
                                    </div>
                                </div>
                                
                                @if(isset($user['role_id']['name']))
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Role:</div>
                                    <div class="profile-info-value">
                                        <span class="badge bg-primary">{{ ucfirst($user['role_id']['name']) }}</span>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Status:</div>
                                    <div class="profile-info-value">
                                        @if(isset($user['status']))
                                            <span class="badge {{ $user['status'] ? 'bg-success' : 'bg-danger' }}">
                                                {{ $user['status'] ? 'Active' : 'Inactive' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if(isset($user['createdAt']))
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Member Since:</div>
                                    <div class="profile-info-value">
                                        {{ \Carbon\Carbon::parse($user['createdAt'])->format('F d, Y') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Profile Tab --}}
            <div class="tab-pane fade" id="edit-profile" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 text-center">
                                    <i class="fas fa-edit me-2"></i> Edit Your Profile
                                </h5>
                                <form action="{{ route('committee.profile.update') }}" method="POST" id="profileForm">
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                            id="name" name="name" value="{{ old('name', $user['name'] ?? '') }}" 
                                            placeholder="Full Name" required>
                                        <label for="name">Full Name</label>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control bg-light" id="email" 
                                            value="{{ $user['email'] ?? '' }}" placeholder="Email" readonly disabled>
                                        <label for="email">Email (Cannot be changed here)</label>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Contact administrator to change your email address
                                        </div>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ (old('gender', $user['gender'] ?? '') == 'male') ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ (old('gender', $user['gender'] ?? '') == 'female') ? 'selected' : '' }}>Female</option>
                                        </select>
                                        <label for="gender">Gender</label>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-4">
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                            id="phone" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}" 
                                            placeholder="Phone Number">
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

                                <form action="{{ route('committee.profile.update-password') }}" method="POST" id="passwordForm">
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
                                                id="password" name="password" required placeholder="New Password" minlength="6">
                                        <label for="password">New Password</label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Password must be at least 6 characters long
                                        </div>
                                    </div>
                                    
                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                                id="password_confirmation" name="password_confirmation" required placeholder="Confirm New Password">
                                        <label for="password_confirmation">Confirm New Password</label>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
        // Profile form submission with SweetAlert confirmation
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
                        // Show loading
                        Swal.fire({
                            title: 'Saving...',
                            html: 'Please wait while we update your profile',
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

        // Password form submission with SweetAlert confirmation
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                // Validate password confirmation
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('password_confirmation').value;
                
                if (password !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'New password and confirmation password do not match!',
                        showConfirmButton: true
                    });
                    return;
                }
                
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
                        // Show loading
                        Swal.fire({
                            title: 'Updating Password...',
                            html: 'Please wait while we update your password',
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
        
        // Handle URL hash to show specific tab
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
        
        // Real-time password validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        
        if (passwordInput && confirmPasswordInput) {
            function validatePasswords() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                    confirmPasswordInput.classList.add('is-invalid');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                    confirmPasswordInput.classList.remove('is-invalid');
                }
            }
            
            passwordInput.addEventListener('input', validatePasswords);
            confirmPasswordInput.addEventListener('input', validatePasswords);
        }
    });
</script>
@endsection