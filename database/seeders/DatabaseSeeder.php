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
        // User::factory(10)->create();

        Role::insert([['id' => 'user'], ['id' => 'provider']]);
        Category::insert([['category_name' => 'Dọn dẹp'], ['category_name' => 'Sửa chữa']]);
        ChannelNotification::insert([['id' => 'user'], ['id' => 'provider'], ['id' => 'admin']]);
    }
}
