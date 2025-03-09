<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenefitController extends Controller
{
    private const RELATION_TABLES = ['price'];
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getAll()
    {
        return response()->json(Benefit::all()->load(self::RELATION_TABLES));
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Benefit::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function getByServiceId(Request $request, string $serviceId)
    {
        return response()->json(Service::findOrFail($serviceId)->benefit()->get()->load(self::RELATION_TABLES));
    }

    public function create(Request $request)
    {
        $validate = $request->validate([
            'benefit_name' => ['required', 'max:50'],
            'service_id' => ['exists:services,id']
        ]);
        return DB::transaction(function () use ($validate, $request) {
            $benefit = Benefit::create($validate);

            // Nếu có price_id, liên kết benefit với price
            if ($request->has('price_id')) {
                $priceId = $request->price_id;
                if (is_array($priceId)) {
                    // Nếu là mảng, liên kết với nhiều price
                    foreach ($priceId as $id) {
                        if (Price::where('id', $id)->exists()) {
                            $benefit->price()->syncWithoutDetaching(Price::findOrFail($id));
                        }
                    }
                } else {
                    // Nếu là số, liên kết với một price
                    if (Price::where('id', $priceId)->exists()) {
                        $benefit->price()->syncWithoutDetaching(Price::findOrFail($priceId));
                    }
                }
            }

            return response()->json($benefit->load(self::RELATION_TABLES));
        });
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'benefit_name' => ['max:50'],
            'price_id' => ['nullable', 'exists:prices,id']
        ]);

        $benefit = Benefit::findOrFail($id);

        return DB::transaction(function () use ($benefit, $validate, $request) {
            // Cập nhật tên benefit nếu có
            if (isset($validate['benefit_name'])) {
                $benefit->update(['benefit_name' => $validate['benefit_name']]);
            }

            // Cập nhật liên kết price_id nếu có
            if ($request->has('price_id')) {
                $benefit->price()->syncWithoutDetaching(Price::findOrFail($validate['price_id']));
            }

            return response()->json($benefit->load(self::RELATION_TABLES));
        });
    }

    /**
     * Xóa liên kết giữa benefit và price
     */
    public function detachPrice(Request $request, string $id)
    {
        $validate = $request->validate([
            'price_id' => ['required', 'exists:prices,id']
        ]);

        $benefit = Benefit::findOrFail($id);

        return DB::transaction(function () use ($benefit, $validate) {
            $benefit->price()->detach($validate['price_id']);
            return response()->json(['message' => 'Đã xóa liên kết thành công']);
        });
    }

    /**
     * Liên kết nhiều benefits với một price
     */
    public function attachToPrice(Request $request, string $priceId)
    {
        $validate = $request->validate([
            'benefit_ids' => ['required', 'array'],
            'benefit_ids.*' => ['exists:benefits,id']
        ]);

        $price = Price::findOrFail($priceId);

        return DB::transaction(function () use ($price, $validate) {
            foreach ($validate['benefit_ids'] as $benefitId) {
                $benefit = Benefit::findOrFail($benefitId);
                $benefit->price()->syncWithoutDetaching($price);
            }
            return response()->json(['message' => 'Đã liên kết lợi ích thành công']);
        });
    }

    /**
     * Lấy tất cả các lợi ích không thuộc về price nào
     */
    public function getIndependent(Request $request, string $serviceId)
    {
        $service = Service::findOrFail($serviceId);

        // Lấy tất cả benefits của service
        $allBenefits = $service->benefit()->with('price')->get();

        // Lọc ra các benefits không có liên kết với price nào
        $independentBenefits = $allBenefits->filter(function ($benefit) {
            return $benefit->price->isEmpty();
        });

        return response()->json($independentBenefits->values());
    }

    public function delete(Request $request, string $id)
    {
        return DB::transaction(function () use ($id) {
            $benefit = Benefit::findOrFail($id);
            // Xóa tất cả các liên kết với price trước
            $benefit->price()->detach();
            $benefit->forceDelete();
            return response()->json(['message' => 'success']);
        });
    }
}
