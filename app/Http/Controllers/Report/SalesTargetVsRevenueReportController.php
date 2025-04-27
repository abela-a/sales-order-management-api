<?php

namespace App\Http\Controllers\Report;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuerySalesTargetVsRevenueRequest;
use App\Models\Order;
use App\Models\SalesTarget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class SalesTargetVsRevenueReportController extends Controller
{
    #[OA\Get(
        path: '/reports/sales/target-vs-actual-revenue',
        summary: 'Get Sales Target vs Actual Revenue Report',
        description: 'Retrieve the sales target vs actual revenue report',
        operationId: 'reports.sales.target-vs-actual-revenue',
        tags: ['Report'],
        parameters: [
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
    public function __invoke(QuerySalesTargetVsRevenueRequest $request)
    {
        try {
            $validated = $request->validated();

            $filters = array_merge($validated, [
                'year' => Carbon::now()->year,
                'sales' => $request->getSales(),
            ]);

            $cacheKey = 'reports.sales.target-vs-revenue|'.md5(json_encode($validated));

            return Cache::remember($cacheKey, 60, function () use ($filters) {
                $items = $this->initializeItemsFormat($filters['year']);

                $orders = Order::query()
                    ->whereYear('created_at', $filters['year'])
                    ->when($filters['sales_id'] ?? null, fn ($query) => $query->where('sales_id', $filters['sales_id']))
                    ->with(['items:id,quantity,selling_price,production_price,order_id'])
                    ->get(['id', 'customer_id', 'sales_id', 'created_at']);

                $targets = SalesTarget::query()
                    ->whereYear('active_date', $filters['year'])
                    ->whereIn('sales_id', $orders->pluck('sales_id')->unique())
                    ->when($filters['sales_id'] ?? null, fn ($query) => $query->where('sales_id', $filters['sales_id']))
                    ->get(['id', 'active_date', 'amount', 'sales_id']);

                $this->processTargesData($targets, $items);
                $this->processOrdersData($orders, $items);

                return ApiResponse::sendResponse([
                    'sales' => $filters['sales']->user->name ?? null,
                    'year' => $filters['year'],
                    'items' => array_values($items),
                ], null, 200, true);
            });
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound($exception->getMessage());
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }

    private function initializeItemsFormat($year): array
    {
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create($year, $month)->format('M');
            $monthlyData[] = [
                'x' => $monthName,
                'y' => '0.00',
            ];
        }

        return [
            [
                'name' => 'Target',
                'data' => $monthlyData,
            ],
            [
                'name' => 'Revenue',
                'data' => $monthlyData,
            ],
            [
                'name' => 'Income',
                'data' => $monthlyData,
            ],
        ];
    }

    private function processTargesData($targets, array &$items): void
    {
        foreach ($targets as $target) {
            $month = Carbon::parse($target->active_date)->format('M');
            $monthIndex = array_search($month, array_column($items[0]['data'], 'x'));

            if ($monthIndex !== false) {
                $currentValue = (float) $items[0]['data'][$monthIndex]['y'];
                $newValue = $currentValue + $target->amount;
                $items[0]['data'][$monthIndex]['y'] = number_format($newValue, 2, '.', '');
            }
        }
    }

    private function processOrdersData($orders, array &$items): void
    {
        foreach ($orders as $order) {
            $date = Carbon::parse($order->created_at);
            $month = $date->format('M');
            $monthIndex = array_search($month, array_column($items[0]['data'], 'x'));

            if ($monthIndex !== false) {
                $totalRevenue = (float) $items[1]['data'][$monthIndex]['y'];
                $totalIncome = (float) $items[2]['data'][$monthIndex]['y'];

                $revenue = $order->items->sum(function ($item) {
                    return $item->quantity * $item->selling_price;
                });

                $income = $order->items->sum(function ($item) {
                    return ($item->selling_price - $item->production_price) * $item->quantity;
                });

                $items[1]['data'][$monthIndex]['y'] = number_format($totalRevenue + $revenue, 2, '.', '');
                $items[2]['data'][$monthIndex]['y'] = number_format($totalIncome + $income, 2, '.', '');
            }
        }
    }
}
