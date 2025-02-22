<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    private const RELATION_TABLES = ['benefit'];
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAll()
    {
        return response()->json(Price::all()->load(self::RELATION_TABLES));
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Price::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function create(Request $request)
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
            return response()->json($price->load(self::RELATION_TABLES));
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
            return response()->json($price->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        return DB::transaction(function () use ($id) {
            $price = Price::findOrFail($id);
            $price->forceDelete();
            return response()->json(['message' => 'success']);
        });
    }
}
