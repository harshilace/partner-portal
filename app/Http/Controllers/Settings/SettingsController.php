<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $partner = $user->partners()->first();

        return Inertia::render('Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
                'avatar' => session('user_avatar') ?? null,
                'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : null,
            ],
            'partner' => $partner ? [
                'id' => $partner->id,
                'name' => $partner->name,
                'type' => $partner->type instanceof \BackedEnum ? $partner->type->value : $partner->type,
                'code' => $partner->code ?? 'PART-8892',
                'website' => 'https://partner-portal.example.com',
                'tax_id' => 'TX-994021-X',
                'logo' => session('partner_logo') ?? null,
                'banner' => session('partner_banner') ?? null,
            ] : [
                'id' => 1,
                'name' => 'Apex Partner Solutions',
                'type' => 'main_partner',
                'code' => 'PART-8892',
                'website' => 'https://apexpartner.example.com',
                'tax_id' => 'TX-994021-X',
                'logo' => session('partner_logo') ?? null,
                'banner' => session('partner_banner') ?? null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return back()->with('success', 'Profile information updated successfully.');
    }

    /**
     * Upload avatar, company logo, or branding images.
     */
    public function uploadImage(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:avatar,logo,banner'],
            'image' => ['required_without:image_url', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'image_url' => ['nullable', 'string'],
        ]);

        $type = $request->input('type');
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('branding', 'public');
            $imageUrl = Storage::url($path);
        } elseif ($request->input('image_url')) {
            $imageUrl = $request->input('image_url');
        }

        if ($type === 'avatar') {
            session(['user_avatar' => $imageUrl]);
            $msg = 'Profile avatar updated successfully.';
        } elseif ($type === 'logo') {
            session(['partner_logo' => $imageUrl]);
            $msg = 'Partner logo updated successfully.';
        } else {
            session(['partner_banner' => $imageUrl]);
            $msg = 'Branding banner updated successfully.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Update security settings (password).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
