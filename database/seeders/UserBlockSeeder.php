<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = User::all();


        for ($i = 0; $i < 10; $i++) {

            $sourceUser = $users->random();


            $targetUsers = $users->where('id', '!=', $sourceUser->id);


            if ($targetUsers->count() > 0) {

                $targetUser = $targetUsers->random();


                $existingBlock = DB::table('user_blocks')
                    ->where('user_id', $sourceUser->id)
                    ->where('blocked_user_id', $targetUser->id)
                    ->exists();


                if (!$existingBlock) {
                    DB::table('user_blocks')->insert([
                        'user_id' => $sourceUser->id,
                        'blocked_user_id' => $targetUser->id,
                        'reason' => 'Lý do chặn: ' . ['Spam', 'Quấy rối', 'Không phù hợp', 'Hành vi xấu', 'Khác'][rand(0, 4)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}