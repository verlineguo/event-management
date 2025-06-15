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

        <!-- Attendance Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance List</h5>
                @if($data['session']['status'] === 'completed')
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="showBulkUploadModal()">
                            <i class="bx bx-upload me-1"></i>Bulk Upload Certificates
                        </button>
                        <button class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportAttendance()">
                                <i class="bx bx-download me-1"></i>Export Attendance
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showBulkActionsModal()">
                                <i class="bx bx-cog me-1"></i>Bulk Actions
                            </a></li>
                        </ul>
                    </div>
                @endif
            </div>
            <div class="table-responsive text-nowrap">
                <table id="attendance-table" class="table">
                    <thead>
                        <tr>
                            @if($data['session']['status'] === 'completed')
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                            @endif
                            <th>Participant</th>
                            <th>Check-in Time</th>
                            <th>Method</th>
                            <th>Scanned By</th>
                            <th>Certificate Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['attendances'] as $attendance)
                            <tr>
                                @if($data['session']['status'] === 'completed')
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input participant-checkbox" type="checkbox" 
                                                   value="{{ $attendance['user_id']['_id'] }}" 
                                                   data-name="{{ $attendance['user_id']['name'] }}"
                                                   data-has-certificate="{{ isset($attendance['certificate_path']) ? 'true' : 'false' }}">
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $attendance['user_id']['name'] }}</strong>
                                            <br><small class="text-muted">{{ $attendance['user_id']['email'] }}</small>
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
                                    {{ $attendance['scanned_by']['name'] ?? 'System' }}
                                </td>
                                <td>
                                    @if(isset($attendance['certificate_path']) && $attendance['certificate_path'])
                                        <span class="badge bg-success">Certificate Issued</span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $attendance['certificate_number'] ?? 'N/A' }}
                                        </small>
                                        @if(isset($attendance['certificate_issued_date']))
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($attendance['certificate_issued_date'])->format('d M Y') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Not Issued</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($data['session']['status'] === 'completed')
                                            @if(isset($attendance['certificate_path']) && $attendance['certificate_path'])
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="downloadCertificate('{{ $attendance['user_id']['_id'] }}')"
                                                        title="Download Certificate">
                                                    <i class="bx bx-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="showUploadModal('{{ $attendance['user_id']['_id'] }}', '{{ $attendance['user_id']['name'] }}', true)"
                                                        title="Replace Certificate">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="showRevokeModal('{{ $attendance['user_id']['_id'] }}', '{{ $attendance['user_id']['name'] }}')"
                                                        title="Revoke Certificate">
                                                    <i class="bx bx-x-circle"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="showUploadModal('{{ $attendance['user_id']['_id'] }}', '{{ $attendance['user_id']['name'] }}', false)"
                                                        title="Upload Certificate">
                                                    <i class="bx bx-upload"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $data['session']['status'] === 'completed' ? '7' : '6' }}" class="text-center py-4">
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
                                <label class="form-label">Certificate File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="certificate" 
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Supported formats: PDF, JPG, PNG (Max: 2MB)</small>
                            </div>
                            <div class="mb-3" id="currentCertificateInfo" style="display: none;">
                                <label class="form-label text-warning">Current Certificate</label>
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <span id="currentCertificateText">A certificate already exists for this participant.</span>
                                    <br><small class="text-muted">Uploading a new file will replace the existing certificate.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                                <i class="bx bx-upload me-1"></i><span id="uploadBtnText">Upload</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Certificates Modal -->
        <div class="modal fade" id="bulkUploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Upload Certificates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('committee.attendance.bulk-upload-certificates', $data['session']['_id']) }}" 
                          method="POST" enctype="multipart/form-data" id="bulkUploadForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Instructions:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Select participants from the list below</li>
                                    <li>Upload certificate files (PDF, JPG, PNG max 2MB each)</li>
                                    <li>Files will be assigned to participants in the order selected</li>
                                    <li>If you upload fewer files than selected participants, files will be distributed cyclically</li>
                                </ul>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Select Participants</h6>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div class="mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllParticipants()">
                                                Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllParticipants()">
                                                Clear All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="selectWithoutCertificates()">
                                                Select Without Certificates
                                            </button>
                                        </div>
                                        <div id="participantsList">
                                            @foreach($data['attendances'] as $attendance)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input bulk-participant-checkbox" type="checkbox" 
                                                           value="{{ $attendance['user_id']['_id'] }}" 
                                                           id="bulk_{{ $attendance['user_id']['_id'] }}"
                                                           name="participant_ids[]"
                                                           data-has-certificate="{{ isset($attendance['certificate_path']) ? 'true' : 'false' }}">
                                                    <label class="form-check-label" for="bulk_{{ $attendance['user_id']['_id'] }}">
                                                        <strong>{{ $attendance['user_id']['name'] }}</strong>
                                                        <br><small class="text-muted">{{ $attendance['user_id']['email'] }}</small>
                                                        @if(isset($attendance['certificate_path']) && $attendance['certificate_path'])
                                                            <span class="badge bg-success ms-2">Has Certificate</span>
                                                        @else
                                                            <span class="badge bg-warning ms-2">No Certificate</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <small class="text-muted">Selected: <span id="selectedCount">0</span> participants</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Upload Certificate Files</h6>
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="certificates[]" 
                                               accept=".pdf,.jpg,.jpeg,.png" multiple required id="bulkCertificateFiles">
                                        <small class="text-muted">Select multiple files (PDF, JPG, PNG max 2MB each)</small>
                                    </div>
                                    
                                    <div id="filePreview" class="border rounded p-3" style="max-height: 250px; overflow-y: auto; display: none;">
                                        <h6 class="text-muted">Selected Files:</h6>
                                        <div id="fileList"></div>
                                    </div>
                                    <small class="text-muted">Files selected: <span id="fileCount">0</span></small>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-warning" id="uploadWarning" style="display: none;">
                                    <i class="bx bx-exclamation-triangle me-1"></i>
                                    <span id="warningMessage"></span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="bulkUploadSubmitBtn" disabled>
                                <i class="bx bx-upload me-1"></i>Upload Certificates
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Revoke Certificate Modal -->
        <div class="modal fade" id="revokeCertificateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bx bx-exclamation-triangle me-1"></i>Revoke Certificate
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="revokeCertificateForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Warning!</strong> This action will revoke the certificate and cannot be undone. 
                                The participant will no longer have access to their certificate.
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Participant</label>
                                <input type="text" class="form-control" id="revokeParticipantName" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Revocation <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" required
                                          placeholder="Enter reason for revoking certificate (required)..."></textarea>
                                <small class="text-muted">This reason will be logged for audit purposes.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="revokeSubmitBtn">
                                <i class="bx bx-x-circle me-1"></i>Revoke Certificate
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
    // Initialize DataTable if you're using it
    // $('#attendance-table').DataTable({
    //     "pageLength": 25,
    //     "order": [[ 2, "desc" ]]
    // });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.participant-checkbox').prop('checked', this.checked);
    });

    $('.participant-checkbox').on('change', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        }
        
        if ($('.participant-checkbox:checked').length === $('.participant-checkbox').length) {
            $('#selectAll').prop('checked', true);
        }
    });

    // Bulk upload participants selection
    $('.bulk-participant-checkbox').on('change', function() {
        updateSelectedCount();
        validateBulkUpload();
    });

    // File input change handler
    $('#bulkCertificateFiles').on('change', function() {
        updateFilePreview();
        validateBulkUpload();
    });

    // Form validation and loading states
    $('#uploadCertificateModal form').on('submit', function(e) {
        const fileInput = $('input[name="certificate"]')[0];
        const submitBtn = $('#uploadSubmitBtn');
        
        if (fileInput.files.length === 0) {
            e.preventDefault();
            showAlert('error', 'Please select a certificate file');
            return false;
        }
        
        if (!validateFileSize(fileInput.files[0])) {
            e.preventDefault();
            return false;
        }
        
        showLoadingState(submitBtn, 'Uploading...');
    });

    // Bulk upload form validation
    $('#bulkUploadForm').on('submit', function(e) {
        const selectedParticipants = $('.bulk-participant-checkbox:checked').length;
        const selectedFiles = $('#bulkCertificateFiles')[0].files.length;
        
        if (selectedParticipants === 0) {
            e.preventDefault();
            showAlert('error', 'Please select at least one participant');
            return false;
        }
        
        if (selectedFiles === 0) {
            e.preventDefault();
            showAlert('error', 'Please select at least one certificate file');
            return false;
        }

        // Validate file sizes
        const files = $('#bulkCertificateFiles')[0].files;
        for (let i = 0; i < files.length; i++) {
            if (!validateFileSize(files[i])) {
                e.preventDefault();
                return false;
            }
        }
        
        showLoadingState('#bulkUploadSubmitBtn', 'Uploading...');
    });

    // Revoke form validation
    $('#revokeCertificateForm').on('submit', function(e) {
        const reason = $('textarea[name="reason"]').val().trim();
        
        if (reason === '') {
            e.preventDefault();
            showAlert('error', 'Please provide a reason for revocation');
            return false;
        }
        
        showLoadingState('#revokeSubmitBtn', 'Revoking...');
    });

    // Reset modals when closed
    $('#uploadCertificateModal').on('hidden.bs.modal', resetUploadModal);
    $('#bulkUploadModal').on('hidden.bs.modal', resetBulkUploadModal);
    $('#revokeCertificateModal').on('hidden.bs.modal', resetRevokeModal);

    // Success/Error messages
    @if(session('success'))
        showAlert('success', '{{ session('success') }}');
    @endif

    @if(session('warning'))
        showAlert('warning', '{{ session('warning') }}');
    @endif

    @if(session('error'))
        showAlert('error', '{{ session('error') }}');
    @endif

    @if($errors->any())
        showAlert('error', '@foreach($errors->all() as $error){{ $error }}@if(!$loop->last)<br>@endif @endforeach');
    @endif
});

