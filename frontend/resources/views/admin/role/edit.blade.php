@extends('admin.layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h5 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light"><a href="{{ route('admin.role.index') }}">Event Role </a>/</span> Edit Role
    </h5>

    <div class="card">
        <h5 class="card-header">Edit Event Role</h5>
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

            <form id="editRoleForm" action="{{ route('admin.role.update', $role['_id']) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="form-label">Role Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ old('name', $role['name']) }}" required>
                </div>

                <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Select Status</option>
                            <option value="1" {{ old('status', $category['status'] ?? '') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $category['status'] ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Update
                    </button>
                    <a href="{{ route('admin.category.index') }}" class="btn btn-secondary">
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
        const form = document.getElementById('editCategoryForm');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to update the category!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        @if(session('success'))
            Swal.fire({
                title: 'Success!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

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
