<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProviderStatisticController extends Controller
{
    public function getStatistics(): JsonResponse
    {
        $providerId = auth()->id();

        $totalServices = Service::where('provider_id', $providerId)->count();

        $orderStats = Order::where('provider_id', $providerId)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_orders'),
                DB::raw('COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_orders'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_orders'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as total_revenue')
            )
            ->first();

        $monthlyRevenue = Order::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'total_services' => $totalServices,
            'order_statistics' => $orderStats,
            'monthly_revenue' => $monthlyRevenue
        ]);
    }
}