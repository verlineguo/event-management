@extends('member.layouts.app')

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
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="relative w-full h-40 bg-gray-200">
        <img src="{{ Auth::user()->cover_photo_path ? asset('storage/' . Auth::user()->cover_photo_path) : 'https://via.placeholder.com/1200x400/D3D3D3/000000?text=Profile%20Cover' }}" alt="Profile Cover" class="w-full h-full object-cover">
    </div>

    <div class="relative px-8 pb-8 profile-card-content">
        <div class="avatar-container">
            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://via.placeholder.com/128x128/ADD8E6/FFFFFF?text=User' }}" alt="User Avatar" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
        </div>

        <div class="text-center pt-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</h1>
            <div class="mt-4 flex justify-center space-x-4">
        </div>

        <hr class="my-8 border-gray-200">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Personal Details</h3>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Full Name</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Nickname</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->nickname ?? 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Gender</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->gender ? ucfirst(Auth::user()->gender) : 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Date of Birth</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->date_of_birth ? \Carbon\Carbon::parse(Auth::user()->date_of_birth)->format('F d, Y') : 'N/A' }}</p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Contact Information</h3>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Phone Number</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->phone_number ?? 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Mobile Number</p>
                    <p class="text-base text-gray-800">{{ Auth::user()->mobile_number ?? 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-600">Email</p>
                    <p class="text-base text-blue-600">
                        <a href="mailto:{{ Auth::user()->email ?? '' }}">{{ Auth::user()->email ?? 'N/A' }}</a>
                    </p>
                </div>
            </div>
        </div>

        <hr class="my-8 border-gray-200">

        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">About Me</h3>
            <p class="text-gray-700 leading-relaxed">
                {{ Auth::user()->about_me ?? 'Hi' }}
            </p>
        </div>

        <div class="mt-8 text-center">
            @auth
                @if(Auth::id() == ($user->id ?? Auth::id()))
                    <a href="{{ route('profile.edit') }}" class="inline-block bg-blue-500 text-white px-8 py-3 rounded-lg hover:bg-blue-600 text-lg font-medium">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection