<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['name' => 'Hà Nội', 'code' => 'HN'],
            ['name' => 'Hồ Chí Minh', 'code' => 'HCM'],
            ['name' => 'Đà Nẵng', 'code' => 'DN'],
            ['name' => 'Hải Phòng', 'code' => 'HP'],
            ['name' => 'Cần Thơ', 'code' => 'CT'],
            ['name' => 'An Giang', 'code' => 'AG'],
            ['name' => 'Bà Rịa - Vũng Tàu', 'code' => 'BRVT'],
            ['name' => 'Bắc Giang', 'code' => 'BG'],
            ['name' => 'Bắc Kạn', 'code' => 'BK'],
            ['name' => 'Bạc Liêu', 'code' => 'BL'],
            ['name' => 'Bắc Ninh', 'code' => 'BN'],
            ['name' => 'Bến Tre', 'code' => 'BT'],
            ['name' => 'Bình Định', 'code' => 'BD'],
            ['name' => 'Bình Dương', 'code' => 'BDG'],
            ['name' => 'Bình Phước', 'code' => 'BP'],
            ['name' => 'Bình Thuận', 'code' => 'BTN'],
            ['name' => 'Cà Mau', 'code' => 'CM'],
            ['name' => 'Cao Bằng', 'code' => 'CB'],
            ['name' => 'Đắk Lắk', 'code' => 'DL'],
            ['name' => 'Đắk Nông', 'code' => 'DNO'],
            ['name' => 'Điện Biên', 'code' => 'DB'],
            ['name' => 'Đồng Nai', 'code' => 'DNA'],
            ['name' => 'Đồng Tháp', 'code' => 'DT'],
            ['name' => 'Gia Lai', 'code' => 'GL'],
            ['name' => 'Hà Giang', 'code' => 'HG'],
            ['name' => 'Hà Nam', 'code' => 'HNM'],
            ['name' => 'Hà Tĩnh', 'code' => 'HT'],
            ['name' => 'Hải Dương', 'code' => 'HD'],
            ['name' => 'Hậu Giang', 'code' => 'HGI'],
            ['name' => 'Hòa Bình', 'code' => 'HB'],
            ['name' => 'Hưng Yên', 'code' => 'HY'],
            ['name' => 'Khánh Hòa', 'code' => 'KH'],
            ['name' => 'Kiên Giang', 'code' => 'KG'],
            ['name' => 'Kon Tum', 'code' => 'KT'],
            ['name' => 'Lai Châu', 'code' => 'LC'],
            ['name' => 'Lâm Đồng', 'code' => 'LD'],
            ['name' => 'Lạng Sơn', 'code' => 'LS'],
            ['name' => 'Lào Cai', 'code' => 'LCA'],
            ['name' => 'Long An', 'code' => 'LA'],
            ['name' => 'Nam Định', 'code' => 'ND'],
            ['name' => 'Nghệ An', 'code' => 'NA'],
            ['name' => 'Ninh Bình', 'code' => 'NB'],
            ['name' => 'Ninh Thuận', 'code' => 'NT'],
            ['name' => 'Phú Thọ', 'code' => 'PT'],
            ['name' => 'Phú Yên', 'code' => 'PY'],
            ['name' => 'Quảng Bình', 'code' => 'QB'],
            ['name' => 'Quảng Nam', 'code' => 'QNA'],
            ['name' => 'Quảng Ngãi', 'code' => 'QNG'],
            ['name' => 'Quảng Ninh', 'code' => 'QNI'],
            ['name' => 'Quảng Trị', 'code' => 'QT'],
            ['name' => 'Sóc Trăng', 'code' => 'ST'],
            ['name' => 'Sơn La', 'code' => 'SL'],
            ['name' => 'Tây Ninh', 'code' => 'TN'],
            ['name' => 'Thái Bình', 'code' => 'TB'],
            ['name' => 'Thái Nguyên', 'code' => 'TNG'],
            ['name' => 'Thanh Hóa', 'code' => 'TH'],
            ['name' => 'Thừa Thiên Huế', 'code' => 'TTH'],
            ['name' => 'Tiền Giang', 'code' => 'TG'],
            ['name' => 'Trà Vinh', 'code' => 'TV'],
            ['name' => 'Tuyên Quang', 'code' => 'TQ'],
            ['name' => 'Vĩnh Long', 'code' => 'VL'],
            ['name' => 'Vĩnh Phúc', 'code' => 'VP'],
            ['name' => 'Yên Bái', 'code' => 'YB'],
        ];


        foreach ($provinces as $provinceData) {
            Province::updateOrCreate(
                ['code' => $provinceData['code']],
                $provinceData
            );
        }
    }
}