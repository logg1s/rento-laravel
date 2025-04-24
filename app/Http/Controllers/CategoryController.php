<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }
    public function getAll(Request $request): JsonResponse
    {
        $size = $request->query('size', 50);
        $category = Category::with('service')->withTrashed()->orderBy('id')->cursorPaginate($size);
        return Response::json($category);
    }

    public function getById(Request $request, string $id)
    {
        return Response::json(Category::findOrFail($id)->load('service'));
    }

}
