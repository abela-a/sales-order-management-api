<?php

describe('index', function () {
    it('gets all orders', function () {
        $response = $this->getJson('/api/v1/orders');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'reference_no',
                            'sales_id',
                            'customer_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'links' => [
                        'first',
                        'prev',
                        'next',
                    ],
                    'meta' => [
                        'current_page',
                        'from',
                        'path',
                        'per_page',
                        'to',
                    ],
                ],
            ]);
    });

    it('gets orders by filter', function () {
        $order = App\Models\Order::first();
        $response = $this->getJson('/api/v1/orders?reference_no='.$order->reference_no);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'reference_no',
                            'sales_id',
                            'customer_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'links',
                    'meta',
                ],
            ]);
    });

    it('returns empty when filter not found', function () {
        $response = $this->getJson('/api/v1/orders?reference_no=NotFoundOrder');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [],
                    'links',
                    'meta',
                ],
            ])
            ->assertJsonCount(0, 'data.items');
    });

    it('gets paginated orders', function () {
        $response = $this->getJson('/api/v1/orders?perPage=2&page=2');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'reference_no',
                            'sales_id',
                            'customer_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'links',
                    'meta' => [
                        'current_page',
                        'from',
                        'path',
                        'per_page',
                        'to',
                    ],
                ],
            ])
            ->assertJsonPath('data.meta.current_page', 2);
    });
});

describe('store', function () {
    it('creates an order', function () {
        $sales = App\Models\Sales::first();
        $customer = App\Models\Customer::first();
        $product = App\Models\Product::first();

        $response = $this->postJson('/api/v1/orders', [
            'reference_no' => 'ORD'.uniqid(),
            'sales_id' => $sales->id,
            'customer_id' => $customer->id,
            'items' => [
                [
                    'quantity' => 2,
                    'product_id' => $product->id,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'reference_no',
                    'sales_id',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    'items',
                ],
                'message',
            ]);
    });

    it('validates store order', function () {
        $response = $this->postJson('/api/v1/orders', [
            'reference_no' => '',
            'sales_id' => '',
            'customer_id' => '',
            'items' => [],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'sales_id',
                    'customer_id',
                    'items',
                ],
            ]);
    });
});

describe('show', function () {
    it('shows an order', function () {
        $order = App\Models\Order::first();
        $response = $this->getJson('/api/v1/orders/'.$order->id);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'reference_no',
                    'customer' => [
                        'id',
                        'name',
                    ],
                    'sales' => [
                        'id',
                        'user_id',
                        'user' => [
                            'id',
                            'name',
                        ],
                    ],
                    'items' => [
                        '*' => [
                            'quantity',
                            'product' => [
                                'id',
                                'name',
                                'production_price',
                                'selling_price',
                            ],
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    it('returns not found for missing order', function () {
        $response = $this->getJson('/api/v1/orders/999999999');

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Order not found');
    });
});

describe('update', function () {
    it('updates an order', function () {
        $order = App\Models\Order::first();
        $product = App\Models\Product::first();

        $response = $this->putJson('/api/v1/orders/'.$order->id, [
            'reference_no' => 'ORD'.uniqid(),
            'items' => [
                [
                    'quantity' => 3,
                    'product_id' => $product->id,
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'reference_no',
                    'sales_id',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    'items',
                ],
                'message',
            ]);
    });

    it('returns not found for update missing order', function () {
        $response = $this->putJson('/api/v1/orders/999999999', [
            'reference_no' => 'ORD'.uniqid(),
        ]);

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Order not found');
    });

    it('validates update order', function () {
        $order = App\Models\Order::first();
        $response = $this->putJson('/api/v1/orders/'.$order->id, [
            'reference_no' => '',
            'items' => [
                [
                    'quantity' => 0,
                    'product_id' => '',
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    });
});

describe('destroy', function () {
    it('deletes an order', function () {
        $order = App\Models\Order::first();
        $response = $this->deleteJson('/api/v1/orders/'.$order->id);

        $response->assertNoContent();
    });

    it('returns not found for delete missing order', function () {
        $response = $this->deleteJson('/api/v1/orders/999999999');

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Order not found');
    });
});
