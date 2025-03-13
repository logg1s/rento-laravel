<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy users là khách hàng
        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        // Lấy các dịch vụ
        $services = Service::all();

        // Mỗi user sẽ có 3-8 dịch vụ yêu thích
        foreach ($users as $user) {
            // Chọn ngẫu nhiên số lượng dịch vụ yêu thích
            $favoriteCount = rand(3, 8);

            // Chọn ngẫu nhiên các dịch vụ để thêm vào yêu thích
            $favoriteServices = $services->random($favoriteCount);

            // Thêm vào bảng service_favorite
            foreach ($favoriteServices as $service) {
                DB::table('favorite')->insert([
                    'user_id' => $user->id,
                    'service_id' => $service->id,

                ]);
            }
        }
    }
}