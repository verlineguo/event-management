@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="d-flex justify-content-end mb-5">
            <a href="{{ route('admin.user.create') }}" class="btn btn-primary">Create</a>
        </div>

        <div class="card">
            <h5 class="card-header">Table Users</h5>
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
                                        class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ route('admin.user.destroy', $user['_id']) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
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
            $('#users-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 5
                }]
            });
        });
    </script>
@endsection
