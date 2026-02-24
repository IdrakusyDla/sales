<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Show profile page or force password change if still using default password.
     */
    public function showProfile(Request $request)
    {
        $user = $request->user()->load('supervisor');
        
        // Jika password masih default, paksa ganti password
        if ($user->hasDefaultPassword()) {
            return redirect()->route('password.edit', ['force' => true])
                ->with('warning', 'Anda harus mengganti password default terlebih dahulu!');
        }

        return view('profile.index', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing password.
     */
    public function editPassword(Request $request): View
    {
        $user = $request->user();
        $forceChange = $request->has('force') || $user->hasDefaultPassword();
        
        return view('profile.password', [
            'user' => $user,
            'forceChange' => $forceChange,
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();
        $wasDefault = $user->hasDefaultPassword();

        // Cek password lama
        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama salah!']);
        }

        // Update password baru
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        // Jika sebelumnya password default, redirect ke profile
        if ($wasDefault) {
            return Redirect::route('profile.show')->with('success', 'Password berhasil diganti! Silakan gunakan password baru untuk login selanjutnya.');
        }

        return Redirect::route('profile.show')->with('success', 'Password berhasil diganti!');
    }
}
