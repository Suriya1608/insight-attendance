<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $users = [
            ['name' => 'Admin User',    'email' => 'admin@hrportal.com',    'password' => Hash::make('password'), 'role' => 'admin'],
            ['name' => 'Manager User',  'email' => 'manager@hrportal.com',  'password' => Hash::make('password'), 'role' => 'manager'],
            ['name' => 'Employee User', 'email' => 'employee@hrportal.com', 'password' => Hash::make('password'), 'role' => 'employee'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }

        // Default site settings
        $defaults = [
            'site_title'   => 'HR Portal — Attendance & Payroll',
            'site_name'    => 'HR Portal',
            'site_url'     => config('app.url', 'http://localhost'),
            'site_logo'    => null,
            'site_favicon' => null,
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Default departments
        $departments = [
            'HR Recruiting',
            'Tech Consulting',
            'Digital Marketing',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(['name' => $name], ['status' => 'active']);
        }
    }
}
