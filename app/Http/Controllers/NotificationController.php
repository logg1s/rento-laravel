<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    private const RELATION_TABLES = ['user'];
    private const RULE_REQUEST = [
        'title' => 'string',
        'message' => 'string',
        'user_id' => 'numeric|exists:users,id',
    ];

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

        return response()->json(Notification::create($validated)->load(self::RELATION_TABLES));
    }

    public function update(Request $request, string $id)
    {

        $validated = $request->validate($validated = self::RULE_REQUEST);

        $notification = Notification::findOrFail($id);
        $notification->update($validated);

        return response()->json($notification->load(self::RELATION_TABLES));
    }

    public function delete(Request $request, string $id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'success']);
    }
}
