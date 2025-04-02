<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        $providers = User::whereHas('role', function ($query) {
            $query->where('id', 'provider');
        })->get();

        $reportReasons = [
            'Dịch vụ không đúng mô tả',
            'Nhà cung cấp không chuyên nghiệp',
            'Giá cả không hợp lý',
            'Thông tin sai lệch',
            'Chất lượng dịch vụ kém',
            'Có dấu hiệu lừa đảo',
            'Vi phạm điều khoản sử dụng',
            'Nội dung không phù hợp',
            'Không thể liên lạc với nhà cung cấp',
            'Dịch vụ đã ngừng hoạt động',
        ];

        $entityTypes = [
            'service',
            'user',
            'message',
            'comment',
        ];

        for ($i = 0; $i < 20; $i++) {
            $reporter = $users->random();


            $reportedUser = $providers->random();

            $entityType = $entityTypes[array_rand($entityTypes)];

            $entityId = null;

            switch ($entityType) {
                case 'service':
                    $service = Service::inRandomOrder()->first();
                    $entityId = $service ? $service->id : '1';
                    break;
                case 'user':
                    $entityId = $reportedUser->id;
                    break;
                case 'message':
                case 'comment':
                    $entityId = rand(1, 100);
                    break;
            }

            $reason = $reportReasons[array_rand($reportReasons)];

            $status = ['pending', 'reviewed', 'rejected', 'resolved'][rand(0, 3)];

            $createdAt = Carbon::now()->subDays(rand(1, 60));

            Report::create([
                'reporter_id' => $reporter->id,
                'reported_user_id' => $reportedUser->id,
                'entity_type' => $entityType,
                'entity_id' => (string) $entityId,
                'reason' => $reason,
                'status' => $status,
                'admin_notes' => $status !== 'pending' ? 'Đã xử lý báo cáo này' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}