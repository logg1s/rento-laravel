<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách các danh mục
        $categories = Category::all();
        $dọnDẹp = $categories->where('category_name', 'Dọn dẹp')->first();
        $sửaChữa = $categories->where('category_name', 'Sửa chữa')->first();
        $nấuĂn = $categories->where('category_name', 'Nấu ăn')->first();
        $giaSư = $categories->where('category_name', 'Gia sư')->first();
        $muaBán = $categories->where('category_name', 'Mua bán')->first();
        $khác = $categories->where('category_name', 'Khác')->first();

        // Lấy danh sách các địa điểm
        $locations = Location::all();

        // Lấy các provider
        $providers = User::whereHas('role', function ($query) {
            $query->where('id', 'provider');
        })->get();

        // Dịch vụ dọn dẹp
        $services[] = [
            'service_name' => 'Dọn dẹp nhà cửa',
            'service_description' => 'Dịch vụ dọn dẹp nhà cửa toàn diện, sạch sẽ, an toàn',
            'user_id' => $providers[0]->id,
            'category_id' => $dọnDẹp->id,
            'location_id' => $locations[0]->id,
        ];

        $services[] = [
            'service_name' => 'Dọn dẹp văn phòng',
            'service_description' => 'Dịch vụ dọn dẹp văn phòng chuyên nghiệp, uy tín',
            'user_id' => $providers[1]->id,
            'category_id' => $dọnDẹp->id,
            'location_id' => $locations[1]->id,
        ];

        $services[] = [
            'service_name' => 'Dọn dẹp sau xây dựng',
            'service_description' => 'Dọn dẹp chuyên nghiệp sau khi hoàn thành công trình xây dựng',
            'user_id' => $providers[2]->id,
            'category_id' => $dọnDẹp->id,
            'location_id' => $locations[2]->id,
        ];

        // Dịch vụ sửa chữa
        $services[] = [
            'service_name' => 'Sửa chữa điện nước',
            'service_description' => 'Dịch vụ sửa chữa điện nước tại nhà, nhanh chóng, uy tín',
            'user_id' => $providers[3]->id,
            'category_id' => $sửaChữa->id,
            'location_id' => $locations[3]->id,
        ];

        $services[] = [
            'service_name' => 'Sửa chữa điều hòa',
            'service_description' => 'Dịch vụ sửa chữa, bảo dưỡng điều hòa mọi loại',
            'user_id' => $providers[4]->id,
            'category_id' => $sửaChữa->id,
            'location_id' => $locations[4]->id,
        ];

        $services[] = [
            'service_name' => 'Sửa chữa đồ gia dụng',
            'service_description' => 'Sửa chữa các thiết bị gia dụng như tủ lạnh, máy giặt, lò vi sóng',
            'user_id' => $providers[5]->id,
            'category_id' => $sửaChữa->id,
            'location_id' => $locations[5]->id,
        ];

        // Dịch vụ nấu ăn
        $services[] = [
            'service_name' => 'Nấu ăn gia đình',
            'service_description' => 'Dịch vụ nấu ăn tại gia đình với các món ăn truyền thống Việt Nam',
            'user_id' => $providers[6]->id,
            'category_id' => $nấuĂn->id,
            'location_id' => $locations[6]->id,
        ];

        $services[] = [
            'service_name' => 'Nấu ăn tiệc',
            'service_description' => 'Dịch vụ nấu ăn cho các buổi tiệc, sinh nhật, hội họp',
            'user_id' => $providers[0]->id,
            'category_id' => $nấuĂn->id,
            'location_id' => $locations[7]->id,
        ];

        $services[] = [
            'service_name' => 'Dạy nấu ăn',
            'service_description' => 'Hướng dẫn nấu các món ăn đặc sản từ các vùng miền',
            'user_id' => $providers[1]->id,
            'category_id' => $nấuĂn->id,
            'location_id' => $locations[8]->id,
        ];

        // Dịch vụ gia sư
        $services[] = [
            'service_name' => 'Gia sư Toán',
            'service_description' => 'Gia sư dạy Toán cho học sinh cấp 1, 2, 3 và ôn thi đại học',
            'user_id' => $providers[2]->id,
            'category_id' => $giaSư->id,
            'location_id' => $locations[9]->id,
        ];

        $services[] = [
            'service_name' => 'Gia sư Tiếng Anh',
            'service_description' => 'Gia sư tiếng Anh giao tiếp và luyện thi IELTS, TOEIC',
            'user_id' => $providers[3]->id,
            'category_id' => $giaSư->id,
            'location_id' => $locations[0]->id,
        ];

        $services[] = [
            'service_name' => 'Gia sư Vật lý',
            'service_description' => 'Gia sư dạy Vật lý cho học sinh cấp 2, 3 và ôn thi đại học',
            'user_id' => $providers[4]->id,
            'category_id' => $giaSư->id,
            'location_id' => $locations[1]->id,
        ];

        // Dịch vụ mua bán
        $services[] = [
            'service_name' => 'Bán đồ nội thất cũ',
            'service_description' => 'Chuyên mua bán các đồ nội thất đã qua sử dụng còn tốt, giá rẻ',
            'user_id' => $providers[5]->id,
            'category_id' => $muaBán->id,
            'location_id' => $locations[2]->id,
        ];

        $services[] = [
            'service_name' => 'Bán đồ điện tử',
            'service_description' => 'Mua bán điện thoại, máy tính, thiết bị điện tử cũ mới',
            'user_id' => $providers[6]->id,
            'category_id' => $muaBán->id,
            'location_id' => $locations[3]->id,
        ];

        $services[] = [
            'service_name' => 'Bán sách cũ',
            'service_description' => 'Mua bán sách cũ, sách hiếm với giá hợp lý',
            'user_id' => $providers[0]->id,
            'category_id' => $muaBán->id,
            'location_id' => $locations[4]->id,
        ];

        // Dịch vụ khác
        $services[] = [
            'service_name' => 'Dịch vụ trông trẻ',
            'service_description' => 'Trông giữ trẻ theo giờ, theo ngày, có kinh nghiệm và yêu trẻ',
            'user_id' => $providers[1]->id,
            'category_id' => $khác->id,
            'location_id' => $locations[5]->id,
        ];

        $services[] = [
            'service_name' => 'Dịch vụ chăm sóc người già',
            'service_description' => 'Chăm sóc người già, người bệnh tại nhà chuyên nghiệp',
            'user_id' => $providers[2]->id,
            'category_id' => $khác->id,
            'location_id' => $locations[6]->id,
        ];

        $services[] = [
            'service_name' => 'Dịch vụ đưa đón học sinh',
            'service_description' => 'Đưa đón học sinh các cấp an toàn, đúng giờ',
            'user_id' => $providers[3]->id,
            'category_id' => $khác->id,
            'location_id' => $locations[7]->id,
        ];

        $services[] = [
            'service_name' => 'Dịch vụ thiết kế web',
            'service_description' => 'Thiết kế website cho cá nhân và doanh nghiệp',
            'user_id' => $providers[4]->id,
            'category_id' => $khác->id,
            'location_id' => $locations[8]->id,
        ];

        // Lưu dịch vụ vào database
        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }
    }
}