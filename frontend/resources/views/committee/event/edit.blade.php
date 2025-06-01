@extends('admin.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('committee.event.index') }}">Event Management </a>/</span> Edit Event
        </h5>

        <div class="card">
            <h5 class="card-header">Edit Event - {{ $event->name }}</h5>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="editEventForm" action="{{ route('committee.event.update', $event->_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $event->name) }}"
                            required placeholder="Enter event name">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                            placeholder="Enter event description">{{ old('description', $event->description) }}</textarea>
                        <div class="form-text">Optional: Brief description about the event</div>
                    </div>

                    <div class="mb-4">
                        <label for="category_id" class="form-label">Event Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Event Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category['_id'] }}" 
                                    {{ old('category_id', $event->category_id) == $category['_id'] ? 'selected' : '' }}>
                                    {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="poster" class="form-label">Event Poster</label>
                        
                        <!-- Current Poster Display -->
                        @if($event->poster)
                            <div class="mb-3">
                                <div class="card" style="max-width: 300px;">
                                    <img src="{{ $event->poster }}" class="card-img-top" alt="Current Poster" 
                                        style="object-fit: cover; height: 200px;">
                                    <div class="card-body p-2">
                                        <small class="text-muted">Current Poster</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" class="form-control" id="poster" name="poster" 
                            accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Upload new poster to replace current one (JPG, PNG, GIF) - Optional</div>
                        
                        <!-- New Image Preview -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <div class="card" style="max-width: 300px;">
                                <img id="previewImg" src="" class="card-img-top" alt="New Poster Preview" 
                                    style="object-fit: cover; height: 200px;">
                                <div class="card-body p-2">
                                    <small class="text-muted">New Poster Preview</small>
                                    <button type="button" class="btn btn-sm btn-outline-danger float-end" 
                                        onclick="removeImage()">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="registration_fee" class="form-label">Registration Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="registration_fee" name="registration_fee" 
                                        value="{{ old('registration_fee', $event->registration_fee) }}" min="0" step="1000"
                                        placeholder="0" required>
                                </div>
                                <div class="form-text">Enter 0 for free events</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                    value="{{ old('max_participants', $event->max_participants) }}" min="1"
                                    placeholder="Enter maximum number of participants" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="open" {{ old('status', $event->status) == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status', $event->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Sessions Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold">Event Sessions</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSession()">
                                <i class="bx bx-plus"></i> Add Session
                            </button>
                        </div>
                        
                        <div id="sessionsContainer">
                            @if($sessions && count($sessions) > 0)
                                @foreach($sessions as $index => $session)
                                    <div class="session-item border rounded p-3 mb-3" data-session="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Session {{ $index + 1 }}</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="removeSession({{ $index }})" 
                                                style="{{ count($sessions) <= 1 ? 'display: none;' : '' }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Hidden field for session ID if editing existing session -->
                                        @if(isset($session->_id))
                                            <input type="hidden" name="sessions[{{ $index }}][id]" value="{{ $session->_id }}">
                                        @endif
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Session Title</label>
                                                    <input type="text" class="form-control" name="sessions[{{ $index }}][title]" 
                                                        value="{{ old('sessions.'.$index.'.title', $session->title ?? '') }}"
                                                        placeholder="Enter session title" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Speaker</label>
                                                    <input type="text" class="form-control" name="sessions[{{ $index }}][speaker]" 
                                                        value="{{ old('sessions.'.$index.'.speaker', $session->speaker ?? '') }}"
                                                        placeholder="Enter speaker name" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="sessions[{{ $index }}][description]" rows="2" 
                                                placeholder="Session description">{{ old('sessions.'.$index.'.description', $session->description ?? '') }}</textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" class="form-control" name="sessions[{{ $index }}][date]" 
                                                        value="{{ old('sessions.'.$index.'.date', isset($session->date) ? date('Y-m-d', strtotime($session->date)) : '') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" class="form-control" name="sessions[{{ $index }}][start_time]" 
                                                        value="{{ old('sessions.'.$index.'.start_time', $session->start_time ?? '') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" class="form-control" name="sessions[{{ $index }}][end_time]" 
                                                        value="{{ old('sessions.'.$index.'.end_time', $session->end_time ?? '') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Location</label>
                                                    <input type="text" class="form-control" name="sessions[{{ $index }}][location]" 
                                                        value="{{ old('sessions.'.$index.'.location', $session->location ?? '') }}"
                                                        placeholder="Session location" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Max Participants (Optional)</label>
                                                    <input type="number" class="form-control" name="sessions[{{ $index }}][max_participants]" 
                                                        value="{{ old('sessions.'.$index.'.max_participants', $session->max_participants ?? '') }}"
                                                        placeholder="Leave empty to use event limit" min="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Default first session if no sessions exist -->
                                <div class="session-item border rounded p-3 mb-3" data-session="0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Session 1</h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSession(0)" style="display: none;">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Session Title</label>
                                                <input type="text" class="form-control" name="sessions[0][title]" 
                                                    placeholder="Enter session title" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Speaker</label>
                                                <input type="text" class="form-control" name="sessions[0][speaker]" 
                                                    placeholder="Enter speaker name" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="sessions[0][description]" rows="2" 
                                            placeholder="Session description"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="sessions[0][date]" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Start Time</label>
                                                <input type="time" class="form-control" name="sessions[0][start_time]" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">End Time</label>
                                                <input type="time" class="form-control" name="sessions[0][end_time]" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" class="form-control" name="sessions[0][location]" 
                                                    placeholder="Session location" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Max Participants (Optional)</label>
                                                <input type="number" class="form-control" name="sessions[0][max_participants]" 
                                                    placeholder="Leave empty to use event limit" min="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update Event
                        </button>
                        <a href="{{ route('committee.event.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Cancel
                        </a>
                        <a href="{{ route('committee.event.show', $event->_id) }}" class="btn btn-info">
                            <i class="bx bx-show me-1"></i>View Event
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let sessionCount = {{ $sessions && count($sessions) > 0 ? count($sessions) : 1 }};

        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission
            const form = document.getElementById('editEventForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate sessions
                if (!validateSessions()) {
                    return;
                }
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update this event with " + sessionCount + " session(s)!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If user confirms, submit the form
                        form.submit();
                    }
                });
            });

            // Check for success message from session
            @if(session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            // Check for error message from session
            @if(session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif

            // Set minimum date to today for all date inputs
            setMinDate();

            // Format registration fee input
            const feeInput = document.getElementById('registration_fee');
            feeInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });

            // Update session count and button visibility on load
            updateRemoveButtons();
        });

        // Set minimum date to today (but allow past dates for editing existing events)
        function setMinDate() {
            // For edit form, we might want to be more lenient with past dates
            // Uncomment the following lines if you want to enforce minimum date
            // const today = new Date().toISOString().split('T')[0];
            // document.querySelectorAll('input[type="date"]').forEach(input => {
            //     input.setAttribute('min', today);
            // });
        }

        // Add new session
        function addSession() {
            const container = document.getElementById('sessionsContainer');
            const sessionHtml = `
                <div class="session-item border rounded p-3 mb-3" data-session="${sessionCount}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Session ${sessionCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSession(${sessionCount})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Session Title</label>
                                <input type="text" class="form-control" name="sessions[${sessionCount}][title]" 
                                    placeholder="Enter session title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Speaker</label>
                                <input type="text" class="form-control" name="sessions[${sessionCount}][speaker]" 
                                    placeholder="Enter speaker name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="sessions[${sessionCount}][description]" rows="2" 
                            placeholder="Session description"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="sessions[${sessionCount}][date]" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="sessions[${sessionCount}][start_time]" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="sessions[${sessionCount}][end_time]" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="sessions[${sessionCount}][location]" 
                                    placeholder="Session location" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Participants (Optional)</label>
                                <input type="number" class="form-control" name="sessions[${sessionCount}][max_participants]" 
                                    placeholder="Leave empty to use event limit" min="1">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', sessionHtml);
            sessionCount++;
            
            // Update min date for new date inputs
            setMinDate();
            
            // Show remove button for first session if there are multiple sessions
            updateRemoveButtons();
        }

        // Remove session
        function removeSession(index) {
            const sessionItem = document.querySelector(`[data-session="${index}"]`);
            if (sessionItem) {
                // Confirm deletion especially for existing sessions
                const hasId = sessionItem.querySelector('input[name*="[id]"]');
                const confirmText = hasId ? 
                    'This will permanently delete this session. Are you sure?' : 
                    'Remove this session?';
                
                Swal.fire({
                    title: 'Remove Session?',
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sessionItem.remove();
                        updateSessionNumbers();
                        updateRemoveButtons();
                        
                        // If it was an existing session, we might want to mark it for deletion
                        if (hasId) {
                            // Add hidden input to mark session for deletion
                            const form = document.getElementById('editEventForm');
                            const deleteInput = document.createElement('input');
                            deleteInput.type = 'hidden';
                            deleteInput.name = 'deleted_sessions[]';
                            deleteInput.value = hasId.value;
                            form.appendChild(deleteInput);
                        }
                    }
                });
            }
        }

        // Update session numbers after removal
        function updateSessionNumbers() {
            const sessions = document.querySelectorAll('.session-item');
            sessions.forEach((session, index) => {
                const title = session.querySelector('h6');
                title.textContent = `Session ${index + 1}`;
            });
        }

        // Update remove button visibility
        function updateRemoveButtons() {
            const sessions = document.querySelectorAll('.session-item');
            const removeButtons = document.querySelectorAll('.session-item .btn-outline-danger');
            
            if (sessions.length === 1) {
                removeButtons.forEach(btn => btn.style.display = 'none');
            } else {
                removeButtons.forEach(btn => btn.style.display = 'inline-block');
            }
        }

        // Validate sessions
        function validateSessions() {
            const sessions = document.querySelectorAll('.session-item');
            let isValid = true;
            
            sessions.forEach(session => {
                const startTime = session.querySelector('input[name*="[start_time]"]').value;
                const endTime = session.querySelector('input[name*="[end_time]"]').value;
                
                if (startTime && endTime && startTime >= endTime) {
                    Swal.fire({
                        title: 'Invalid Time!',
                        text: 'End time must be after start time for all sessions.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    isValid = false;
                    return false;
                }
            });
            
            return isValid;
        }

        // Image preview function
        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Remove image function
        function removeImage() {
            const fileInput = document.getElementById('poster');
            const preview = document.getElementById('imagePreview');
            
            fileInput.value = '';
            preview.style.display = 'none';
        }
    </script>
@endsection