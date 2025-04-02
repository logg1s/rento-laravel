<?php

namespace Database\Seeders;

use App\Models\Benefit;
use App\Models\Service;
use Illuminate\Database\Seeder;

class BenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $services = Service::all();


        $benefitsByCategory = [
            'Dọn dẹp' => [
                'Đảm bảo sạch sẽ 100%',
                'Hóa chất làm sạch an toàn',
                'Đúng hẹn, đúng giờ',
                'Nhân viên được đào tạo chuyên nghiệp',
                'Bảo hành dịch vụ'
            ],
            'Sửa chữa' => [
                'Bảo hành dài hạn',
                'Tư vấn miễn phí',
                'Linh kiện chính hãng',
                'Kỹ thuật viên có chứng chỉ',
                'Hỗ trợ 24/7'
            ],
            'Nấu ăn' => [
                'Nguyên liệu tươi sạch',
                'Thực đơn đa dạng',
                'Đầu bếp chuyên nghiệp',
                'Phục vụ tận nơi',
                'Tùy chỉnh theo yêu cầu'
            ],
            'Gia sư' => [
                'Giáo viên có kinh nghiệm',
                'Giáo trình chuẩn',
                'Lịch học linh hoạt',
                'Báo cáo tiến độ định kỳ',
                'Bảo đảm kết quả học tập'
            ],
            'Mua bán' => [
                'Giao hàng tận nơi',
                'Kiểm tra hàng khi nhận',
                'Bảo hành sản phẩm',
                'Đổi trả trong 7 ngày',
                'Tư vấn sản phẩm chuyên nghiệp'
            ],
            'Khác' => [
                'Dịch vụ chuyên nghiệp',
                'Nhân viên được đào tạo',
                'Giá cả hợp lý',
                'Dịch vụ tận tâm',
                'Hỗ trợ 24/7'
            ]
        ];


        foreach ($services as $service) {
            $categoryName = $service->category->category_name;
            $benefits = $benefitsByCategory[$categoryName] ?? $benefitsByCategory['Khác'];


            $benefitCount = rand(3, 5);
            $selectedBenefits = array_slice($benefits, 0, $benefitCount);

            foreach ($selectedBenefits as $benefitName) {
                Benefit::create([
                    'service_id' => $service->id,
                    'benefit_name' => $benefitName
                ]);
            }
        }
    }
}