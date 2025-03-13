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
        // Lấy tất cả users
        $users = User::all();

        foreach ($users as $user) {
            // Tạo cài đặt với giá trị ngẫu nhiên
            UserSetting::create([
                'user_id' => $user->id,
                'is_notification' => rand(0, 1),

            ]);
        }
    }
}