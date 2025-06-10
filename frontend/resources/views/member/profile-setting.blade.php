@extends('member.layouts.app') {{-- Menggunakan layout utama app.blade.php --}}

@section('title', 'Settings Profile') {{-- Mengatur judul halaman --}}

@push('styles') {{-- Memasukkan CSS kustom ke dalam head --}}
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Settings</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Section: Your Photo --}}
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Photo</h2>
            <div class="flex items-center space-x-4 mb-4">
                {{-- Tampilkan foto profil jika ada, jika tidak, gunakan placeholder --}}
                <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://via.placeholder.com/96x96/ADD8E6/FFFFFF?text=Avatar' }}" alt="User Avatar" class="w-24 h-24 rounded-full object-cover border-2 border-gray-200">
                <div>
                    <p class="text-sm text-gray-600 mb-2">File smaller than 10MB and at least 400px by 400px.</p>
                    <p class="text-sm text-gray-600">This image will be shown in the members directory and your profile page if you choose to share it with other members. It will also help us recognize you!</p>
                    <div class="mt-3 flex space-x-2">
                        {{-- Form untuk upload foto profil, biasanya di dalam form terpisah atau AJAX --}}
                        <label for="upload-photo" class="cursor-pointer bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm">
                            <i class="fas fa-upload mr-2"></i> Upload photo
                            <input type="file" id="upload-photo" class="hidden">
                        </label>
                        <button type="button" class="text-gray-500 hover:text-red-500 p-2 rounded-full">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Profile Page Cover --}}
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Profile Page Cover</h2>
            <div class="relative w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300 overflow-hidden">
                <img src="{{ Auth::user()->cover_photo_path ? asset('storage/' . Auth::user()->cover_photo_path) : 'https://via.placeholder.com/1200x300/D3D3D3/000000?text=Cover%20Photo' }}" alt="Cover Photo" class="absolute inset-0 w-full h-full object-cover">
            </div>
            <p class="text-sm text-gray-600 mt-2">File smaller than 10MB and at least 1200px by 300px.</p>
            <p class="text-sm text-gray-600 mt-1">This image will be shown as background banner in your profile page if you choose to share it with other members.</p>
            <div class="mt-3 flex space-x-2">
                <label for="upload-cover" class="cursor-pointer bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm">
                    <i class="fas fa-upload mr-2"></i> Upload photo
                    <input type="file" id="upload-cover" class="hidden">
                </label>
                <button type="button" class="text-gray-500 hover:text-red-500 p-2 rounded-full">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <hr class="my-8 border-gray-200">

    {{-- Section: Personal Details --}}
    <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Personal Details</h2>
        {{-- Form utama untuk personal details --}}
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf {{-- CSRF token untuk keamanan --}}
            @method('PUT') {{-- Method spoofing untuk request PUT/PATCH --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" id="full_name"
                        value="{{ old('full_name', Auth::user()->name) }}" {{-- old() untuk mempertahankan input setelah validasi gagal --}}
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="nickname" class="block text-sm font-medium text-gray-700">What should we call you?</label>
                    <input type="text" name="nickname" id="nickname"
                        value="{{ old('nickname', Auth::user()->nickname) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number"
                        value="{{ old('phone_number', Auth::user()->phone_number) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="text" name="mobile_number" id="mobile_number"
                        value="{{ old('mobile_number', Auth::user()->mobile_number) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                    <select name="gender" id="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender', Auth::user()->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', Auth::user()->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', Auth::user()->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth"
                        value="{{ old('date_of_birth', Auth::user()->date_of_birth ? \Carbon\Carbon::parse(Auth::user()->date_of_birth)->format('Y-m-d') : '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-start space-x-3">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">Save changes</button>
                {{-- Gunakan route helper untuk link "View my profile" --}}
                <a href="{{ route('profile.show.mine') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300">View my profile</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts') {{-- Memasukkan JavaScript kustom ke dalam body (sebelum </body>) --}}
    <script>
        // Contoh sederhana untuk menampilkan nama file saat upload
        document.getElementById('upload-photo').addEventListener('change', function(event) {
            if (event.target.files.length > 0) {
                console.log('Photo selected:', event.target.files[0].name);
                // Anda bisa menambahkan logika preview gambar di sini
            }
        });

        document.getElementById('upload-cover').addEventListener('change', function(event) {
            if (event.target.files.length > 0) {
                console.log('Cover selected:', event.target.files[0].name);
                // Anda bisa menambahkan logika preview gambar di sini
            }
        });
    </script>
@endpush