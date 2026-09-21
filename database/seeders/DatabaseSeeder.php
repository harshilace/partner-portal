<?php

namespace Database\Seeders;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => Role::ADMIN->value,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Main Partner & User
        $mainPartner = Partner::firstOrCreate(
            ['partner_code' => 'PARTNER-MAIN'],
            [
                'name' => 'Main Partner Organization',
                'type' => 'main',
                'status' => 'active',
            ]
        );

        $mainUser = User::firstOrCreate(
            ['email' => 'partner@example.com'],
            [
                'name' => 'Main Partner User',
                'password' => Hash::make('password'),
                'role' => Role::MAIN_PARTNER->value,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        PartnerUser::firstOrCreate([
            'partner_id' => $mainPartner->id,
            'user_id' => $mainUser->id,
        ]);

        // Sub Partner & User
        $subPartner = Partner::firstOrCreate(
            ['partner_code' => 'PARTNER-SUB'],
            [
                'name' => 'Sub Partner Organization',
                'type' => 'sub',
                'parent_partner_id' => $mainPartner->id,
                'status' => 'active',
            ]
        );

        $subUser = User::firstOrCreate(
            ['email' => 'subpartner@example.com'],
            [
                'name' => 'Sub Partner User',
                'password' => Hash::make('password'),
                'role' => Role::SUB_PARTNER->value,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        PartnerUser::firstOrCreate([
            'partner_id' => $subPartner->id,
            'user_id' => $subUser->id,
        ]);

        // Default Test User
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => Role::PARTNER_USER->value,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
