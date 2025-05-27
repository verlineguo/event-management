@extends('committee.layouts.app')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light"><a href="{{ route('committee.event.index') }}">Event Management </a>/</span> Edit Event
        </h5>
        
        <div class="card">
            <h5 class="card-header">Edit Event</h5>
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
                
                <form id="editEventForm" action="{{ route('committee.event.update', $event['_id']) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $event['name']) }}" required placeholder="Enter event name">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="date" class="form-label">Event Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
    value="{{ old('date', isset($event['date']) ? date('Y-m-d', strtotime($event['date'])) : '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="time" class="form-label">Event Time</label>
                                <input type="time" class="form-control" id="time" name="time" 
                                    value="{{ old('time', $event['time']) }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location"
                            value="{{ old('location', $event['location']) }}" required placeholder="Enter event location">
                    </div>
                    
                    <div class="mb-4">
                        <label for="speaker" class="form-label">Speaker</label>
                        <input type="text" class="form-control" id="speaker" name="speaker"
                            value="{{ old('speaker', $event['speaker']) }}" required placeholder="Enter speaker name">
                    </div>
                    
                    <div class="mb-4">
                        <label for="poster" class="form-label">Event Poster</label>
                        <input type="file" class="form-control" id="poster" name="poster" 
                            accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Optional: Upload new poster to replace current one (JPG, PNG, GIF)</div>
                        
                        <!-- Current Image Display -->
                        @if(isset($event['poster']) && !empty($event['poster']))
                            <div class="mt-3">
                                <div class="card" style="max-width: 300px;">
                                    <img src="{{ asset('storage/' . $event['poster']) }}" class="card-img-top" alt="Current Poster" 
                                        style="object-fit: cover;">
                                    <div class="card-body p-2">
                                        <small class="text-muted">Current Poster</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- New Image Preview -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <div class="card" style="max-width: 300px;">
                                <img id="previewImg" src="" class="card-img-top" alt="New Poster Preview" 
                                    style="object-fit: cover;">
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
                                        value="{{ old('registration_fee', $event['registration_fee'] ?? 0) }}" 
                                        min="0" step="1000" placeholder="0">
                                </div>
                                <div class="form-text">Enter 0 for free events</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                    value="{{ old('max_participants', $event['max_participants'] ?? '') }}" 
                                    min="1" placeholder="Enter maximum number of participants">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="open" {{ old('status', $event['status'] ?? '') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status', $event['status'] ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="ongoing" {{ old('status', $event['status'] ?? '') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="cancelled" {{ old('status', $event['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="completed" {{ old('status', $event['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update Event
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
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission
            const form = document.getElementById('editEventForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update this event!",
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

            // Set minimum date to today for future events
            const dateInput = document.getElementById('date');
            const today = new Date().toISOString().split('T')[0];
            // Comment this line if you want to allow editing past events
            // dateInput.setAttribute('min', today);

            // Format registration fee input
            const feeInput = document.getElementById('registration_fee');
            feeInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });
        });

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