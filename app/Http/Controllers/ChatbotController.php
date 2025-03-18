<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;


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
    public function run(Request $request)
    {
        $validate = $request->validate([
            'command' => 'required|string',
        ]);

        $command = strtolower($validate['command']);
        if (str_starts_with($command, 'select')) {
            if ($this->checkCommand($command)) {
                $result = DB::select($command);
                return response()->json($result);
            } else {
                return response()->json(['message' => 'Command is not valid'], 400);
            }
        }

        return response()->json(['message' => 'Command is not valid'], 400);





    }
}
