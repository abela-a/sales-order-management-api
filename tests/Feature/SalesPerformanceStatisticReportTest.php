<?php

$config = [
    'url' => '/api/v1/reports/sales/performance-statistic',
];

describe('Sales Performance Statistic Report', function () use ($config) {
    it('returns sales performance statistic report', function () use ($config) {
        $response = $this->getJson($config['url'].'?month=3&is_underperform=true');

        $response->assertOk();
        $response->assertJsonStructure([
            'is_underperform',
            'month',
            'items' => [
                '*' => [
                    'sales',
                    'revenue' => [
                        'amount',
                        'abbreviation',
                    ],
                    'target' => [
                        'amount',
                        'abbreviation',
                    ],
                    'percentage',
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'is_underperform' => true,
            'month' => 'March '.date('Y'),
        ]);
    });

    it('returns validation error if sales does not exist', function () use ($config) {
        $response = $this->getJson($config['url'].'?month=13&is_underperform=invalid');

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'The month field must be between 1 and 12. (and 1 more error)',
                'errors' => [
                    'month' => ['The month field must be between 1 and 12.'],
                    'is_underperform' => ['The selected is underperform is invalid.'],
                ],
            ]);
    });
});
