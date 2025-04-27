<?php

namespace App\Http\Controllers\Report;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuerySalesPerformanceStatisticReportRequest;
use App\Models\Order;
use App\Models\SalesTarget;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use OpenApi\Attributes as OA;

class SalesPerformanceStatisticReportController extends Controller
{
    #[OA\Get(
        path: '/reports/sales/performance-statistic',
        summary: 'Get Sales Performance Statistics Report',
        description: 'Retrieve performance statistics comparing sales revenue against targets',
        operationId: 'reports.sales.performance-statistic',
        tags: ['Report'],
        parameters: [
            new OA\Parameter(
                name: 'month',
                in: 'query',
                description: 'The month to filter the report (range 1-12)',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 3)
            ),
            new OA\Parameter(
                name: 'is_underperform',
                in: 'query',
                description: 'Filter for underperforming sales targets',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
        ],
        responses: [
            new OA\Response(response: Response::HTTP_OK, description: 'The request has succeeded'),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The requested resource could not be found'),
            new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The request was well-formed but contains semantic errors'),
            new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
        ],
    )]
    public function __invoke(QuerySalesPerformanceStatisticReportRequest $request)
    {
        try {
            $validated = $request->validated();

            $filters = [
                'start_date' => $request->getDateRange()['start_date'],
                'end_date' => $request->getDateRange()['end_date'],
                'is_underperform' => isset($validated['is_underperform']) ? (bool) $validated['is_underperform'] : null,
            ];

            $cacheKey = 'reports.sales.performance-statistic|'.md5(json_encode($validated));

            return Cache::remember($cacheKey, 60, function () use ($filters) {
                $orders = Order::query()
                    ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
                    ->with([
                        'items:id,quantity,selling_price,order_id',
                        'sales.user:id,name',
                        'sales.user:id,user_id',
                    ])
                    ->get(['id', 'customer_id', 'sales_id', 'created_at']);

                $targets = SalesTarget::query()
                    ->whereBetween('active_date', [$filters['start_date'], $filters['end_date']])
                    ->whereIn('sales_id', $orders->pluck('sales_id')->unique())
                    ->get(['id', 'active_date', 'amount', 'sales_id']);

                $items = $this->processOrdersData($orders, $targets);

                $items = $this->filterItems($filters, $items);

                $items = collect($items)->sortByDesc('percentage')->values()->all();

                return ApiResponse::sendResponse([
                    'is_underperform' => $filters['is_underperform'],
                    'month' => $filters['start_date']->format('F Y'),
                    'items' => $items,
                ], null, 200, true);
            });
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound($exception->getMessage());
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }

    private function processOrdersData($orders, $targets): array
    {
        $items = [];

        $ordersBySales = $orders->groupBy('sales_id');

        foreach ($ordersBySales as $salesId => $salesOrders) {
            $totalRevenue = $salesOrders->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->quantity * $item->selling_price;
                });
            });

            $targetBySalesId = $targets->where('sales_id', $salesId)->sum('amount');

            $percentage = $targetBySalesId > 0 ? ($totalRevenue / $targetBySalesId) * 100 : 0;

            $items[$salesId] = [
                'sales' => $salesOrders->first()->sales->user->name,
                'revenue' => [
                    'amount' => number_format($totalRevenue, 2, '.', ''),
                    'abbreviation' => Number::abbreviate($totalRevenue, 2),
                ],
                'target' => [
                    'amount' => number_format($targetBySalesId, 2, '.', ''),
                    'abbreviation' => Number::abbreviate($targetBySalesId, 2),
                ],
                'percentage' => number_format($percentage, 2, '.', ''),
            ];
        }

        return $items;
    }

    private function filterItems($filters, $items)
    {
        return collect($items)
            ->filter(function ($item) use ($filters) {
                if ($filters['is_underperform'] === null) {
                    return true;
                }

                if ($filters['is_underperform']) {
                    return $item['revenue']['amount'] < $item['target']['amount'];
                }

                return $item['revenue']['amount'] >= $item['target']['amount'];
            })
            ->values()
            ->all();
    }
}
