<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{

    private const RELATION_TABLES = ['image', 'service', 'user'];

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getAll()
    {
        return response()->json(Comment::with(self::RELATION_TABLES)->orderBy('id', 'desc')->get());
    }

    public function getById(string $id)
    {
        return response()->json(Comment::findOrFail($id)->load(self::RELATION_TABLES));
    }

    public function create(Request $request, string $serviceId)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'rate' => ['required', 'integer', 'between:1,5'],
            'comment_body' => ['required', 'string', 'min:1'],
        ]);

        if (Comment::where([['service_id', $serviceId], ['user_id', $user->id]])->exists()) {
            return response()->json(['message' => 'User already commented on this service.'], 400);
        }

        return DB::transaction(fn() => response()->json(
            Comment::create($validated + ['user_id' => $user->id, 'service_id' => $serviceId])
        ));
    }


    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        // Validate input
        $validated = $request->validate([
            'rate' => ['integer', 'between:1,5'],
            'comment_body' => ['required', 'string', 'min:1'],
        ]);

        $comment = Comment::where([
            ['id', $id],
            ['user_id', $user->id]
        ])->first();

        if (!$comment) {
            return response()->json(['message' => 'You are not allowed to edit this comment.'], 403);
        }

        DB::transaction(fn() => $comment->update($validated));

        return response()->json($comment);
    }


    public function delete(string $id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();

        if ($user->id === $comment->user_id || $user->id === $comment->service->user_id) {
            $comment->delete();
            return response()->json(['message' => 'Comment successfully deleted!']);
        }

        return response()->json(['message' => 'Forbidden failed!'], 403);
    }
}
