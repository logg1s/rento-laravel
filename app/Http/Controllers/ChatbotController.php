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
    public function run(Request $request)
    {
        $validate = $request->validate([
            'command' => 'required|string',
        ]);

        $command = strtolower($validate['command']);
        if (str_starts_with($command, 'select')) {
            if (str_contains($command, 'services') || str_contains($command, 'categories') || str_contains($command, 'orders')) {
                $result = DB::select($command);
                return response()->json($result);
            } else {
                return response()->json(['message' => 'Command is not valid'], 400);
            }
        }

        return response()->json(['message' => 'Command is not valid'], 400);





    }
}
