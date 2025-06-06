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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    const RELATION_TABLES = ['user', 'service', 'price'];

    public function getAll(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $service = $user->service();
        $order = Service::where('user_id', $user->id)->with('order')->get();
        return Response::json($order);
    }

    public function getById(Request $request, string $id): JsonResponse
    {
        return Response::json(Order::findOrFail($id)->load(['service', 'price', 'cancelBy']));
    }

    // public function sendNewOrderMessage(Service $service, User $user)
    // {
    //     $title = 'Bạn có đơn dịch vụ mới';
    //     $body = 'Đơn dịch vụ ' . $service->service_name . ' của người dùng ' . $user->name;
    //     $data = ['type' => 'order'];
    //     Notification::sendToUser($service->user->id, $title, $body, $data, true);
    // }

    public static function sendUpdateOrderMessageToUser(Service $service, User $user, Order $order)
    {
        $title = '';
        $body = '';
        $data = ['type' => 'order', 'id' => $order->id];
        switch ($order->status) {
            // case 1:
            //     $title = '⏳ Đơn dịch vụ đang trong trạng thái chờ';
            //     $body = 'Dịch vụ ' . $service->service_name . 'đang chờ nhà cung cấp xét duyệt';
            //     break;
            case 2:
                $title = 'Dịch vụ đang được thực hiện';
                $body = 'Đơn dịch vụ ' . $service->service_name . ' đang được thực hiện';
                break;
            case 3:
                $title = '✅ Đơn dịch vụ ' . $service->service_name . ' đã hoàn tất';
                $body = 'Nhà cung cấp ' . $service->user->name . ' đã hoàn tất đơn dịch vụ ' . $service->service_name;
                break;
            default:
                $title = '❌ Đơn dịch vụ ' . $service->service_name . ' đã bị hủy';
                $body = 'Nhà cung cấp ' . $service->user->name . ' đã hủy đơn dịch vụ ' . $service->service_name;
                break;
        }
        Notification::sendToUser($user->id, $title, $body, $data, true);
    }

    public static function sendUpdateOrderMessageToProvider(Service $service, User $user, Order $order)
    {
        $title = '';
        $body = '';
        $data = ['type' => 'order', 'id' => $order->id];
        switch ($order->status) {
            case 1:
                $title = '⏳ Bạn có đơn dịch vụ mới';
                $body = 'Người dùng ' . $user->name . ' đã đặt đơn dịch vụ ' . $service->service_name;
                break;
            case 0:
                $title = '❌ Đơn dịch vụ ' . $service->service_name . ' đã bị hủy';
                $body = 'Người dùng ' . $user->name . ' đã hủy đơn dịch vụ ' . $service->service_name;
                break;
        }
        Notification::sendToUser($service->user->id, $title, $body, $data, true);
    }

    public function create(Request $request): JsonResponse
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
                array_merge(['user_id' => $user->id], $validate, ['status' => 1])
            );
            self::sendUpdateOrderMessageToProvider($service, $user, $order);
            return Response::json($order->load(self::RELATION_TABLES));
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
            self::sendUpdateOrderMessageToUser($order->service, $order->user, $order);
            return Response::json($order->load(self::RELATION_TABLES));
        });
    }

    public function delete(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $message = $order->forceDelete();
        return Response::json(['message' => $message, 'deleted' => $order->load(self::RELATION_TABLES)]);
    }

    public function getProviderOrders(Request $request): JsonResponse
    {
        $providerId = auth()->id();
        $status = $request->query('status', 'all');
        $limit = $request->query('limit', 10);
        $searchQuery = $request->query('search', '');
        $searchFilter = $request->query('searchFilter', 'service');
        $sortBy = $request->query('sortBy', 'newest');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        \Log::info('Provider orders request:', [
            'status' => $status,
            'sortBy' => $sortBy,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);


        $query = Order::with(['service', 'user', 'price'])
            ->whereHas('service', function ($query) use ($providerId) {
                $query->where('user_id', $providerId);
            });


        if ($status !== 'all') {
            $statusMapping = [
                'pending' => 1,
                'processing' => 2,
                'completed' => 3,
                'cancelled' => 0
            ];

            if (isset($statusMapping[$status])) {
                $statusValue = $statusMapping[$status];
                $query->where('status', $statusValue);
            }
        }


        if ($searchQuery) {
            switch ($searchFilter) {
                case 'service':
                    $query->whereHas('service', function ($q) use ($searchQuery) {
                        $q->where('service_name', 'LIKE', "%{$searchQuery}%");
                    });
                    break;
                case 'customer':
                    $query->whereHas('user', function ($q) use ($searchQuery) {
                        $q->where('name', 'LIKE', "%{$searchQuery}%");
                    });
                    break;
                case 'order_id':
                    $query->where('id', 'LIKE', "%{$searchQuery}%");
                    break;
                case 'phone':
                    $query->where('phone_number', 'LIKE', "%{$searchQuery}%");
                    break;
                case 'address':
                    $query->where('address', 'LIKE', "%{$searchQuery}%");
                    break;
                case 'email':
                    $query->whereHas('user', function ($q) use ($searchQuery) {
                        $q->where('email', 'LIKE', "%{$searchQuery}%");
                    });
                    break;
                case 'all':
                    $query->where(function ($q) use ($searchQuery) {
                        $q->whereHas('service', function ($sq) use ($searchQuery) {
                            $sq->where('service_name', 'LIKE', "%{$searchQuery}%");
                        })
                            ->orWhereHas('user', function ($sq) use ($searchQuery) {
                                $sq->where('name', 'LIKE', "%{$searchQuery}%")
                                    ->orWhere('email', 'LIKE', "%{$searchQuery}%");
                            })
                            ->orWhere('id', 'LIKE', "%{$searchQuery}%")
                            ->orWhere('phone_number', 'LIKE', "%{$searchQuery}%")
                            ->orWhere('address', 'LIKE', "%{$searchQuery}%");
                    });
                    break;
            }
        }


        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }


        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_final_value', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price_final_value', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }


        $baseCountQuery = (clone $query);
        if ($searchQuery || $startDate || $endDate) {
            $totalCounts = [
                'total' => (clone $baseCountQuery)->count(),
                'pending' => (clone $baseCountQuery)->where('status', 1)->count(),
                'processing' => (clone $baseCountQuery)->where('status', 2)->count(),
                'completed' => (clone $baseCountQuery)->where('status', 3)->count(),
                'cancelled' => (clone $baseCountQuery)->where('status', 0)->count(),
            ];
        } else {

            $totalCounts = [
                'total' => Order::whereHas('service', function ($q) use ($providerId) {
                    $q->where('user_id', $providerId);
                })->count(),
                'pending' => Order::whereHas('service', function ($q) use ($providerId) {
                    $q->where('user_id', $providerId);
                })->where('status', 1)->count(),
                'processing' => Order::whereHas('service', function ($q) use ($providerId) {
                    $q->where('user_id', $providerId);
                })->where('status', 2)->count(),
                'completed' => Order::whereHas('service', function ($q) use ($providerId) {
                    $q->where('user_id', $providerId);
                })->where('status', 3)->count(),
                'cancelled' => Order::whereHas('service', function ($q) use ($providerId) {
                    $q->where('user_id', $providerId);
                })->where('status', 0)->count(),
            ];
        }

        try {

            $orders = $query->cursorPaginate($limit);


            $orders->through(function ($order) {
                $order->load(['service.category', 'service.user', 'price.benefit']);
                return $order;
            });


            return Response::json([
                'data' => $orders->items(),
                'next_cursor' => $orders->nextCursor() ? $orders->nextCursor()->encode() : null,
                'has_more' => $orders->hasMorePages(),
                'counts' => $totalCounts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching provider orders: ' . $e->getMessage());
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);


        if ($order->service->user_id !== auth()->id()) {
            return Response::json(['message' => 'Unauthorized'], 403);
        }

        $statusMapping = [
            'cancelled' => 0,
            'pending' => 1,
            'processing' => 2,
            'completed' => 3,
        ];

        $order->status = $statusMapping[$request->status];
        $order->save();

        self::sendUpdateOrderMessageToUser($order->service, $order->user, $order);

        return Response::json($order->load(['service', 'user', 'price']));
    }
}
