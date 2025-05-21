@extends('admin.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('admin.user.index') }}">User Management </a>/</span> Edit User
        </h5>
        
        <div class="card">
            <h5 class="card-header">Edit User</h5>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form id="editUserForm" action="{{ route('admin.user.update', $user['_id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $user['name']) }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ old('email', $user['email']) }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password"
                            placeholder="Leave blank to keep current password" minlength="6">
                        <div class="form-text">Leave blank to keep current password (min. 6 characters)</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            placeholder="Confirm new password">
                        <div class="form-text">Must match the new password</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select" id="role_id" name="role_id" disabled>
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                @if (!in_array($role['name'], ['guest', 'member']))
                                    <option value="{{ $role['_id'] }}"
                                        {{ old('role_id', isset($user['role_id']['_id']) ? $user['role_id']['_id'] : '') == $role['_id'] ? 'selected' : '' }}>
                                        {{ ucfirst($role['name']) }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $user['gender'] ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user['gender'] ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                            value="{{ old('phone', $user['phone'] ?? '') }}" placeholder="Enter phone number">
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Select Status</option>
                            <option value="1" {{ old('status', $user['status'] ?? '') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $user['status'] ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update
                        </button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tangkap form submit
            const form = document.getElementById('editUserForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to edit a new user!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, edit it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika user mengkonfirmasi, submit form
                        form.submit();
                    }
                });
            });

            // Cek jika ada pesan sukses dari session
            @if(session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            // Cek jika ada pesan error dari session
            @if(session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
@endsection
