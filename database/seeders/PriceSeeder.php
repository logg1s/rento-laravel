<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả dịch vụ
        $services = Service::all();

        foreach ($services as $service) {
            // Tạo từ 2-3 mức giá cho mỗi dịch vụ
            $priceCount = rand(2, 3);

            if ($service->category->category_name === 'Dọn dẹp') {
                // Giá dịch vụ dọn dẹp
                $priceNames = ['Gói 2 giờ', 'Gói 4 giờ', 'Gói 8 giờ'];
                $priceValues = [200000, 350000, 600000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-50000, 50000), // Thêm một chút dao động
                        'service_id' => $service->id
                    ]);
                }
            } else if ($service->category->category_name === 'Sửa chữa') {
                // Giá dịch vụ sửa chữa
                $priceNames = ['Gói cơ bản', 'Gói trung bình', 'Gói cao cấp'];
                $priceValues = [150000, 300000, 500000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-30000, 30000),
                        'service_id' => $service->id
                    ]);
                }
            } else if ($service->category->category_name === 'Nấu ăn') {
                // Giá dịch vụ nấu ăn
                $priceNames = ['Bữa đơn', 'Gói 3 bữa', 'Gói tuần'];
                $priceValues = [250000, 600000, 1500000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-100000, 100000),
                        'service_id' => $service->id
                    ]);
                }
            } else if ($service->category->category_name === 'Gia sư') {
                // Giá dịch vụ gia sư
                $priceNames = ['1 buổi', 'Gói 5 buổi', 'Gói 10 buổi'];
                $priceValues = [200000, 900000, 1800000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-50000, 50000),
                        'service_id' => $service->id
                    ]);
                }
            } else if ($service->category->category_name === 'Mua bán') {
                // Giá dịch vụ mua bán
                $priceNames = ['Sản phẩm phổ thông', 'Sản phẩm trung cấp', 'Sản phẩm cao cấp'];
                $priceValues = [500000, 1500000, 3000000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-200000, 200000),
                        'service_id' => $service->id
                    ]);
                }
            } else {
                // Giá dịch vụ khác
                $priceNames = ['Gói tiêu chuẩn', 'Gói nâng cao', 'Gói chuyên nghiệp'];
                $priceValues = [300000, 500000, 800000];

                for ($i = 0; $i < $priceCount; $i++) {
                    Price::create([
                        'price_name' => $priceNames[$i],
                        'price_value' => $priceValues[$i] + rand(-100000, 100000),
                        'service_id' => $service->id
                    ]);
                }
            }
        }
    }
}