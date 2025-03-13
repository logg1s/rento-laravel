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
        // Lấy danh sách users
        $users = User::all();

        // Tạo ngẫu nhiên 10 bản ghi chặn user
        for ($i = 0; $i < 10; $i++) {
            // Chọn ngẫu nhiên user nguồn (người chặn)
            $sourceUser = $users->random();

            // Loại bỏ user nguồn khỏi danh sách để chọn user đích (người bị chặn)
            $targetUsers = $users->where('id', '!=', $sourceUser->id);

            // Nếu vẫn còn user để chặn
            if ($targetUsers->count() > 0) {
                // Chọn ngẫu nhiên một user để chặn
                $targetUser = $targetUsers->random();

                // Kiểm tra xem bản ghi chặn đã tồn tại chưa
                $existingBlock = DB::table('user_blocks')
                    ->where('user_id', $sourceUser->id)
                    ->where('blocked_user_id', $targetUser->id)
                    ->exists();

                // Nếu chưa tồn tại thì tạo mới
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