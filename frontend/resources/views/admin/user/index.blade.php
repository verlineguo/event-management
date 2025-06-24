@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="d-flex justify-content-between mb-5">
            <h5 class="fw-bold mb-4">
                User Management
            </h5>
            <a href="{{ route('admin.user.create') }}" class="btn btn-primary">Create</a>
        </div>

        <div class="card">
            
            <h5 class="card-header">Table Users</h5>
            <div class="ms-6">
                <label for="role-filter" class="form-label">Filter by Role:</label>
                <select id="role-filter" class="form-select" style="width: 200px; display: inline-block;">
                    <option value="">All</option>
                    @foreach ($roles as $role)
                        @if ($role['name'] !== 'guest')
                            <option value="{{ $role['name'] }}">{{ ucfirst($role['name']) }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="text-nowrap p-6">
                <table id="users-table" class="p-0 table table-responsive">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user['name'] }}</td>
                                <td>{{ $user['email'] }}</td>
                                <td>{{ $user['role_id']['name'] ?? '-' }}</td>
                                <td>{{ $user['gender'] ?? '-' }}</td>
                                <td>{{ $user['phone'] ?? '-' }}</td>
                                <td>
                                    @if (isset($user['status']))
                                        {{ $user['status'] ? 'Active' : 'Inactive' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.user.edit', $user['_id']) }}"
                                        class="btn btn-sm btn-info"><i class="bx bx-edit" style="font-size: 18px"></i></a>
                                    <form action="{{ route('admin.user.destroy', $user['_id']) }}" method="POST"
                                        style="display: inline-block" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $user['_id'] }}">
                                            <i class="bx bx-trash" style="font-size: 18px"></i>
                                        </button>
                                    </form>
                                </td>

                                {{-- <td>
                                    @if (($user['role_id']['name'] ?? '') !== 'member')
                                        <a href="{{ route('admin.user.edit', $user['_id']) }}"
                                            class="btn btn-sm btn-info"><i class="bx bx-edit"
                                                style="font-size: 18px"></i></a>
                                    @endif
                                    <form action="{{ route('admin.user.destroy', $user['_id']) }}" method="POST"
                                        style="display: inline-block" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $user['_id'] }}">
                                            <i class="bx bx-trash" style="font-size: 18px"></i>
                                        </button>
                                    </form>
                                </td> --}}
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
            var table = $('#users-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 6 // Sesuaikan dengan kolom Actions (indeks dimulai dari 0)
                }]
            });


            $('#role-filter').on('change', function() {
                table.column(2).search(this.value).draw(); // Kolom ke-2 adalah Role
            });

            // SweetAlert for delete confirmation
            $('.delete-btn').on('click', function() {
                const form = $(this).closest('form');
                const userId = $(this).data('id');

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

            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            @if (session('error'))
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
