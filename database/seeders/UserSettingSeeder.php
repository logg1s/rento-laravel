<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class UserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = User::all();

        foreach ($users as $user) {

            UserSetting::create([
                'user_id' => $user->id,
                'is_notification' => rand(0, 1),

            ]);
        }
    }
}