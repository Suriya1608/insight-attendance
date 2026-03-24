<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = $this->currentUser()->load(['department', 'employeeDetail', 'level1Manager', 'level2Manager']);
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = $this->currentUser()->load(['department', 'employeeDetail']);
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $this->currentUser();

        $request->validate([
            'mobile'            => 'nullable|string|max:15',
            'emergency_contact' => 'nullable|string|max:15',
            'address_line1'     => 'nullable|string|max:255',
            'address_line2'     => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:100',
            'state'             => 'nullable|string|max:100',
            'country'           => 'nullable|string|max:100',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->update(['mobile' => $request->input('mobile')]);

        $detailData = [
            'emergency_contact' => $request->input('emergency_contact'),
            'address_line1'     => $request->input('address_line1'),
            'address_line2'     => $request->input('address_line2'),
            'city'              => $request->input('city'),
            'state'             => $request->input('state'),
            'country'           => $request->input('country') ?: 'India',
        ];

        if ($request->hasFile('profile_image')) {
            $existing = $user->employeeDetail?->profile_image;
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $detailData['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->employeeDetail()->updateOrCreate(
            ['user_id' => $user->id],
            $detailData
        );

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function changePasswordForm()
    {
        return view('profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/',      // uppercase
                'regex:/[a-z]/',      // lowercase
                'regex:/[0-9]/',      // digit
                'regex:/[@$!%*#?&]/', // special char
            ],
        ], [
            'new_password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@$!%*#?&).',
        ]);

        $user = $this->currentUser();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'New password must be different from the current password.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('password.change')->with('success', 'Password changed successfully.');
    }
}
