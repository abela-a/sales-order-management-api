<?php

use App\Models\Customer;
use App\Models\Sales;

$config = [
    'url' => '/api/v1/reports/orders/monthly-trend',
];

describe('Order Monthly Trend Report', function () use ($config) {
    it('returns monthly trend report for orders', function () use ($config) {
        $customer = Customer::find(1);
        $sales = Sales::with('user:id,name')->find(1);

        $response = $this->getJson($config['url'].'?customer_id='.$customer->id.'&sales_id='.$sales->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'customer',
            'sales',
            'items' => [
                '*' => [
                    'name',
                    'data' => [
                        '*' => [
                            'x',
                            'y',
                        ],
                    ],
                ],
            ],
        ]);
        $response->assertJsonFragment(['customer' => $customer->name]);
        $response->assertJsonFragment(['sales' => $sales->user->name]);
    });

    it('returns validation error if customer does not exist', function () use ($config) {
        $response = $this->getJson($config['url'].'?customer_id=999999');

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'The selected customer id is invalid.',
                'errors' => [
                    'customer_id' => [
                        'The selected customer id is invalid.',
                    ],
                ],
            ]);
    });

    it('returns validation error if sales does not exist', function () use ($config) {
        $response = $this->getJson($config['url'].'?sales_id=999999');

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'The selected sales id is invalid.',
                'errors' => [
                    'sales_id' => [
                        'The selected sales id is invalid.',
                    ],
                ],
            ]);
    });
});
