<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    const RELATION_TABLES = ['user', 'service', 'price'];

    public function getAll(Request $request)
    {
        return response()->json(Order::with(self::RELATION_TABLES)->get());
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Order::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function create(Request $request)
    {
        $validate = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price_id' => 'required|exists:prices,id',
            'price_final_value' => 'required|integer',
            'address' => 'string|max:255',
            'phone_number' => ['required', 'regex:/0\d{9,}/'],
            'time_start' => ['nullable', Rule::date()->format('Y-m-d H:i')],
            'message' => 'nullable|string|max:255',
        ]);
        $user = auth()->user();
        return DB::transaction(function () use ($user, $validate) {
            $order = Order::create(
                array_merge(['user_id' => $user->id], $validate),
            );
            return response()->json($order->load(self::RELATION_TABLES));
        });
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'status' => 'required|integer|between:0,3',
            'time_start' => [Rule::date()->format('Y-m-d H:i')],
        ]);
        // var_dump($validate);

        return DB::transaction(function () use ($id, $validate) {
            $order = Order::findOrFail($id);
            $order->update([
                $validate
            ]);
            return response()->json($order->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $message = $order->forceDelete();
        return response()->json(['message' => $message, 'deleted' => $order->load(self::RELATION_TABLES)]);
    }
}