// Utility functions
function updateSelectedCount() {
    const count = $('.bulk-participant-checkbox:checked').length;
    $('#selectedCount').text(count);
}

function updateFilePreview() {
    const files = $('#bulkCertificateFiles')[0].files;
    $('#fileCount').text(files.length);
    
    if (files.length > 0) {
        $('#filePreview').show();
        let fileListHtml = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            const iconClass = file.type.includes('pdf') ? 'bx-file-pdf' : 'bx-image';
            
            fileListHtml += `
                <div class="d-flex align-items-center mb-2">
                    <i class="bx ${iconClass} me-2 text-primary"></i>
                    <div class="flex-grow-1">
                        <small class="fw-bold">${file.name}</small>
                        <br><small class="text-muted">${sizeInMB} MB</small>
                    </div>
                </div>
            `;
        }
        
        $('#fileList').html(fileListHtml);
    } else {
        $('#filePreview').hide();
    }
}

function validateBulkUpload() {
    const selectedParticipants = $('.bulk-participant-checkbox:checked').length;
    const selectedFiles = $('#bulkCertificateFiles')[0].files.length;
    const submitBtn = $('#bulkUploadSubmitBtn');
    const warning = $('#uploadWarning');
    
    let canSubmit = selectedParticipants > 0 && selectedFiles > 0;
    let warningMessage = '';
    
    if (selectedParticipants > 0 && selectedFiles > 0) {
        if (selectedFiles !== selectedParticipants) {
            warningMessage = `You have selected ${selectedParticipants} participants but ${selectedFiles} files. `;
            if (selectedFiles < selectedParticipants) {
                warningMessage += 'Files will be distributed cyclically among participants.';
            } else {
                warningMessage += 'Extra files will be ignored.';
            }
        }
        
        // Check for participants with existing certificates
        const withCertificates = $('.bulk-participant-checkbox:checked[data-has-certificate="true"]').length;
        if (withCertificates > 0) {
            if (warningMessage) warningMessage += ' ';
            warningMessage += `${withCertificates} selected participants already have certificates that will be replaced.`;
        }
    }
    
    if (warningMessage) {
        warning.show();
        $('#warningMessage').text(warningMessage);
    } else {
        warning.hide();
    }
    
    submitBtn.prop('disabled', !canSubmit);
}

