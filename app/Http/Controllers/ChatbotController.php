<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class ChatbotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    private function checkCommand($command)
    {
        $command = strtolower($command);
        $allowTable = ['services', 'categories', 'orders', 'prices', 'benefits', 'comments', 'favorite', 'provinces', 'locations', 'users'];
        if (array_filter($allowTable, fn($table) => str_contains($command, $table))) {
            return true;
        }
        return false;
    }
    public function run(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'command' => 'required|string',
        ]);

        $command = strtolower($validate['command']);
        if (str_starts_with($command, 'select')) {
            if ($this->checkCommand($command)) {
                $result = DB::select($command);
                return Response::json($result);
            } else {
                return Response::json(['message' => 'Command is not valid'], 400);
            }
        }

        return Response::json(['message' => 'Command is not valid'], 400);
    }
}
