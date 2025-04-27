<?php

use App\Models\Sales;

$config = [
    'url' => '/api/v1/reports/sales/target-vs-actual-revenue',
];

describe('Sales Target vs Actual Revenue Report', function () use ($config) {
    it('returns target, revenue, and income from sales', function () use ($config) {
        $sales = Sales::with('user:id,name')->find(1);

        $response = $this->getJson($config['url'].'?sales_id='.$sales->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'sales',
            'year',
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
        $responseData = $response->json();

        $itemNames = collect($responseData['items'])->pluck('name')->all();
        $this->assertContains('Target', $itemNames);
        $this->assertContains('Revenue', $itemNames);
        $this->assertContains('Income', $itemNames);

        $response->assertJsonFragment(['sales' => $sales->user->name]);
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
