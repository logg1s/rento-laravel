<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Utils\DirtyLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    const RELATION_TABLES = ['user', 'service', 'price'];

    public function getAll(Request $request)
    {
        $user = auth()->guard()->user();
        $service = $user->service();
        $order = Service::where('user_id', $user->id)->with('order')->get();
        return response()->json($order);
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Order::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function sendNewOrderMessage(Service $service, User $user)
    {
        $title = 'Bạn có đơn dịch vụ mới';
        $body = 'Đơn dịch vụ ' . $service->service_name . ' của người dùng ' . $user->name;
        $data = ['tag' => 'order'];
        Notification::sendToUser($service->user->id, $title, $body, $data, true);
    }

    public function sendUpdateOrderMessage(Service $service, User $user, int $status)
    {
        $title = '';
        $body = '';
        $data = ['tag' => 'order'];
        switch ($status) {
            case 1:
                $title = '⏳ Đơn dịch vụ đang trong trạng thái chờ';
                $body = 'Dịch vụ ' . $service->service_name . 'đang chờ nhà cung cấp xét duyệt';
                break;
            case 2:
                $title = 'Dịch vụ đang thực hiện công việc';
                $body = 'Dịch vụ ' . $service->service_name . ' đang được thực hiện';
                break;
            case 3:
                $title = '✅ Dịch vụ hoàn tất';
                $body = 'Dịch vụ ' . $service->service_name . ' đã hoàn thành';
                break;
            default:
                $title = '❌ Dịch vụ đã được hủy';
                $body = $service->service_name . ' đã được hủy';
                break;
        }
        Notification::sendToUser($user->id, $title, $body, $data, true);
    }

    public function create(Request $request)
    {
        $validate = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price_id' => 'required|exists:prices,id',
            'price_final_value' => 'required|integer',
            'address' => 'string|max:255',
            'phone_number' => ['required', 'regex:/[0-9]{10,}/'],
            'time_start' => ['nullable', Rule::date()->format('Y-m-d H:i')],
            'message' => 'nullable|string|max:255',
        ]);
        $user = auth()->guard()->user();
        $service = Service::findOrFail($validate['service_id']);
        return DB::transaction(function () use ($user, $validate, $service) {
            $order = Order::create(
                array_merge(['user_id' => $user->id], $validate),
            );
            $this->sendNewOrderMessage($service, $user);
            return response()->json($order->load(self::RELATION_TABLES));
        });
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'status' => 'required|integer|between:0,3',
            'time_start' => [Rule::date()->format('Y-m-d H:i')],
        ]);

        return DB::transaction(function () use ($id, $validate) {
            $order = Order::findOrFail($id);
            $order->update([
                $validate
            ]);
            $this->sendUpdateOrderMessage($order->service, $order->user, $validate['status']);
            return response()->json($order->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $message = $order->forceDelete();
        return response()->json(['message' => $message, 'deleted' => $order->load(self::RELATION_TABLES)]);
    }

    public function getProviderOrders()
    {
        $providerId = auth()->id();
        return Order::with(['service', 'user'])
            ->whereHas('service', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);

        // Kiểm tra xem order có thuộc về provider này không
        if ($order->service->provider_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->status = $request->status;
        $order->save();

        // Gửi thông báo cho user
        event(new OrderStatusUpdated($order));

        return response()->json($order);
    }
}
