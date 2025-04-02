<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone_number' => '0123456789',
            'password' => Hash::make('password'),
            'address' => 'Hà Nội, Việt Nam',
            'is_oauth' => false,
            'status' => UserStatusEnum::ACTIVE,
        ]);
        $admin->role()->attach(['admin']);


        for ($i = 1; $i <= 7; $i++) {
            $user = User::create([
                'name' => 'Người dùng ' . $i,
                'email' => 'user' . $i . '@example.com',
                'phone_number' => '098765432' . $i,
                'password' => Hash::make('password'),
                'address' => 'Địa chỉ người dùng ' . $i,
                'is_oauth' => false,
                'status' => UserStatusEnum::ACTIVE,
            ]);
            $user->role()->attach(['user']);
        }


        for ($i = 1; $i <= 7; $i++) {
            $provider = User::create([
                'name' => 'Nhà cung cấp ' . $i,
                'email' => 'provider' . $i . '@example.com',
                'phone_number' => '097654321' . $i,
                'password' => Hash::make('password'),
                'address' => 'Địa chỉ nhà cung cấp ' . $i,
                'is_oauth' => false,
                'status' => UserStatusEnum::ACTIVE,
            ]);
            $provider->role()->attach(['provider']);
        }
    }
}