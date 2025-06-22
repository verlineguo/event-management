@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between mb-5">
            <h5 class="fw-bold mb-4">Role Management</h5>
            <a href="{{ route('admin.role.create') }}" class="btn btn-primary">Create</a>
        </div>

        <!-- Role Table Card -->
        <div class="card">
            <h5 class="card-header">Table Categories</h5>
            <div class="text-nowrap p-6">
                <table id="categories-table" class="p-0 table table-responsive">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role['name'] }}</td>

                                <td>
                                    @if (isset($role['status']))
                                        {{ $role['status'] ? 'Active' : 'Inactive' }}
                                    @else
                                        -
                                    @endif
                                </td>

                     
                                <td>
                                    <a href="{{ route('admin.role.edit', $role['_id']) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="bx bx-edit" style="font-size: 18px"></i>
                                    </a>
                                    <form action="{{ route('admin.role.destroy', $role['_id']) }}"
                                          method="POST"
                                          style="display: inline-block"
                                          class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                data-id="{{ $role['_id'] }}">
                                            <i class="bx bx-trash" style="font-size: 18px"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#categories-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 2// Kolom Actions
                }]
            });

            $('.delete-btn').on('click', function() {
                const form = $(this).closest('form');
                const roleId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
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
