<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::allKeyed();
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_title'              => ['required', 'string', 'max:150'],
            'site_name'               => ['required', 'string', 'max:150'],
            'site_url'                => ['required', 'url', 'max:255'],
            'employee_id_prefix'      => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/i'],
            'company_address'         => ['nullable', 'string', 'max:500'],
            'company_email'           => ['nullable', 'email', 'max:200'],
            'company_mobile'          => ['nullable', 'string', 'max:20'],
            'signatory_name'          => ['nullable', 'string', 'max:150'],
            'signatory_designation'   => ['nullable', 'string', 'max:150'],
            'site_logo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'site_favicon'            => ['nullable', 'image', 'mimes:ico,png,jpg,jpeg,svg', 'max:512'],
        ]);

        // Plain text fields
        SiteSetting::set('site_title',            $request->input('site_title'));
        SiteSetting::set('site_name',             $request->input('site_name'));
        SiteSetting::set('site_url',              $request->input('site_url'));
        SiteSetting::set('employee_id_prefix',    strtoupper($request->input('employee_id_prefix')));
        SiteSetting::set('company_address',       $request->input('company_address', ''));
        SiteSetting::set('company_email',         $request->input('company_email', ''));
        SiteSetting::set('company_mobile',        $request->input('company_mobile', ''));
        SiteSetting::set('signatory_name',        $request->input('signatory_name', ''));
        SiteSetting::set('signatory_designation', $request->input('signatory_designation', ''));

        // Logo upload
        if ($request->hasFile('site_logo')) {
            $this->deleteOldFile(SiteSetting::get('site_logo'));
            $path = $request->file('site_logo')->store('settings', 'public');
            SiteSetting::set('site_logo', $path);
        }

        // Favicon upload
        if ($request->hasFile('site_favicon')) {
            $this->deleteOldFile(SiteSetting::get('site_favicon'));
            $path = $request->file('site_favicon')->store('settings', 'public');
            SiteSetting::set('site_favicon', $path);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Site settings updated successfully.');
    }

    private function deleteOldFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
