<?php

namespace App\Http\Controllers;

use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserBlockController extends Controller
{
    /**
     * Display a listing of blocked users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = UserBlock::with(['user', 'blockedUser']);

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by blocked_user_id if provided
        if ($request->has('blocked_user_id')) {
            $query->where('blocked_user_id', $request->blocked_user_id);
        }

        $blocks = $query->orderBy('created_at', 'desc')->get();

        return response()->json($blocks);
    }

    /**
     * Store a newly created user block in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Kiểm tra nếu đã tồn tại block
        $existingBlock = UserBlock::where('user_id', $request->user_id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->first();

        if ($existingBlock) {
            return response()->json(['message' => 'User is already blocked'], 409);
        }

        $block = UserBlock::create([
            'user_id' => $request->user_id,
            'blocked_user_id' => $request->blocked_user_id,
        ]);

        return response()->json(['message' => 'User blocked successfully', 'data' => $block], 201);
    }

    /**
     * Remove the specified user block from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $block = UserBlock::findOrFail($id);
        $block->delete();

        return response()->json(['message' => 'User unblocked successfully']);
    }

    /**
     * Check if a user is blocked by another user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkBlock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'target_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $isBlocked = UserBlock::where('user_id', $request->user_id)
            ->where('blocked_user_id', $request->target_user_id)
            ->exists();

        $isBlockedBy = UserBlock::where('user_id', $request->target_user_id)
            ->where('blocked_user_id', $request->user_id)
            ->exists();

        return response()->json([
            'is_blocked' => $isBlocked,
            'is_blocked_by' => $isBlockedBy,
        ]);
    }
}