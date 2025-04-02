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

        $services = Service::all();


        $users = User::whereHas('role', function ($query) {
            $query->where('id', 'user');
        })->get();


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


        $neutralComments = [
            'Dịch vụ khá ổn, có thể cải thiện thêm.',
            'Chất lượng dịch vụ tạm được.',
            'Không có gì nổi bật nhưng cũng không tệ.',
            'Dịch vụ đạt yêu cầu cơ bản.',
            'Giá cả hợp lý cho dịch vụ cung cấp.',
        ];


        $negativeComments = [
            'Dịch vụ chưa đáp ứng được mong đợi.',
            'Cần cải thiện thêm về thái độ phục vụ.',
            'Thời gian thực hiện lâu hơn dự kiến.',
            'Chất lượng không tương xứng với giá tiền.',
            'Tôi cảm thấy hơi thất vọng về kết quả.',
        ];


        foreach ($services as $service) {

            $commentCount = rand(3, 8);

            for ($i = 0; $i < $commentCount; $i++) {

                $user = $users->random();


                $rating = rand(1, 5);


                if ($rating >= 4) {
                    $comment = $positiveComments[array_rand($positiveComments)];
                } elseif ($rating >= 3) {
                    $comment = $neutralComments[array_rand($neutralComments)];
                } else {
                    $comment = $negativeComments[array_rand($negativeComments)];
                }


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