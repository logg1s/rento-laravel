<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getAll(Request $request)
    {
        $size = $request->query('size', 50);
        $category = Category::with('service')->withTrashed()->orderBy('id')->cursorPaginate($size);
        return response()->json($category);
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Category::findOrFail($id)->load('service'));
    }

}
