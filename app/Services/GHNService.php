<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    public function productWeight(): int
    {
        return (int) config('services.ghn.default_weight', 200);
    }

    public function packageParameters(int $totalWeight): array
    {
        return [
            'weight' => max(100, $totalWeight),
            'length' => 20,
            'width' => 15,
            'height' => 10,
            'service_type_id' => 2,
        ];
    }

    public function calculateFee(array $data): array
    {
        $token = config('services.ghn.token');
        $shopId = config('services.ghn.shop_id');
        $endpoint = config('services.ghn.endpoint_fee', 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee');

        if (!empty($token) && !empty($shopId)) {
            try {
                $response = Http::withHeaders([
                    'Token' => $token,
                    'ShopId' => (string) $shopId,
                ])->withoutVerifying()->timeout(8)->post($endpoint, $data);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Throwable $e) {
                Log::warning('GHN calculateFee failed: ' . $e->getMessage());
            }
        }

        // Mặc định phí ship chuẩn giả lập cho môi trường phát triển
        return [
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'total' => 30000,
                'service_fee' => 30000,
            ],
        ];
    }

    public function getProvinces(): array
    {
        return Cache::remember('ghn_provinces', 86400, function () {
            $token = config('services.ghn.token');
            if (!empty($token)) {
                try {
                    $response = Http::withHeaders([
                        'Token' => $token,
                        'Content-Type' => 'application/json',
                    ])->withoutVerifying()->timeout(8)->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province');

                    if ($response->successful()) {
                        $data = $response->json('data') ?? [];
                        $clean = [];
                        foreach ($data as $p) {
                            $name = $p['ProvinceName'] ?? '';
                            if (str_contains($name, 'Test') || str_contains($name, 'Alert') || $name === 'Hà Nội 02') {
                                continue;
                            }
                            $clean[] = [
                                'id' => (int) $p['ProvinceID'],
                                'name' => $name,
                            ];
                        }
                        usort($clean, fn($a, $b) => strcmp($a['name'], $b['name']));
                        if (!empty($clean)) {
                            return $clean;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('GHN getProvinces failed: ' . $e->getMessage());
                }
            }

            return $this->defaultProvinces();
        });
    }

    public function getDistricts(int $provinceId): array
    {
        return Cache::remember("ghn_districts_{$provinceId}", 86400, function () use ($provinceId) {
            $token = config('services.ghn.token');
            if (!empty($token)) {
                try {
                    $response = Http::withHeaders([
                        'Token' => $token,
                        'Content-Type' => 'application/json',
                    ])->withoutVerifying()->timeout(8)->post('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district', [
                        'province_id' => $provinceId,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json('data') ?? [];
                        $clean = [];
                        foreach ($data as $d) {
                            $clean[] = [
                                'id' => (int) $d['DistrictID'],
                                'name' => $d['DistrictName'],
                            ];
                        }
                        usort($clean, fn($a, $b) => strcmp($a['name'], $b['name']));
                        if (!empty($clean)) {
                            return $clean;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('GHN getDistricts failed: ' . $e->getMessage());
                }
            }

            return $this->defaultDistricts($provinceId);
        });
    }

    public function getWards(int $districtId): array
    {
        return Cache::remember("ghn_wards_{$districtId}", 86400, function () use ($districtId) {
            $token = config('services.ghn.token');
            if (!empty($token)) {
                try {
                    $response = Http::withHeaders([
                        'Token' => $token,
                        'Content-Type' => 'application/json',
                    ])->withoutVerifying()->timeout(8)->post('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward', [
                        'district_id' => $districtId,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json('data') ?? [];
                        $clean = [];
                        foreach ($data as $w) {
                            // Bỏ qua các phường/xã bị ngưng hoạt động trong GHN sandbox
                            if ((isset($w['Status']) && $w['Status'] == 3) || (isset($w['SupportType']) && $w['SupportType'] == 0)) {
                                continue;
                            }
                            $clean[] = [
                                'code' => (string) $w['WardCode'],
                                'name' => $w['WardName'],
                            ];
                        }

                        // Nếu bộ lọc loại hết do dữ liệu sandbox, giữ lại danh sách gốc để người dùng vẫn chọn được
                        if (empty($clean) && !empty($data)) {
                            foreach ($data as $w) {
                                $clean[] = [
                                    'code' => (string) $w['WardCode'],
                                    'name' => $w['WardName'],
                                ];
                            }
                        }

                        usort($clean, fn($a, $b) => strcmp($a['name'], $b['name']));
                        if (!empty($clean)) {
                            return $clean;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('GHN getWards failed: ' . $e->getMessage());
                }
            }

            return $this->defaultWards($districtId);
        });
    }

    private function defaultProvinces(): array
    {
        return [
            ['id' => 202, 'name' => 'Hồ Chí Minh'],
            ['id' => 201, 'name' => 'Hà Nội'],
            ['id' => 203, 'name' => 'Đà Nẵng'],
            ['id' => 224, 'name' => 'Hải Phòng'],
            ['id' => 220, 'name' => 'Cần Thơ'],
            ['id' => 217, 'name' => 'An Giang'],
            ['id' => 206, 'name' => 'Bà Rịa - Vũng Tàu'],
            ['id' => 248, 'name' => 'Bắc Giang'],
            ['id' => 245, 'name' => 'Bắc Kạn'],
            ['id' => 253, 'name' => 'Bạc Liêu'],
            ['id' => 249, 'name' => 'Bắc Ninh'],
            ['id' => 213, 'name' => 'Bến Tre'],
            ['id' => 262, 'name' => 'Bình Định'],
            ['id' => 205, 'name' => 'Bình Dương'],
            ['id' => 239, 'name' => 'Bình Phước'],
            ['id' => 258, 'name' => 'Bình Thuận'],
            ['id' => 252, 'name' => 'Cà Mau'],
            ['id' => 246, 'name' => 'Cao Bằng'],
            ['id' => 210, 'name' => 'Đắk Lắk'],
            ['id' => 241, 'name' => 'Đắk Nông'],
            ['id' => 265, 'name' => 'Điện Biên'],
            ['id' => 204, 'name' => 'Đồng Nai'],
            ['id' => 216, 'name' => 'Đồng Tháp'],
            ['id' => 207, 'name' => 'Gia Lai'],
            ['id' => 227, 'name' => 'Hà Giang'],
            ['id' => 232, 'name' => 'Hà Nam'],
            ['id' => 236, 'name' => 'Hà Tĩnh'],
            ['id' => 225, 'name' => 'Hải Dương'],
            ['id' => 250, 'name' => 'Hậu Giang'],
            ['id' => 267, 'name' => 'Hòa Bình'],
            ['id' => 268, 'name' => 'Hưng Yên'],
            ['id' => 208, 'name' => 'Khánh Hòa'],
            ['id' => 219, 'name' => 'Kiên Giang'],
            ['id' => 259, 'name' => 'Kon Tum'],
            ['id' => 264, 'name' => 'Lai Châu'],
            ['id' => 209, 'name' => 'Lâm Đồng'],
            ['id' => 247, 'name' => 'Lạng Sơn'],
            ['id' => 269, 'name' => 'Lào Cai'],
            ['id' => 211, 'name' => 'Long An'],
            ['id' => 231, 'name' => 'Nam Định'],
            ['id' => 235, 'name' => 'Nghệ An'],
            ['id' => 233, 'name' => 'Ninh Bình'],
            ['id' => 261, 'name' => 'Ninh Thuận'],
            ['id' => 229, 'name' => 'Phú Thọ'],
            ['id' => 260, 'name' => 'Phú Yên'],
            ['id' => 237, 'name' => 'Quảng Bình'],
            ['id' => 243, 'name' => 'Quảng Nam'],
            ['id' => 242, 'name' => 'Quảng Ngãi'],
            ['id' => 230, 'name' => 'Quảng Ninh'],
            ['id' => 238, 'name' => 'Quảng Trị'],
            ['id' => 218, 'name' => 'Sóc Trăng'],
            ['id' => 266, 'name' => 'Sơn La'],
            ['id' => 240, 'name' => 'Tây Ninh'],
            ['id' => 226, 'name' => 'Thái Bình'],
            ['id' => 244, 'name' => 'Thái Nguyên'],
            ['id' => 234, 'name' => 'Thanh Hóa'],
            ['id' => 223, 'name' => 'Thừa Thiên Huế'],
            ['id' => 212, 'name' => 'Tiền Giang'],
            ['id' => 214, 'name' => 'Trà Vinh'],
            ['id' => 228, 'name' => 'Tuyên Quang'],
            ['id' => 215, 'name' => 'Vĩnh Long'],
            ['id' => 221, 'name' => 'Vĩnh Phúc'],
            ['id' => 263, 'name' => 'Yên Bái'],
        ];
    }

    private function defaultDistricts(int $provinceId): array
    {
        if ($provinceId == 201) { // Hà Nội
            return [
                ['id' => 1482, 'name' => 'Quận Ba Đình'],
                ['id' => 1484, 'name' => 'Quận Hoàn Kiếm'],
                ['id' => 1485, 'name' => 'Quận Cầu Giấy'],
                ['id' => 1486, 'name' => 'Quận Đống Đa'],
                ['id' => 1488, 'name' => 'Quận Hai Bà Trưng'],
                ['id' => 1489, 'name' => 'Quận Thanh Xuân'],
            ];
        }

        if ($provinceId == 203) { // Đà Nẵng
            return [
                ['id' => 1530, 'name' => 'Quận Hải Châu'],
                ['id' => 1531, 'name' => 'Quận Thanh Khê'],
                ['id' => 1532, 'name' => 'Quận Sơn Trà'],
                ['id' => 1533, 'name' => 'Quận Ngũ Hành Sơn'],
            ];
        }

        // Mặc định TP. Hồ Chí Minh (202) hoặc các tỉnh khác
        return [
            ['id' => 1442, 'name' => 'Quận 1'],
            ['id' => 1444, 'name' => 'Quận 3'],
            ['id' => 1447, 'name' => 'Quận Bình Thạnh'],
            ['id' => 1451, 'name' => 'Quận 7'],
            ['id' => 1461, 'name' => 'TP. Thủ Đức'],
            ['id' => 1443, 'name' => 'Quận 2'],
            ['id' => 1446, 'name' => 'Quận 5'],
            ['id' => 1448, 'name' => 'Quận 10'],
        ];
    }

    private function defaultWards(int $districtId): array
    {
        if ($districtId == 1482) { // Ba Đình
            return [
                ['code' => '1A0107', 'name' => 'Phường Cống Vị'],
                ['code' => '1A0101', 'name' => 'Phường Điện Biên'],
                ['code' => '1A0106', 'name' => 'Phường Kim Mã'],
                ['code' => '1A0103', 'name' => 'Phường Quán Thánh'],
            ];
        }

        if ($districtId == 1530) { // Hải Châu
            return [
                ['code' => '30101', 'name' => 'Phường Hải Châu 1'],
                ['code' => '30102', 'name' => 'Phường Hải Châu 2'],
                ['code' => '30104', 'name' => 'Phường Thạch Thang'],
            ];
        }

        // Mặc định Quận 1
        return [
            ['code' => '20308', 'name' => 'Phường Bến Nghé'],
            ['code' => '20309', 'name' => 'Phường Bến Thành'],
            ['code' => '20311', 'name' => 'Phường Đa Kao'],
            ['code' => '20301', 'name' => 'Phường Tân Định'],
            ['code' => '20314', 'name' => 'Phường Phạm Ngũ Lão'],
        ];
    }
}
