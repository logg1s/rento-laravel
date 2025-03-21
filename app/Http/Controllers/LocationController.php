<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Lấy danh sách tất cả các địa điểm
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $locations = Location::all();

        return Response::json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    /**
     * Tạo một địa điểm mới
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
            'address' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $location = Location::create([
            'location_name' => $request->location_name,
            'province_id' => $request->province_id,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Đã tạo địa điểm thành công',
            'data' => $location->load('province')
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết của một địa điểm
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $location = Location::with('province')->findOrFail($id);

        return Response::json([
            'status' => 'success',
            'data' => $location
        ]);
    }

    /**
     * Cập nhật thông tin của một địa điểm
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'location_name' => 'sometimes|required|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
            'address' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $location->update($request->only([
            'location_name',
            'province_id',
            'address',
            'lat',
            'lng',
        ]));

        return Response::json([
            'status' => 'success',
            'message' => 'Đã cập nhật địa điểm thành công',
            'data' => $location->fresh()->load('province')
        ]);
    }

    /**
     * Xóa một địa điểm
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return Response::json([
            'status' => 'success',
            'message' => 'Đã xóa địa điểm thành công'
        ]);
    }
}