function validateFileSize(file) {
    if (file.size > 2048 * 1024) {
        showAlert('error', `File "${file.name}" is too large. Maximum size is 2MB.`);
        return false;
    }
    return true;
}

function showLoadingState(buttonSelector, text) {
    const btn = $(buttonSelector);
    btn.prop('disabled', true);
    btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status"></span>${text}`);
}

function resetUploadModal() {
    const submitBtn = $('#uploadSubmitBtn');
    submitBtn.prop('disabled', false).removeClass('btn-warning').addClass('btn-primary');
    submitBtn.html('<i class="bx bx-upload me-1"></i><span id="uploadBtnText">Upload</span>');
    $('.modal-title').text('Upload Certificate');
    $('#currentCertificateInfo').hide();
    $('input[name="certificate"]').val('');
}

function resetBulkUploadModal() {
    $('#bulkUploadSubmitBtn').prop('disabled', true).html('<i class="bx bx-upload me-1"></i>Upload Certificates');
    $('.bulk-participant-checkbox').prop('checked', false);
    $('#bulkCertificateFiles').val('');
    $('#filePreview').hide();
    $('#uploadWarning').hide();
    updateSelectedCount();
}

function resetRevokeModal() {
    $('#revokeSubmitBtn').prop('disabled', false).html('<i class="bx bx-x-circle me-1"></i>Revoke Certificate');
    $('textarea[name="reason"]').val('');
}

