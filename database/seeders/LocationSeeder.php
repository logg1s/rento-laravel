<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Province;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các province đã được tạo từ ProvinceSeeder
        $provinces = Province::all();

        // Tạo các địa điểm ở Hà Nội
        $hanoi = $provinces->where('code', 'HN')->first();
        $locations = [
            [
                'lng' => 105.8342,
                'lat' => 21.0278,
                'location_name' => 'Hoàn Kiếm, Hà Nội',
                'real_location_name' => 'Quận Hoàn Kiếm',
                'province_id' => $hanoi->id,
                'address' => 'Phố Hàng Đào, Hoàn Kiếm, Hà Nội'
            ],
            [
                'lng' => 105.8544,
                'lat' => 21.0380,
                'location_name' => 'Hai Bà Trưng, Hà Nội',
                'real_location_name' => 'Quận Hai Bà Trưng',
                'province_id' => $hanoi->id,
                'address' => 'Phố Bạch Mai, Hai Bà Trưng, Hà Nội'
            ],
            [
                'lng' => 105.7772,
                'lat' => 21.0227,
                'location_name' => 'Cầu Giấy, Hà Nội',
                'real_location_name' => 'Quận Cầu Giấy',
                'province_id' => $hanoi->id,
                'address' => 'Phố Trần Thái Tông, Cầu Giấy, Hà Nội'
            ],
        ];

        // Tạo các địa điểm ở TP HCM
        $hcm = $provinces->where('code', 'HCM')->first();
        $locations = array_merge($locations, [
            [
                'lng' => 106.6297,
                'lat' => 10.8231,
                'location_name' => 'Quận 1, TP Hồ Chí Minh',
                'real_location_name' => 'Quận 1',
                'province_id' => $hcm->id,
                'address' => 'Đường Nguyễn Huệ, Quận 1, TP Hồ Chí Minh'
            ],
            [
                'lng' => 106.6144,
                'lat' => 10.8031,
                'location_name' => 'Quận 3, TP Hồ Chí Minh',
                'real_location_name' => 'Quận 3',
                'province_id' => $hcm->id,
                'address' => 'Đường Võ Văn Tần, Quận 3, TP Hồ Chí Minh'
            ],
            [
                'lng' => 106.7018,
                'lat' => 10.7756,
                'location_name' => 'Quận 7, TP Hồ Chí Minh',
                'real_location_name' => 'Quận 7',
                'province_id' => $hcm->id,
                'address' => 'Phú Mỹ Hưng, Quận 7, TP Hồ Chí Minh'
            ],
        ]);

        // Tạo các địa điểm ở Đà Nẵng
        $danang = $provinces->where('code', 'DN')->first();
        $locations = array_merge($locations, [
            [
                'lng' => 108.2208,
                'lat' => 16.0544,
                'location_name' => 'Hải Châu, Đà Nẵng',
                'real_location_name' => 'Quận Hải Châu',
                'province_id' => $danang->id,
                'address' => 'Đường Bạch Đằng, Hải Châu, Đà Nẵng'
            ],
            [
                'lng' => 108.2480,
                'lat' => 16.0677,
                'location_name' => 'Sơn Trà, Đà Nẵng',
                'real_location_name' => 'Quận Sơn Trà',
                'province_id' => $danang->id,
                'address' => 'Đường Ngô Quyền, Sơn Trà, Đà Nẵng'
            ],
        ]);

        // Tạo các địa điểm ở Cần Thơ
        $cantho = $provinces->where('code', 'CT')->first();
        $locations = array_merge($locations, [
            [
                'lng' => 105.7874,
                'lat' => 10.0452,
                'location_name' => 'Ninh Kiều, Cần Thơ',
                'real_location_name' => 'Quận Ninh Kiều',
                'province_id' => $cantho->id,
                'address' => 'Đường Hòa Bình, Ninh Kiều, Cần Thơ'
            ],
            [
                'lng' => 105.7608,
                'lat' => 10.0238,
                'location_name' => 'Bình Thủy, Cần Thơ',
                'real_location_name' => 'Quận Bình Thủy',
                'province_id' => $cantho->id,
                'address' => 'Đường Cách Mạng Tháng 8, Bình Thủy, Cần Thơ'
            ],
        ]);

        // Tạo các địa điểm ở Hải Phòng
        $haiphong = $provinces->where('code', 'HP')->first();
        $locations = array_merge($locations, [
            [
                'lng' => 106.6880,
                'lat' => 20.8449,
                'location_name' => 'Hồng Bàng, Hải Phòng',
                'real_location_name' => 'Quận Hồng Bàng',
                'province_id' => $haiphong->id,
                'address' => 'Đường Điện Biên Phủ, Hồng Bàng, Hải Phòng'
            ],
            [
                'lng' => 106.7017,
                'lat' => 20.8298,
                'location_name' => 'Lê Chân, Hải Phòng',
                'real_location_name' => 'Quận Lê Chân',
                'province_id' => $haiphong->id,
                'address' => 'Đường Trần Nguyên Hãn, Lê Chân, Hải Phòng'
            ],
        ]);

        // Lưu tất cả các địa điểm vào database
        foreach ($locations as $locationData) {
            Location::create($locationData);
        }
    }
}