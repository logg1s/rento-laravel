<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
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
        return Response::json(Benefit::orderBy('created_at', 'desc')->get()->load(self::RELATION_TABLES));
    }

    public function getById(Request $request, string $id)
    {
        return Response::json(Benefit::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function getByServiceId(Request $request, string $serviceId)
    {
        return Response::json(
            Service::findOrFail($serviceId)
                ->benefit()
                ->orderBy('created_at', 'desc')
                ->get()
                ->load(self::RELATION_TABLES)
        );
    }

    public function create(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'benefit_name' => ['required', 'max:50'],
            'service_id' => ['exists:services,id']
        ]);
        return DB::transaction(function () use ($validate, $request) {
            $benefit = Benefit::create($validate);


            if ($request->has('price_id')) {
                $priceId = $request->price_id;
                if (is_array($priceId)) {

                    foreach ($priceId as $id) {
                        if (Price::where('id', $id)->exists()) {
                            $benefit->price()->syncWithoutDetaching(Price::findOrFail($id));
                        }
                    }
                } else {

                    if (Price::where('id', $priceId)->exists()) {
                        $benefit->price()->syncWithoutDetaching(Price::findOrFail($priceId));
                    }
                }
            }

            return Response::json($benefit->load(self::RELATION_TABLES));
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

            if (isset($validate['benefit_name'])) {
                $benefit->update(['benefit_name' => $validate['benefit_name']]);
            }


            if ($request->has('price_id')) {
                $benefit->price()->syncWithoutDetaching(Price::findOrFail($validate['price_id']));
            }

            return Response::json($benefit->load(self::RELATION_TABLES));
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
            return Response::json(['message' => 'Đã xóa liên kết thành công']);
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
            return Response::json(['message' => 'Đã liên kết lợi ích thành công']);
        });
    }


    public function getIndependent(Request $request, string $serviceId)
    {
        $service = Service::findOrFail($serviceId);


        $allBenefits = $service->benefit()->with('price')->orderBy('created_at', 'desc')->get();


        $independentBenefits = $allBenefits->filter(function ($benefit) {
            return $benefit->price->isEmpty();
        });

        return Response::json($independentBenefits->values());
    }

    public function delete(Request $request, string $id)
    {
        return DB::transaction(function () use ($id) {
            $benefit = Benefit::findOrFail($id);

            $benefit->price()->detach();
            $benefit->forceDelete();
            return Response::json(['message' => 'success']);
        });
    }


    public function createWithPrices(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'benefit_name' => ['required', 'max:50'],
            'service_id' => ['required', 'exists:services,id'],
            'price_ids' => ['nullable', 'array'],
            'price_ids.*' => ['exists:prices,id']
        ]);

        return DB::transaction(function () use ($validate) {
            $benefit = Benefit::create([
                'benefit_name' => $validate['benefit_name'],
                'service_id' => $validate['service_id']
            ]);


            if (isset($validate['price_ids']) && count($validate['price_ids']) > 0) {
                $benefit->price()->syncWithoutDetaching($validate['price_ids']);
            }

            return Response::json($benefit->load(self::RELATION_TABLES));
        });
    }


    public function updateWithPrices(Request $request, string $id)
    {
        $validate = $request->validate([
            'benefit_name' => ['required', 'max:50'],
            'price_ids' => ['nullable', 'array'],
            'price_ids.*' => ['exists:prices,id']
        ]);

        $benefit = Benefit::findOrFail($id);

        return DB::transaction(function () use ($benefit, $validate) {

            $benefit->update(['benefit_name' => $validate['benefit_name']]);


            if (isset($validate['price_ids'])) {
                $benefit->price()->sync($validate['price_ids']);
            } else {
                $benefit->price()->detach();
            }

            return Response::json($benefit->load(self::RELATION_TABLES));
        });
    }

    /**
     * Cập nhật nhiều benefit cùng lúc
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'benefits' => ['required', 'array'],
            'benefits.*.id' => ['required', 'exists:benefits,id'],
            'benefits.*.benefit_name' => ['required', 'max:50'],
            'benefits.*.price_ids' => ['nullable', 'array'],
            'benefits.*.price_ids.*' => ['exists:prices,id']
        ]);

        return DB::transaction(function () use ($validate) {
            $updated = [];

            foreach ($validate['benefits'] as $benefitData) {
                $benefit = Benefit::findOrFail($benefitData['id']);
                $benefit->update(['benefit_name' => $benefitData['benefit_name']]);

                if (isset($benefitData['price_ids'])) {
                    $benefit->price()->sync($benefitData['price_ids']);
                }

                $updated[] = $benefit->load(self::RELATION_TABLES);
            }

            return Response::json($updated);
        });
    }
}