function showAlert(type, message) {
    const icons = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    Swal.fire({
        title: type.charAt(0).toUpperCase() + type.slice(1) + '!',
        html: message,
        icon: icons[type] || 'info',
        timer: type === 'success' ? 3000 : 0,
        showConfirmButton: type !== 'success'
    });
}

// Modal functions
function showUploadModal(participantId, participantName, isReplace = false) {
    $('#participantId').val(participantId);
    $('#participantName').val(participantName);
    $('input[name="certificate"]').val('');
    
    if (isReplace) {
        $('#currentCertificateInfo').show();
        $('#uploadBtnText').text('Replace');
        $('#uploadSubmitBtn').removeClass('btn-primary').addClass('btn-warning');
        $('.modal-title').text('Replace Certificate');
    } else {
        $('#currentCertificateInfo').hide();
        $('#uploadBtnText').text('Upload');
        $('#uploadSubmitBtn').removeClass('btn-warning').addClass('btn-primary');
        $('.modal-title').text('Upload Certificate');
    }
    
    $('#uploadCertificateModal').modal('show');
}

function showBulkUploadModal() {
    resetBulkUploadModal();
    $('#bulkUploadModal').modal('show');
}

function showRevokeModal(participantId, participantName) {
    $('#revokeParticipantName').val(participantName);
    const form = document.getElementById('revokeCertificateForm');
    form.action = `{{ url('committee/certificate/revoke') }}/${participantId}`;
    $('#revokeCertificateModal').modal('show');
}
// Download certificate function
function downloadCertificate(participantId) {
    // You need to implement this function to call your download certificate API
    window.open(`{{ url('committee/certificate/download') }}/${participantId}`, '_blank');
}

function selectAllParticipants() {
    $('.bulk-participant-checkbox').prop('checked', true);
    updateSelectedCount();
    validateBulkUpload();
}

function clearAllParticipants() {
    $('.bulk-participant-checkbox').prop('checked', false);
    updateSelectedCount();
    validateBulkUpload();
}

function selectWithoutCertificates() {
    $('.bulk-participant-checkbox').prop('checked', false);
    $('.bulk-participant-checkbox[data-has-certificate="false"]').prop('checked', true);
    updateSelectedCount();
    validateBulkUpload();
}

// Export functionality
function exportAttendance() {
    const sessionId = '{{ $data["session"]["_id"] }}';
    showLoadingState('#exportBtn', 'Exporting...');
    
    window.location.href = `{{ url('committee/attendance') }}/${sessionId}/export`;
    
    // Reset button after a delay
    setTimeout(() => {
        const btn = $('#exportBtn');
        if (btn.length) {
            btn.prop('disabled', false).html('<i class="bx bx-download me-1"></i>Export Attendance');
        }
    }, 2000);
}

