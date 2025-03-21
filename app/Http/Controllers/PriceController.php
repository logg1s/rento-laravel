<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class PriceController extends Controller
{
    private const RELATION_TABLES = ['benefit'];
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getAll()
    {
        return Response::json(Price::orderBy('created_at', 'desc')->get()->load(self::RELATION_TABLES));
    }

    public function getById(Request $request, string $id)
    {
        return Response::json(Price::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function create(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'price_name' => ['required', 'max:50'],
            'price_value' => ['numeric', 'min: 1000'],
            'service_id' => ['exists:services,id']
        ]);
        $service = Service::findOrFail($validate['service_id']);
        return DB::transaction(function () use ($service, $validate) {
            $price = new Price;
            $price->price_name = $validate['price_name'];
            $price->price_value = $validate['price_value'];
            $price->service()->associate($service);
            $price->save();
            return Response::json($price->load(self::RELATION_TABLES));
        });
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'price_name' => ['required', 'max:50'],
            'price_value' => ['numeric', 'min: 1000'],
        ]);
        $price = Price::findOrFail($id);
        return DB::transaction(function () use ($price, $validate) {
            $price->price_name = $validate['price_name'];
            $price->price_value = $validate['price_value'];
            $price->save();
            return Response::json($price->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        return DB::transaction(function () use ($id) {
            $price = Price::findOrFail($id);
            $price->forceDelete();
            return Response::json(['message' => 'success']);
        });
    }

    /**
     * Thêm giá và đồng thời liên kết với nhiều benefits
     */
    public function createWithBenefits(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'price_name' => ['required', 'max:50'],
            'price_value' => ['numeric', 'min: 1000'],
            'service_id' => ['required', 'exists:services,id'],
            'benefit_ids' => ['nullable', 'array'],
            'benefit_ids.*' => ['exists:benefits,id']
        ]);

        $service = Service::findOrFail($validate['service_id']);

        return DB::transaction(function () use ($service, $validate) {
            $price = new Price;
            $price->price_name = $validate['price_name'];
            $price->price_value = $validate['price_value'];
            $price->service()->associate($service);
            $price->save();

            // Liên kết price với benefits nếu có
            if (isset($validate['benefit_ids']) && count($validate['benefit_ids']) > 0) {
                $price->benefit()->syncWithoutDetaching($validate['benefit_ids']);
            }

            return Response::json($price->load(self::RELATION_TABLES));
        });
    }

    /**
     * Cập nhật giá và liên kết với benefits
     */
    public function updateWithBenefits(Request $request, string $id)
    {
        $validate = $request->validate([
            'price_name' => ['required', 'max:50'],
            'price_value' => ['numeric', 'min: 1000'],
            'benefit_ids' => ['nullable', 'array'],
            'benefit_ids.*' => ['exists:benefits,id']
        ]);

        $price = Price::findOrFail($id);

        return DB::transaction(function () use ($price, $validate) {
            $price->price_name = $validate['price_name'];
            $price->price_value = $validate['price_value'];
            $price->save();

            // Đồng bộ lại toàn bộ liên kết với benefits
            if (isset($validate['benefit_ids'])) {
                $price->benefit()->sync($validate['benefit_ids']);
            }

            return Response::json($price->load(self::RELATION_TABLES));
        });
    }

    /**
     * Cập nhật nhiều giá cùng lúc
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.id' => ['required', 'exists:prices,id'],
            'prices.*.price_name' => ['required', 'max:50'],
            'prices.*.price_value' => ['numeric', 'min: 1000'],
            'prices.*.benefit_ids' => ['nullable', 'array'],
            'prices.*.benefit_ids.*' => ['exists:benefits,id']
        ]);

        return DB::transaction(function () use ($validate) {
            $updated = [];

            foreach ($validate['prices'] as $priceData) {
                $price = Price::findOrFail($priceData['id']);
                $price->price_name = $priceData['price_name'];
                $price->price_value = $priceData['price_value'];
                $price->save();

                if (isset($priceData['benefit_ids'])) {
                    $price->benefit()->sync($priceData['benefit_ids']);
                }

                $updated[] = $price->load(self::RELATION_TABLES);
            }

            return Response::json($updated);
        });
    }
}
