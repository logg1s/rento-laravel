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

        $roles = ['user', 'provider', 'admin'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['id' => $role]);
        }


        $categories = ['Dọn dẹp', 'Sửa chữa', 'Nấu ăn', 'Gia sư', 'Mua bán', 'Khác'];
        foreach ($categories as $category) {
            Category::firstOrCreate(['category_name' => $category]);
        }


        $channels = ['user', 'provider', 'admin'];
        foreach ($channels as $channel) {
            ChannelNotification::firstOrCreate(['id' => $channel]);
        }


        $this->call([

            ProvinceSeeder::class,


            UserSeeder::class,
            LocationSeeder::class,


            ServiceSeeder::class,
            PriceSeeder::class,
            BenefitSeeder::class,


            CommentSeeder::class,
            OrderSeeder::class,
            FavoriteSeeder::class,
            ViewedServiceLogSeeder::class,

            NotificationSeeder::class,
            ChannelNotificationUserSeeder::class,
            UserSettingSeeder::class,

            ReportSeeder::class,
            UserBlockSeeder::class,
        ]);
    }
}
