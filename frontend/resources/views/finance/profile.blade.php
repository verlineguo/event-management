@extends('finance.layouts.app')

@section('title', 'Profile' . (Auth::user()->name ?? 'Guest'))

@push('styles')
    <style>
        .profile-card-content {
            padding-top: 5rem;
        }
        .avatar-container {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
    </style>
@endpush

@section('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    {{-- Success and Error Alerts --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        </script>
    @endif
    
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    showConfirmButton: true
                });
            });
        </script>
    @endif

@endsection