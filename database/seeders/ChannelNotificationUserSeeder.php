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
        $users = User::all();

        $channels = ChannelNotification::all();

        foreach ($users as $user) {
            $roles = $user->role->pluck('id')->toArray();

            if (in_array('admin', $roles)) {

                foreach ($channels as $channel) {
                    DB::table('channel_notification_user')->insert([
                        'user_id' => $user->id,
                        'channel_notification_id' => $channel->id,

                    ]);
                }
            } elseif (in_array('provider', $roles)) {
                $providerChannels = $channels->whereIn('id', ['provider', 'user']);
                foreach ($providerChannels as $channel) {
                    DB::table('channel_notification_user')->insert([
                        'user_id' => $user->id,
                        'channel_notification_id' => $channel->id,

                    ]);
                }
            } elseif (in_array('user', $roles)) {
                $userChannel = $channels->where('id', 'user')->first();
                DB::table('channel_notification_user')->insert([
                    'user_id' => $user->id,
                    'channel_notification_id' => $userChannel->id,

                ]);
            }
        }
    }
}