<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     *
     * @return \Illuminate\View\View
     */
    public function showMyProfile()
    {
        $user = Auth::user();

        return view('profile.show', compact('user'));
    }

    /**
     * 
     * @param  \App\Models\User
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        if (Auth::check() && Auth::id() == $user->id) {
            return redirect()->route('profile.show.mine');
        }
    }


    /**
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('settings.profile-setting', compact('user'));
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'mobile_number' => 'nullable|string|max:20',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date',
            'profile_photo' => 'nullable|image|max:10240|dimensions:min_width=400,min_height=400', // Max 10MB, min 400x400px
            'cover_photo' => 'nullable|image|max:10240|dimensions:min_width=1200,min_height=300',   // Max 10MB, min 1200x300px
        ]);

        $user->name = $request->input('full_name');
        $user->phone_number = $request->input('phone_number');
        $user->mobile_number = $request->input('mobile_number');
        $user->gender = $request->input('gender');
        $user->date_of_birth = $request->input('date_of_birth');

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        if ($request->hasFile('cover_photo')) {
            if ($user->cover_photo_path && Storage::disk('public')->exists($user->cover_photo_path)) {
                Storage::disk('public')->delete($user->cover_photo_path);
            }
            $path = $request->file('cover_photo')->store('cover-photos', 'public');
            $user->cover_photo_path = $path;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil Anda berhasil diperbarui!');
    }

    /**
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfilePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null; // Set path ke null
            $user->save();
            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada foto profil untuk dihapus.');
    }

    /**
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCoverPhoto()
    {
        $user = Auth::user();

        if ($user->cover_photo_path && Storage::disk('public')->exists($user->cover_photo_path)) {
            Storage::disk('public')->delete($user->cover_photo_path);
            $user->cover_photo_path = null; // Set path ke null
            $user->save();
            return redirect()->back()->with('success', 'Foto sampul berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada foto sampul untuk dihapus.');
    }
}