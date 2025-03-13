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
        // Lấy danh sách user thông thường
        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        // Lấy danh sách nhà cung cấp
        $providers = User::whereHas('role', function ($query) {
            $query->where('id', 'provider');
        })->get();

        // Các lý do báo cáo
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

        // Các loại entity có thể báo cáo
        $entityTypes = [
            'service',
            'user',
            'message',
            'comment',
        ];

        // Tạo 20 báo cáo mẫu
        for ($i = 0; $i < 20; $i++) {
            // Chọn ngẫu nhiên user báo cáo
            $reporter = $users->random();

            // Chọn ngẫu nhiên một nhà cung cấp bị báo cáo
            $reportedUser = $providers->random();

            // Chọn ngẫu nhiên loại entity được báo cáo
            $entityType = $entityTypes[array_rand($entityTypes)];

            // Khởi tạo entity_id
            $entityId = null;

            // Tùy thuộc vào loại entity, lấy một ID hợp lệ
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
                    // Đặt một ID giả cho message và comment
                    $entityId = rand(1, 100);
                    break;
            }

            // Chọn ngẫu nhiên một lý do báo cáo
            $reason = $reportReasons[array_rand($reportReasons)];

            // Tạo ngẫu nhiên trạng thái báo cáo
            $status = ['pending', 'reviewed', 'rejected', 'resolved'][rand(0, 3)];

            // Tạo ngẫu nhiên thời gian tạo báo cáo trong 60 ngày gần đây
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            // Tạo báo cáo
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