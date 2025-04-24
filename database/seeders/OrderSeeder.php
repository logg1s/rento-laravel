<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Order;
use App\Models\Price;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
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


        for ($i = 0; $i < 30; $i++) {

            $user = $users->random();


            $service = $services->random();


            $price = Price::where('service_id', $service->id)->inRandomOrder()->first();


            $statusRandom = rand(1, 100);
            if ($statusRandom <= 20) {
                $status = StatusEnum::PENDING;
                $timeStart = null;
                $cancelBy = null;
            } elseif ($statusRandom <= 40) {
                $status = StatusEnum::WORKING;
                $timeStart = Carbon::now()->addDays(rand(1, 7));
                $cancelBy = null;
            } elseif ($statusRandom <= 60) {
                $status = StatusEnum::SUCCESS;
                $timeStart = Carbon::now()->subDays(rand(1, 30));
                $cancelBy = null;
            } elseif ($statusRandom <= 100) {
                $status = StatusEnum::CANCELLED;
                $timeStart = null;
                $cancelBy = rand(0, 1) ? $user->id : $service->user_id;
            }

            Order::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'price_id' => $price->id,
                'price_final_value' => $price->price_value,
                'status' => $status,
                'address' => "Địa chỉ đặt hàng số {$i}, " . $service->location->location_name,
                'phone_number' => '09' . rand(10000000, 99999999),
                'time_start' => $timeStart,
                'message' => "Yêu cầu dịch vụ số {$i}. Vui lòng liên hệ trước khi đến.",
                'cancel_by' => $cancelBy,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
            ]);
        }
    }
}