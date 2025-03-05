<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private const RELATION_TABLES = ['user'];

    private const RULE_REQUEST = [
        'title' => 'required|string',
        'body' => 'required|string',
        'receiver_id' => 'required|numeric|exists:users,id',
        'data' => 'nullable|array'
    ];

    public function registerToken(Request $request)
    {
        $validate = $request->validate(['expo_token' => 'required|string']);
        $user = auth()->guard()->user();
        $user->update($validate);
        return response()->json(['message' => 'register token success', 'expo_token' => $user->expo_token]);
    }

    public function deleteToken(Request $request)
    {
        $user = auth()->guard()->user();
        $user->update(['expo_token' => null]);
        return response()->json(['message' => 'delete token success', 'expo_token' => $user->expo_token]);
    }

    public function getAll(Request $request)
    {
        $size = $request->query('size', 50);
        $category = Notification::with(self::RELATION_TABLES)->orderBy('id', 'desc')->cursorPaginate($size);
        return response()->json($category);
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Notification::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function create(Request $request)
    {
        $validated = $request->validate(self::RULE_REQUEST);
        $receiver = User::findOrFail($validated['receiver_id']);

        $result = Notification::sendToUser($receiver->id, $validated['title'], $validated['body'], $validated['data'] ?? []);

        if ($validated['data'] ?? []) {
            $validated['data'] = json_encode($validated['data']);
        }
        $notification = $receiver->notification()->create($validated)->load(self::RELATION_TABLES);
        return response()->json(['notification' => $notification, 'result' => $result]);
    }

    public function delete(Request $request, string $id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'success']);
    }
}
