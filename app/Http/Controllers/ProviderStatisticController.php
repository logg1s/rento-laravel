<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderStatisticController extends Controller
{
    /**
     * Get comprehensive statistics for a provider
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $providerId = auth()->id();
        $period = $request->query('period', 'week'); // week, month, year

        try {
            return response()->json([
                'revenue' => $this->getRevenueStatistics($providerId, $period),
                'orders' => $this->getOrderStatistics($providerId, $period),
                'services' => $this->getServiceEffectiveness($providerId, $period),
                'summary' => $this->getSummaryStatistics($providerId)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in provider statistics: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve statistics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get revenue statistics based on the specified period
     *
     * @param int $providerId
     * @param string $period
     * @return array
     */
    private function getRevenueStatistics(int $providerId, string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);

        // Format and group by based on period
        $dateFormat = $this->getDateFormatForPeriod($period);
        $groupByFormat = $this->getGroupByFormatForPeriod($period);
        $labelFormat = $this->getLabelFormatForPeriod($period);

        // Get orders for services owned by this provider
        $revenues = Order::whereHas('service', function ($query) use ($providerId) {
            $query->where('user_id', $providerId);
        })
            ->where('status', 3) // 3 = completed
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('SUM(price_final_value) as revenue')
            )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$groupByFormat}')"))
            ->orderBy('date', 'asc')
            ->get();

        // Format labels and data for frontend
        $labels = [];
        $data = [];

        $currentDate = clone $startDate;
        $endDate = Carbon::now();

        while ($currentDate <= $endDate) {
            $formattedDate = $currentDate->format($labelFormat);
            $labels[] = $formattedDate;

            $revenueForDate = $revenues->firstWhere('date', $currentDate->format($dateFormat));
            $data[] = $revenueForDate ? $revenueForDate->revenue : 0;

            // Increment based on period
            if ($period === 'week') {
                $currentDate->addDay();
            } elseif ($period === 'month') {
                $currentDate->addDay();
            } else { // year
                $currentDate->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'average' => count($data) > 0 ? array_sum($data) / count($data) : 0,
            'trend' => $this->calculateTrend($data)
        ];
    }

    /**
     * Get order statistics
     *
     * @param int $providerId
     * @param string $period
     * @return array
     */
    private function getOrderStatistics(int $providerId, string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $orderStats = Order::whereHas('service', function ($query) use ($providerId) {
            $query->where('user_id', $providerId);
        })
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COUNT(CASE WHEN status = 3 THEN 1 END) as completed_orders'),
                DB::raw('COUNT(CASE WHEN status = 0 THEN 1 END) as cancelled_orders'),
                DB::raw('COUNT(CASE WHEN status = 1 THEN 1 END) as pending_orders'),
                DB::raw('COUNT(CASE WHEN status = 2 THEN 1 END) as in_progress_orders')
            )
            ->first();

        // Calculate completion rate
        $completionRate = $orderStats->total_orders > 0
            ? ($orderStats->completed_orders / $orderStats->total_orders) * 100
            : 0;

        // Get daily/weekly/monthly order trends
        $dateFormat = $this->getDateFormatForPeriod($period);
        $groupByFormat = $this->getGroupByFormatForPeriod($period);
        $labelFormat = $this->getLabelFormatForPeriod($period);

        $orderTrends = Order::whereHas('service', function ($query) use ($providerId) {
            $query->where('user_id', $providerId);
        })
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$groupByFormat}')"))
            ->orderBy('date', 'asc')
            ->get();

        // Format for frontend
        $labels = [];
        $data = [];

        $currentDate = clone $startDate;
        $endDate = Carbon::now();

        while ($currentDate <= $endDate) {
            $formattedDate = $currentDate->format($labelFormat);
            $labels[] = $formattedDate;

            $ordersForDate = $orderTrends->firstWhere('date', $currentDate->format($dateFormat));
            $data[] = $ordersForDate ? $ordersForDate->total : 0;

            // Increment based on period
            if ($period === 'week') {
                $currentDate->addDay();
            } elseif ($period === 'month') {
                $currentDate->addDay();
            } else { // year
                $currentDate->addMonth();
            }
        }

        return [
            'total' => $orderStats->total_orders,
            'completed' => $orderStats->completed_orders,
            'cancelled' => $orderStats->cancelled_orders,
            'pending' => $orderStats->pending_orders,
            'in_progress' => $orderStats->in_progress_orders,
            'completion_rate' => round($completionRate, 2),
            'cancellation_rate' => $orderStats->total_orders > 0
                ? round(($orderStats->cancelled_orders / $orderStats->total_orders) * 100, 2)
                : 0,
            'trends' => [
                'labels' => $labels,
                'data' => $data
            ]
        ];
    }

    /**
     * Get service effectiveness statistics
     *
     * @param int $providerId
     * @param string $period
     * @return array
     */
    private function getServiceEffectiveness(int $providerId, string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);

        // Get services with order counts and ratings
        $services = Service::where('user_id', $providerId)
            ->select(
                'services.id',
                'services.service_name as name',
                DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.service_id = services.id AND orders.created_at >= "' . $startDate->format('Y-m-d H:i:s') . '") as order_count'),
                DB::raw('(SELECT SUM(price_final_value) FROM orders WHERE orders.service_id = services.id AND orders.status = 3 AND orders.created_at >= "' . $startDate->format('Y-m-d H:i:s') . '") as revenue'),
                DB::raw('(SELECT AVG(rate) FROM comments WHERE comments.service_id = services.id AND comments.created_at >= "' . $startDate->format('Y-m-d H:i:s') . '") as average_rating'),
                DB::raw('(SELECT COUNT(*) FROM comments WHERE comments.service_id = services.id AND comments.created_at >= "' . $startDate->format('Y-m-d H:i:s') . '") as review_count')
            )
            ->orderBy('order_count', 'desc')
            ->get();

        foreach ($services as &$service) {
            $service->revenue = $service->revenue ?? 0;
            $service->average_rating = $service->average_rating ? round($service->average_rating, 1) : 0;
            $service->review_count = $service->review_count ?? 0;
        }

        return [
            'services' => $services,
            'total_services' => $services->count(),
            'most_popular' => $services->first() ? $services->first()->name : null,
            'highest_rated' => $services->sortByDesc('average_rating')->first()
                ? $services->sortByDesc('average_rating')->first()->name
                : null,
            'most_profitable' => $services->sortByDesc('revenue')->first()
                ? $services->sortByDesc('revenue')->first()->name
                : null
        ];
    }

    /**
     * Get summary statistics for provider
     *
     * @param int $providerId
     * @return array
     */
    private function getSummaryStatistics(int $providerId): array
    {
        // Lifetime stats
        $lifetimeStats = Order::whereHas('service', function ($query) use ($providerId) {
            $query->where('user_id', $providerId);
        })
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN status = 3 THEN price_final_value ELSE 0 END) as total_revenue'),
                DB::raw('AVG(CASE WHEN status = 3 THEN price_final_value ELSE NULL END) as average_order_value')
            )
            ->first();

        // Average rating
        $averageRating = Comment::whereHas('service', function ($query) use ($providerId) {
            $query->where('user_id', $providerId);
        })
            ->avg('rate') ?? 0;

        return [
            'total_services' => Service::where('user_id', $providerId)->count(),
            'total_orders' => $lifetimeStats->total_orders,
            'total_revenue' => $lifetimeStats->total_revenue ?? 0,
            'average_order_value' => round($lifetimeStats->average_order_value ?? 0, 2),
            'average_rating' => round($averageRating, 1)
        ];
    }

    /**
     * Helper function to get start date based on period
     *
     * @param string $period
     * @return Carbon
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        $now = Carbon::now();

        switch ($period) {
            case 'week':
                return $now->copy()->subDays(6)->startOfDay(); // Last 7 days
            case 'month':
                return $now->copy()->subDays(29)->startOfDay(); // Last 30 days
            case 'year':
                return $now->copy()->subMonths(11)->startOfMonth(); // Last 12 months
            default:
                return $now->copy()->subDays(6)->startOfDay(); // Default to week
        }
    }

    /**
     * Get date format for SQL based on period
     */
    private function getDateFormatForPeriod(string $period): string
    {
        switch ($period) {
            case 'week':
            case 'month':
                return '%Y-%m-%d';
            case 'year':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }

    /**
     * Get group by format for SQL based on period
     */
    private function getGroupByFormatForPeriod(string $period): string
    {
        switch ($period) {
            case 'week':
            case 'month':
                return '%Y-%m-%d';
            case 'year':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }

    /**
     * Get label format for frontend based on period
     */
    private function getLabelFormatForPeriod(string $period): string
    {
        switch ($period) {
            case 'week':
                return 'd/m';
            case 'month':
                return 'd/m';
            case 'year':
                return 'm/Y';
            default:
                return 'd/m';
        }
    }

    /**
     * Calculate trend (percentage increase/decrease)
     */
    private function calculateTrend(array $data): float
    {
        if (count($data) < 2) {
            return 0;
        }

        // Compare first half with second half
        $halfPoint = floor(count($data) / 2);
        $firstHalf = array_slice($data, 0, $halfPoint);
        $secondHalf = array_slice($data, $halfPoint);

        $firstHalfAvg = array_sum($firstHalf) / count($firstHalf);
        $secondHalfAvg = array_sum($secondHalf) / count($secondHalf);

        if ($firstHalfAvg == 0) {
            return $secondHalfAvg > 0 ? 100 : 0;
        }

        return round((($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100, 2);
    }
}