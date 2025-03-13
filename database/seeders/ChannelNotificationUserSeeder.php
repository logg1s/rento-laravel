<?php

namespace Database\Seeders;

use App\Models\ChannelNotification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelNotificationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả người dùng
        $users = User::all();

        // Lấy tất cả kênh thông báo
        $channels = ChannelNotification::all();

        // Gán kênh thông báo cho mỗi người dùng
        foreach ($users as $user) {
            // Xác định vai trò của người dùng
            $roles = $user->role->pluck('id')->toArray();

            if (in_array('admin', $roles)) {
                // Admin nhận tất cả các kênh thông báo
                foreach ($channels as $channel) {
                    DB::table('channel_notification_user')->insert([
                        'user_id' => $user->id,
                        'channel_notification_id' => $channel->id,

                    ]);
                }
            } elseif (in_array('provider', $roles)) {
                // Provider nhận thông báo provider và user
                $providerChannels = $channels->whereIn('id', ['provider', 'user']);
                foreach ($providerChannels as $channel) {
                    DB::table('channel_notification_user')->insert([
                        'user_id' => $user->id,
                        'channel_notification_id' => $channel->id,

                    ]);
                }
            } elseif (in_array('user', $roles)) {
                // User thông thường chỉ nhận thông báo user
                $userChannel = $channels->where('id', 'user')->first();
                DB::table('channel_notification_user')->insert([
                    'user_id' => $user->id,
                    'channel_notification_id' => $userChannel->id,

                ]);
            }
        }
    }
}