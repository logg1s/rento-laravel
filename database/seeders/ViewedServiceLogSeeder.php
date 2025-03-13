<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use App\Models\ViewedServiceLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ViewedServiceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các user
        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        // Lấy các dịch vụ
        $services = Service::all();

        // Tạo 100 bản ghi lịch sử xem dịch vụ
        for ($i = 0; $i < 100; $i++) {
            // Chọn ngẫu nhiên một user
            $user = $users->random();

            // Chọn ngẫu nhiên một service
            $service = $services->random();

            // Tạo thời gian xem ngẫu nhiên trong 30 ngày gần đây
            $viewedAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            // Tạo bản ghi lịch sử xem
            ViewedServiceLog::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);
        }
    }
}