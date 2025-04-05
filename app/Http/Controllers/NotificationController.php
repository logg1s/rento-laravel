<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use App\Utils\DirtyLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    private const RELATION_TABLES = ['user'];

    private const RULE_REQUEST = [
        'title' => 'required|string',
        'body' => 'required|string',
        'receiver_id' => 'required|numeric|exists:users,id',
        'data' => 'nullable|array'
    ];

    public function registerToken(Request $request): JsonResponse
    {
        $validate = $request->validate(['expo_token' => 'required|string']);
        $user = auth()->guard()->user();
        $user->update($validate);
        return Response::json(['message' => 'register token success', 'expo_token' => $user->expo_token]);
    }

    public function deleteToken(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $user->update(['expo_token' => null]);
        return Response::json(['message' => 'delete token success', 'expo_token' => $user->expo_token]);
    }

    public function getAll(Request $request): JsonResponse
    {
        $size = $request->query('size', 50);
        $user = auth()->guard()->user();
        $category = Notification::where('user_id', $user->id)->with(self::RELATION_TABLES)->orderBy('id', 'desc')->cursorPaginate($size);
        return Response::json($category);
    }

    public function getById(Request $request, string $id)
    {
        return Response::json(Notification::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function chatNotification(Request $request, string $id)
    {
        $validate = $request->validate(['body' => 'required|string']);
        $sender = auth()->guard()->user();
        $receiver = User::findOrFail($id);
        if ($receiver->expo_token) {
            $title = '💬 ' . $sender->name;
            $body = $validate['body'];
            $data = ['type' => 'message', 'id' => $sender->id];
            Notification::sendToUser($id, $title, $body, $data, false, 'messaging');
        }
        return Response::json(['message' => 'success']);
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate(self::RULE_REQUEST);
        $receiver = User::findOrFail($validated['receiver_id']);

        $result = Notification::sendToUser($receiver->id, $validated['title'], $validated['body'], $validated['data'] ?? []);

        if ($validated['data'] ?? []) {
            $validated['data'] = json_encode($validated['data']);
        }
        $notification = $receiver->notification()->create($validated)->load(self::RELATION_TABLES);
        return Response::json(['notification' => $notification, 'result' => $result]);
    }

    public function delete(Request $request, string $id)
    {
        Notification::findOrFail($id)->delete();
        return Response::json(['message' => 'success']);
    }

    public function readedAll(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $user->notification()->update(['is_read' => 1]);
        return Response::json(['message' => 'sucesss']);
    }

    public function readedById(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => 1]);
        return Response::json(['message' => 'sucesss']);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $unReadNotificationsCount = $user->notification()->where('is_read', false)->count();
        return Response::json(['unReadNotificationsCount' => $unReadNotificationsCount]);
    }
}
