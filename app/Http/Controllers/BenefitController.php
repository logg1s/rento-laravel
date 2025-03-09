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
        return response()->json(Service::findOrFail($serviceId)->benefit()->get());
    }

    public function create(Request $request)
    {
        $validate = $request->validate([
            'benefit_name' => ['required', 'max:50'],
            'service_id' => ['exists:services,id']
        ]);
        return DB::transaction(function () use ($validate) {
            $benefit = Benefit::create($validate);
            return response()->json($benefit);
        });
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'benefit_name' => ['max:50'],
            'price_id' => ['exists:prices,id']
        ]);
        $benefit = Benefit::findOrFail($id);
        return DB::transaction(function () use ($benefit, $validate) {
            $benefit->update($validate);
            $benefit->price()->syncWithoutDetaching(Price::findOrFail($validate['price_id']));
            return response()->json($benefit->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        return DB::transaction(function () use ($id) {
            $benefit = Benefit::findOrFail($id);
            $benefit->forceDelete();
            return response()->json(['message' => 'success']);
        });
    }
}
