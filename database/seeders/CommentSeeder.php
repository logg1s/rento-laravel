<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả dịch vụ
        $services = Service::all();

        // Lấy danh sách user thông thường (không phải provider)
        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();

        // Mảng các bình luận tích cực
        $positiveComments = [
            'Dịch vụ rất tốt, tôi rất hài lòng!',
            'Nhân viên chuyên nghiệp và nhiệt tình.',
            'Giá cả hợp lý cho chất lượng dịch vụ.',
            'Tôi sẽ quay lại sử dụng dịch vụ trong tương lai.',
            'Làm việc nhanh chóng và hiệu quả.',
            'Đúng thời gian, đúng cam kết.',
            'Rất hài lòng với kết quả.',
            'Dịch vụ vượt quá mong đợi của tôi.',
            'Chất lượng dịch vụ xứng đáng với giá tiền.',
            'Tôi đã giới thiệu dịch vụ này cho bạn bè.',
        ];

        // Mảng các bình luận trung tính
        $neutralComments = [
            'Dịch vụ khá ổn, có thể cải thiện thêm.',
            'Chất lượng dịch vụ tạm được.',
            'Không có gì nổi bật nhưng cũng không tệ.',
            'Dịch vụ đạt yêu cầu cơ bản.',
            'Giá cả hợp lý cho dịch vụ cung cấp.',
        ];

        // Mảng các bình luận tiêu cực
        $negativeComments = [
            'Dịch vụ chưa đáp ứng được mong đợi.',
            'Cần cải thiện thêm về thái độ phục vụ.',
            'Thời gian thực hiện lâu hơn dự kiến.',
            'Chất lượng không tương xứng với giá tiền.',
            'Tôi cảm thấy hơi thất vọng về kết quả.',
        ];

        // Tạo bình luận cho mỗi dịch vụ
        foreach ($services as $service) {
            // Tạo từ 3-8 bình luận cho mỗi dịch vụ
            $commentCount = rand(3, 8);

            for ($i = 0; $i < $commentCount; $i++) {
                // Chọn ngẫu nhiên một user
                $user = $users->random();

                // Chọn ngẫu nhiên rating từ 1-5
                $rating = rand(1, 5);

                // Chọn bình luận dựa trên rating
                if ($rating >= 4) {
                    $comment = $positiveComments[array_rand($positiveComments)];
                } elseif ($rating >= 3) {
                    $comment = $neutralComments[array_rand($neutralComments)];
                } else {
                    $comment = $negativeComments[array_rand($negativeComments)];
                }

                // Tạo comment
                Comment::create([
                    'rate' => $rating,
                    'comment_body' => $comment,
                    'user_id' => $user->id,
                    'service_id' => $service->id
                ]);
            }
        }
    }
}