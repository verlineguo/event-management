@extends('admin.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('committee.event.index') }}">Event Management </a>/</span> Create Event
        </h5>

        <div class="card">
            <h5 class="card-header">Form Event</h5>
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

                <form id="createEventForm" action="{{ route('committee.event.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                            required placeholder="Enter event name">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                            placeholder="Enter event description">{{ old('description') }}</textarea>
                        <div class="form-text">Optional: Brief description about the event</div>
                    </div>

                    <div class="mb-4">
                        <label for="category_id" class="form-label">Event Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Event Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category['_id'] }}" {{ old('category_id') == $category['_id'] ? 'selected' : '' }}>
                                    {{ $category['name']  }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="poster" class="form-label">Event Poster</label>
                        <input type="file" class="form-control" id="poster" name="poster" 
                            accept="image/*" onchange="previewImage(this)" required>
                        <div class="form-text">Upload event poster (JPG, PNG, GIF) - Required</div>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <div class="card" style="max-width: 300px;">
                                <img id="previewImg" src="" class="card-img-top" alt="Poster Preview" 
                                    style="object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">Poster Preview</small>
                                    <button type="button" class="btn btn-sm btn-outline-danger float-end" 
                                        onclick="removeImage()">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="max_participants" class="form-label">Max Participants</label>
                        <input type="number" class="form-control" id="max_participants" name="max_participants" 
                            value="{{ old('max_participants') }}" min="1"
                            placeholder="Enter maximum number of participants" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                            <!-- Default first session -->
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

                                <!-- Session Fee Field -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Session Fee</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" name="sessions[0][session_fee]" 
                                                    value="0" min="0" step="1000" placeholder="0" required>
                                            </div>
                                            <div class="form-text">Enter 0 for free session</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>Create Event
                        </button>
                        <a href="{{ route('committee.event.index') }}" class="btn btn-secondary">
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
        let sessionCount = 1;

        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission
            const form = document.getElementById('createEventForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate sessions
                if (!validateSessions()) {
                    return;
                }
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to create a new event with " + sessionCount + " session(s)!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, create it!'
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
        });

        // Set minimum date to today
        function setMinDate() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.setAttribute('min', today);
            });
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Session Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="sessions[${sessionCount}][session_fee]" 
                                        value="0" min="0" step="1000" placeholder="0" required>
                                </div>
                                <div class="form-text">Enter 0 for free session</div>
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
                sessionItem.remove();
                updateSessionNumbers();
                updateRemoveButtons();
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