<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class OrderRepository implements OrderRepositoryInterface
{
    public function index($query)
    {
        $params = [
            'reference_no' => $query['reference_no'] ?? null,
            'sales_id' => $query['sales_id'] ?? null,
            'customer_id' => $query['customer_id'] ?? null,

            'paginate' => [
                'perPage' => $query['perPage'] ?? 15,
                'columns' => $query['columns'] ?? ['*'],
                'pageName' => $query['pageName'] ?? 'page',
                'page' => $query['page'] ?? null,
            ],
        ];

        $cacheKey = 'orders:index|'.serialize($params);

        return Cache::remember($cacheKey, 0, function () use ($params) {
            return Order::query()
                ->when($params['reference_no'], fn ($query) => $query->where('reference_no', $params['reference_no']))
                ->when($params['sales_id'], fn ($query) => $query->where('sales_id', $params['sales_id']))
                ->when($params['customer_id'], fn ($query) => $query->where('customer_id', $params['customer_id']))
                ->simplePaginate(
                    $params['paginate']['perPage'],
                    $params['paginate']['columns'],
                    $params['paginate']['pageName'],
                    $params['paginate']['page']
                );
        });
    }

    public function show($id)
    {
        return Cache::remember('orders:'.$id, 60, function () use ($id) {
            return Order::query()
                ->with([
                    'items.product:id,name,production_price,selling_price',
                    'customer:id,name',
                    'sales.user:id,name',
                    'sales:id,user_id',
                ])
                ->findOrFail($id);
        });
    }

    public function store(array $data)
    {
        $order = Order::create($data);

        $products = Product::query()
            ->whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get();

        foreach ($data['items'] as $item) {
            $product = $products->where('id', $item['product_id'])->first();

            if ($product) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'production_price' => $product->selling_price,
                    'selling_price' => $product->selling_price,
                ]);
            }
        }

        return $order->load(['items']);
    }

    public function update(array $data, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($data);

        $products = Product::query()
            ->whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get();

        foreach ($data['items'] as $item) {
            $product = $products->where('id', $item['product_id'])->first();

            if ($product) {
                $order->items()->updateOrCreate(
                    ['product_id' => $item['product_id']],
                    [
                        'quantity' => $item['quantity'],
                        'production_price' => $product->selling_price,
                        'selling_price' => $product->selling_price,
                    ]
                );
            }
        }

        $order->items()->whereNotIn('product_id', collect($data['items'])->pluck('product_id'))->delete();

        Cache::forget('orders:'.$id);

        return $order->load(['items']);
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $order->items()->delete();
        $order->delete();

        Cache::forget('orders:'.$id);
    }
}
