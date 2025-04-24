<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $orders = Order::all();


        $orderNotifications = [
            'Đơn hàng của bạn đã được xác nhận',
            'Đơn hàng của bạn đã hoàn tất',
            'Đơn hàng của bạn đã bị hủy',
            'Đơn hàng mới cần xác nhận',
            'Nhắc nhở: Đơn hàng sắp đến thời gian thực hiện',
        ];


        $systemNotifications = [
            'Chào mừng bạn đến với Rento!',
            'Cập nhật chính sách bảo mật mới',
            'Khuyến mãi đặc biệt cho thành viên',
            'Tính năng mới đã được cập nhật',
            'Đánh giá dịch vụ gần đây nhất của bạn',
        ];


        foreach ($orders as $order) {

            Notification::create([
                'user_id' => $order->user_id,
                'title' => $orderNotifications[array_rand($orderNotifications)],
                'body' => "Thông tin chi tiết về đơn hàng #{$order->id} cho dịch vụ {$order->service->service_name}. Vui lòng kiểm tra trong phần đơn hàng.",
                'data' => json_encode(['order_id' => $order->id]),
                'created_at' => $order->created_at->addHours(rand(1, 24)),
                'is_read' => rand(0, 1),
            ]);


            Notification::create([
                'user_id' => $order->service->user_id,
                'title' => "Đơn đặt hàng mới #" . $order->id,
                'body' => "Khách hàng {$order->user->name} đã đặt dịch vụ {$order->service->service_name}. Vui lòng kiểm tra và xác nhận đơn hàng.",
                'data' => json_encode(['order_id' => $order->id]),
                'created_at' => $order->created_at->addMinutes(rand(1, 30)),
                'is_read' => rand(0, 1),
            ]);
        }


        $users = User::all();
        foreach ($users as $user) {
            $notificationCount = rand(2, 4);
            for ($i = 0; $i < $notificationCount; $i++) {
                $title = $systemNotifications[array_rand($systemNotifications)];

                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'body' => "Thông báo hệ thống: {$title}. Nhấp để xem chi tiết.",
                    'data' => json_encode(['system' => true]),
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'is_read' => rand(0, 1),
                ]);
            }
        }
    }
}