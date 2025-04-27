<?php

namespace App\Repositories;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function index($query)
    {
        $params = [
            'search' => $query['search'] ?? null,
            'paginate' => [
                'perPage' => $query['perPage'] ?? 15,
                'columns' => $query['columns'] ?? ['*'],
                'pageName' => $query['pageName'] ?? 'page',
                'page' => $query['page'] ?? null,
            ],
        ];

        $cacheKey = 'customers:index|'.serialize($params);

        return Cache::remember($cacheKey, 60, function () use ($params) {
            return Customer::query()
                ->search($params['search'])
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
        return Cache::remember('customers:'.$id, 60, function () use ($id) {
            return Customer::findOrFail($id);
        });
    }

    public function store(array $data)
    {
        return Customer::create($data);
    }

    public function update(array $data, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);

        Cache::forget('customers:'.$id);

        return $customer;
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        Cache::forget('customers:'.$id);
    }
}
