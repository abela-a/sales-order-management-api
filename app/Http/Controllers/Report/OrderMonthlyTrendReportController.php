<?php

namespace App\Http\Controllers\Report;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryOrderMonthlyTrendReportRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class OrderMonthlyTrendReportController extends Controller
{
    #[OA\Get(
        path: '/reports/orders/monthly-trend',
        summary: 'Get Monthly Trend Report for Orders',
        description: 'Retrieve the monthly trend report for orders',
        operationId: 'reports.orders.monthly-trend',
        tags: ['Report'],
        parameters: [
            new OA\Parameter(
                name: 'customer_id',
                in: 'query',
                description: 'Filter by customer ID',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'sales_id',
                in: 'query',
                description: 'Filter by sales ID',
                required: false,
                schema: new OA\Schema(type: 'integer')
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
    public function __invoke(QueryOrderMonthlyTrendReportRequest $request)
    {
        try {
            $validated = $request->validated();

            $filters = array_merge($validated, [
                'start_date' => Carbon::now()->subYears(2)->startOfYear(),
                'end_date' => Carbon::now()->endOfYear(),

                'customer' => $request->getCustomer(),
                'sales' => $request->getSales(),
            ]);

            $cacheKey = 'reports.orders.monthly-trend|'.md5(json_encode($validated));

            return Cache::remember($cacheKey, 60, function () use ($filters) {
                $items = $this->initializeItemsFormat(
                    $filters['start_date']->year,
                    $filters['end_date']->year
                );

                $orders = Order::query()
                    ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
                    ->when($filters['customer_id'] ?? null, fn ($query) => $query->where('customer_id', $filters['customer_id']))
                    ->when($filters['sales_id'] ?? null, fn ($query) => $query->where('sales_id', $filters['sales_id']))
                    ->with(['items:id,quantity,selling_price,order_id'])
                    ->get(['id', 'customer_id', 'sales_id', 'created_at']);

                $this->processOrdersData($orders, $items);

                return ApiResponse::sendResponse([
                    'customer' => $filters['customer']->name ?? null,
                    'sales' => $filters['sales']->user->name ?? null,
                    'items' => array_values($items),
                ], null, 200, true);
            });
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound($exception->getMessage());
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }

    private function initializeItemsFormat(int $startYear, int $endYear): array
    {
        $yearlyData = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create($year, $month)->format('M');
                $monthlyData[] = [
                    'x' => $monthName,
                    'y' => '0.00',
                ];
            }

            $yearlyData[$year] = [
                'name' => $year,
                'data' => $monthlyData,
            ];
        }

        return $yearlyData;
    }

    private function processOrdersData($orders, array &$yearlyData): void
    {
        foreach ($orders as $order) {
            $date = Carbon::parse($order->created_at);
            $year = $date->year;
            $month = $date->month;

            if (isset($yearlyData[$year])) {
                $total = $order->items->sum(function ($item) {
                    return $item->quantity * $item->selling_price;
                });

                $currentValue = (float) $yearlyData[$year]['data'][$month - 1]['y'];
                $newValue = $currentValue + $total;
                $yearlyData[$year]['data'][$month - 1]['y'] = number_format($newValue, 2, '.', '');
            }
        }
    }
}
