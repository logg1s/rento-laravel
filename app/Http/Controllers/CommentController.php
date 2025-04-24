<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Service;
use App\Utils\DirtyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class CommentController extends Controller
{

    private const RELATION_TABLES = ['image', 'service', 'user'];

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getAll(): JsonResponse
    {
        return Response::json(Comment::with(self::RELATION_TABLES)->orderBy('created_at', 'desc')->cursorPaginate(10));
    }

    public function getById(string $id): JsonResponse
    {
        return Response::json(Comment::findOrFail($id)->load(self::RELATION_TABLES));
    }

    /**
     * Get all comments for a specific service
     */
    public function getCommentsByServiceId(Request $request, string $id): JsonResponse
    {
        $comments = Comment::where('service_id', $id)
            ->with('user')
            ->orderBy('id')
            ->cursorPaginate(10);

        return Response::json($comments);
    }

    public function create(Request $request, string $serviceId): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'rate' => ['required', 'integer', 'between:1,5'],
            'comment_body' => ['required', 'string', 'min:1'],
        ]);

        if (Comment::where([['service_id', $serviceId], ['user_id', $user->id]])->exists()) {
            return Response::json(['message' => 'User already commented on this service.'], 400);
        }

        return DB::transaction(fn() => Response::json(
            Comment::create($validated + ['user_id' => $user->id, 'service_id' => $serviceId])
        ));
    }


    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();


        $validated = $request->validate([
            'rate' => ['integer', 'between:1,5'],
            'comment_body' => ['required', 'string', 'min:1'],
        ]);

        $comment = Comment::where([
            ['id', $id],
            ['user_id', $user->id]
        ])->first();

        if (!$comment) {
            return Response::json(['message' => 'You are not allowed to edit this comment.'], 403);
        }

        DB::transaction(fn() => $comment->update($validated));

        return Response::json($comment);
    }


    public function delete(string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();

        if ($user->id === $comment->user_id || $user->id === $comment->service->user_id) {
            $comment->delete();
            return Response::json(['message' => 'Comment successfully deleted!']);
        }

        return Response::json(['message' => 'Forbidden failed!'], 403);
    }
}
