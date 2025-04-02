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

        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        $services = Service::all();


        for ($i = 0; $i < 100; $i++) {

            $user = $users->random();


            $service = $services->random();


            $viewedAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));


            ViewedServiceLog::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);
        }
    }
}