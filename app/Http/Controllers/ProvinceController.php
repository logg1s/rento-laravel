<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    /**
     * Lấy danh sách tất cả các tỉnh thành
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $provinces = Province::orderBy('name')->get();

        return Response::json([
            'status' => 'success',
            'data' => $provinces
        ]);
    }

    /**
     * Lấy thông tin chi tiết của một tỉnh thành
     *
     * @param int $id ID của tỉnh thành
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $province = Province::findOrFail($id);

        return Response::json([
            'status' => 'success',
            'data' => $province
        ]);
    }

    /**
     * Tìm kiếm tỉnh thành theo tên
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->get('keyword', '');

        $provinces = Province::where('name', 'like', "%{$keyword}%")
            ->orderBy('name')
            ->get();

        return Response::json([
            'status' => 'success',
            'data' => $provinces
        ]);
    }
}