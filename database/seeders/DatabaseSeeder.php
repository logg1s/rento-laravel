<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChannelNotification;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo role nếu chưa tồn tại
        $roles = ['user', 'provider', 'admin'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['id' => $role]);
        }

        // Tạo danh mục nếu chưa tồn tại
        $categories = ['Dọn dẹp', 'Sửa chữa', 'Nấu ăn', 'Gia sư', 'Mua bán', 'Khác'];
        foreach ($categories as $category) {
            Category::firstOrCreate(['category_name' => $category]);
        }

        // Tạo kênh thông báo nếu chưa tồn tại
        $channels = ['user', 'provider', 'admin'];
        foreach ($channels as $channel) {
            ChannelNotification::firstOrCreate(['id' => $channel]);
        }

        // Chạy seeder theo thứ tự phụ thuộc
        $this->call([
                // Seeder về tỉnh thành
            ProvinceSeeder::class,

                // Seeder về người dùng và vị trí (cần chạy trước các seeder liên quan đến dịch vụ)
            UserSeeder::class,
            LocationSeeder::class,

                // Seeder về dịch vụ (phải chạy sau user và location)
            ServiceSeeder::class,
            PriceSeeder::class, // Cần service trước
            BenefitSeeder::class, // Cần service trước

                // Seeder liên quan đến tương tác người dùng với dịch vụ
            CommentSeeder::class, // Cần user và service trước
            OrderSeeder::class, // Cần user, service và price trước
            FavoriteSeeder::class, // Cần user và service trước
            ViewedServiceLogSeeder::class, // Cần user và service trước

                // Seeder về thông báo và cài đặt người dùng
            NotificationSeeder::class, // Cần user và có thể cần order trước
            ChannelNotificationUserSeeder::class, // Cần user trước
            UserSettingSeeder::class, // Cần user trước

                // Seeder về báo cáo và chặn người dùng
            ReportSeeder::class, // Cần user và service trước
            UserBlockSeeder::class, // Cần user trước
        ]);
    }
}