// Bulk actions modal
function showBulkActionsModal() {
    const selectedCount = $('.participant-checkbox:checked').length;
    
    if (selectedCount === 0) {
        showAlert('warning', 'Please select participants first');
        return;
    }
    
    const selectedParticipants = [];
    $('.participant-checkbox:checked').each(function() {
        selectedParticipants.push({
            id: $(this).val(),
            name: $(this).data('name'),
            hasCertificate: $(this).data('has-certificate') === 'true'
        });
    });
    
    // Show custom bulk actions modal
    let modalHtml = `
        <div class="modal fade" id="bulkActionsModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Actions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Selected participants: <strong>${selectedCount}</strong></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="bulkDownloadCertificates()">
                                <i class="bx bx-download me-1"></i>Download All Certificates
                            </button>
                            <button class="btn btn-outline-warning" onclick="bulkRevokeCertificates()">
                                <i class="bx bx-x-circle me-1"></i>Revoke All Certificates
                            </button>
                            <button class="btn btn-outline-info" onclick="sendBulkEmails()">
                                <i class="bx bx-mail-send me-1"></i>Send Certificate Emails
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#bulkActionsModal').remove();
    
    // Add and show modal
    $('body').append(modalHtml);
    $('#bulkActionsModal').modal('show');
}

// Bulk download certificates
function bulkDownloadCertificates() {
    const selectedIds = [];
    $('.participant-checkbox:checked').each(function() {
        if ($(this).data('has-certificate') === 'true') {
            selectedIds.push($(this).val());
        }
    });
    
    if (selectedIds.length === 0) {
        showAlert('warning', 'No participants with certificates selected');
        return;
    }
    
    // Download certificates one by one
    selectedIds.forEach((id, index) => {
        setTimeout(() => {
            downloadCertificate(id);
        }, index * 500); // Delay to prevent overwhelming the server
    });
    
    $('#bulkActionsModal').modal('hide');
    showAlert('info', `Downloading ${selectedIds.length} certificates...`);
}

// Enhanced file validation
function validateFileTypes(files) {
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const allowedExtensions = ['.pdf', '.jpg', '.jpeg', '.png'];
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileName = file.name.toLowerCase();
        const fileType = file.type.toLowerCase();
        
        // Check file type
        if (!allowedTypes.includes(fileType)) {
            const extension = fileName.substring(fileName.lastIndexOf('.'));
            if (!allowedExtensions.includes(extension)) {
                showAlert('error', `File "${file.name}" has invalid format. Only PDF, JPG, and PNG files are allowed.`);
                return false;
            }
        }
        
        // Check file size (2MB)
        if (file.size > 2048 * 1024) {
            showAlert('error', `File "${file.name}" is too large. Maximum size is 2MB.`);
            return false;
        }
    }
    
    return true;
}

// Enhanced form validation for single upload
$('#uploadCertificateModal form').on('submit', function(e) {
    const fileInput = $('input[name="certificate"]')[0];
    
    if (fileInput.files.length === 0) {
        e.preventDefault();
        showAlert('error', 'Please select a certificate file');
        return false;
    }
    
    if (!validateFileTypes(fileInput.files)) {
        e.preventDefault();
        return false;
    }
});

// Enhanced form validation for bulk upload
$('#bulkUploadForm').on('submit', function(e) {
    const selectedParticipants = $('.bulk-participant-checkbox:checked').length;
    const fileInput = $('#bulkCertificateFiles')[0];
    
    if (selectedParticipants === 0) {
        e.preventDefault();
        showAlert('error', 'Please select at least one participant');
        return false;
    }
    
    if (fileInput.files.length === 0) {
        e.preventDefault();
        showAlert('error', 'Please select at least one certificate file');
        return false;
    }
    
    if (!validateFileTypes(fileInput.files)) {
        e.preventDefault();
        return false;
    }
});

// Progress indicator for file uploads
function showUploadProgress(formSelector) {
    const form = $(formSelector);
    const progressHtml = `
        <div class="upload-progress mt-3">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-muted">Uploading files...</small>
        </div>
    `;
    
    form.find('.modal-body').append(progressHtml);
    
    // Simulate progress (in real implementation, you'd track actual upload progress)
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        
        $('.progress-bar').css('width', progress + '%');
        
        if (progress >= 90) {
            clearInterval(interval);
        }
    }, 200);
}



</script>
@endsection