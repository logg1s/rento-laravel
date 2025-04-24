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

        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        $services = Service::all();

        foreach ($users as $user) {
            $favoriteCount = rand(3, 8);

            $favoriteServices = $services->random($favoriteCount);

            foreach ($favoriteServices as $service) {
                DB::table('favorite')->insert([
                    'user_id' => $user->id,
                    'service_id' => $service->id,

                ]);
            }
        }
    }
}