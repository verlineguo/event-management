@extends('committee.layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-1">Session Attendance</h5>
                <p class="text-muted mb-0">{{ $data['session']['title'] }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('committee.event.participants', $data['session']['event_id']['_id']) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Participants
                </a>
            </div>
        </div>

        <!-- Session Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="card-title">{{ $data['session']['title'] }}</h6>
                        <p class="text-muted mb-2">{{ $data['session']['description'] ?? 'No description' }}</p>
                        <div class="d-flex gap-3">
                            <small class="text-muted">
                                <i class="bx bx-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($data['session']['date'])->format('d M Y') }}
                            </small>
                            <small class="text-muted">
                                <i class="bx bx-time me-1"></i>
                                {{ $data['session']['start_time'] }} - {{ $data['session']['end_time'] }}
                            </small>
                            <small class="text-muted">
                                <i class="bx bx-map me-1"></i>
                                {{ $data['session']['location'] }}
                            </small>
                            <small class="text-muted">
                                <i class="bx bx-user me-1"></i>
                                {{ $data['session']['speaker'] }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex flex-column">
                            <span class="badge bg-{{ $data['session']['status'] === 'completed' ? 'success' : ($data['session']['status'] === 'ongoing' ? 'info' : ($data['session']['status'] === 'cancelled' ? 'danger' : 'warning')) }} mb-2">
                                {{ ucfirst($data['session']['status']) }}
                            </span>
                            <h4 class="mb-0">{{ $data['total_attendees'] }}</h4>
                            <small class="text-muted">Total Attendees</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Form -->
        @if($data['session']['status'] === 'completed')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bulk Upload Certificates</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('committee.attendance.bulk-upload-certificates', $data['session']['_id']) }}" 
                          method="POST" enctype="multipart/form-data" id="bulkUploadForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Certificates (Multiple files)</label>
                                    <input type="file" class="form-control" name="certificates[]" 
                                           accept=".pdf,.jpg,.jpeg,.png" multiple required>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple files. Max 2MB per file.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Participants</label>
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        <div class="mb-2">
                                            <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                            <label for="selectAll" class="form-check-label fw-bold">Select All</label>
                                        </div>
                                        <hr>
                                        @foreach($data['attendances'] as $attendance)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input participant-check" 
                                                       name="participant_ids[]" 
                                                       value="{{ $attendance['user_id']['_id'] }}"
                                                       id="participant_{{ $attendance['_id'] }}">
                                                <label class="form-check-label" for="participant_{{ $attendance['_id'] }}">
                                                    {{ $attendance['user_id']['name'] }}
                                                    <small class="text-muted d-block">{{ $attendance['user_id']['email'] }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            Select participants and upload their certificates in bulk. 
                            File names should match participant names or email addresses.
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i>Upload All Certificates
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Attendance Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attendance List</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Check-in Time</th>
                            <th>Method</th>
                            <th>Scanned By</th>
                            @if($data['session']['status'] === 'completed')
                                <th>Certificate</th>
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['attendances'] as $attendance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($attendance['user_id']['name'], 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $attendance['user_id']['name'] }}</strong>
                                            <br><small class="text-muted">{{ $attendance['user_id']['email'] }}</small>
                                            @if(isset($attendance['user_id']['phone']))
                                                <br><small class="text-muted">{{ $attendance['user_id']['phone'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($attendance['check_in_time'])->format('H:i:s') }}</strong>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($attendance['check_in_time'])->format('d M Y') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $attendance['attendance_method'] === 'qr_scan' ? 'success' : 'info' }}">
                                        {{ $attendance['attendance_method'] === 'qr_scan' ? 'QR Scan' : 'Manual' }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($attendance['scanned_by']))
                                        {{ $attendance['scanned_by']['name'] }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                @if($data['session']['status'] === 'completed')
                                    <td>
                                        @if(isset($attendance['certificate_path']))
                                            <a href="{{ asset('storage/' . $attendance['certificate_path']) }}" 
                                               target="_blank" class="text-success">
                                                <i class="bx bx-file-blank me-1"></i>Certificate
                                            </a>
                                        @else
                                            <span class="text-muted">Not uploaded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(!isset($attendance['certificate_path']))
                                                <button class="btn btn-sm btn-primary upload-certificate-btn" 
                                                        data-attendance-id="{{ $attendance['_id'] }}"
                                                        data-participant-id="{{ $attendance['user_id']['_id'] }}"
                                                        data-participant-name="{{ $attendance['user_id']['name'] }}"
                                                        title="Upload Certificate">
                                                    <i class="bx bx-upload"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-warning replace-certificate-btn" 
                                                        data-attendance-id="{{ $attendance['_id'] }}"
                                                        data-participant-id="{{ $attendance['user_id']['_id'] }}"
                                                        data-participant-name="{{ $attendance['user_id']['name'] }}"
                                                        title="Replace Certificate">
                                                    <i class="bx bx-refresh"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $data['session']['status'] === 'completed' ? '6' : '4' }}" class="text-center py-4">
                                    <i class="bx bx-user-x text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No attendance records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upload Single Certificate Modal -->
    @if($data['session']['status'] === 'completed')
        <div class="modal fade" id="uploadCertificateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Certificate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('committee.attendance.upload-certificate', $data['session']['_id']) }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Participant</label>
                                <input type="text" class="form-control" id="participantName" readonly>
                                <input type="hidden" id="participantId" name="participant_id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Certificate File</label>
                                <input type="file" class="form-control" name="certificate" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Supported formats: PDF, JPG, PNG (Max: 2MB)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-upload me-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select All functionality for bulk upload
    $('#selectAll').on('change', function() {
        $('.participant-check').prop('checked', $(this).is(':checked'));
    });

    // Upload certificate modal
    $('.upload-certificate-btn, .replace-certificate-btn').on('click', function() {
        const participantId = $(this).data('participant-id');
        const participantName = $(this).data('participant-name');
        
        $('#participantId').val(participantId);
        $('#participantName').val(participantName);
        $('#uploadCertificateModal').modal('show');
    });

    // Form validation before submit
    $('#bulkUploadForm').on('submit', function(e) {
        const selectedParticipants = $('.participant-check:checked').length;
        const selectedFiles = $('input[name="certificates[]"]')[0].files.length;
        
        if (selectedParticipants === 0) {
            e.preventDefault();
            alert('Please select at least one participant');
            return false;
        }
        
        if (selectedFiles === 0) {
            e.preventDefault();
            alert('Please select certificate files to upload');
            return false;
        }
        
        // Optional: Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...');
    });

    // Success/Error messages with SweetAlert
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Error!',
            text: '{{ session('error') }}',
            icon: 'error'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            title: 'Validation Error!',
            html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
            icon: 'error'
        });
    @endif
});
</script>
@endsection