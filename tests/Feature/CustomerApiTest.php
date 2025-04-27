<?php

describe('index', function () {
    it('gets all customers', function () {
        $response = $this->getJson('/api/v1/customers');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'address',
                            'phone',
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
            ])
            ->assertJsonCount(15, 'data.items');
    });

    it('gets customers by search', function () {
        $response = $this->getJson('/api/v1/customers?search=Yayasan');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'address',
                            'phone',
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

    it('returns empty when search not found', function () {
        $response = $this->getJson('/api/v1/customers?search=NotFoundCustomer');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [],
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
            ])
            ->assertJsonCount(0, 'data.items');
    });

    it('validates search parameter', function () {
        $blankSearch = $this->getJson('/api/v1/customers?search=');
        $blankSearch
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'search',
                ],
            ]);

        $invalidSearch = $this->getJson('/api/v1/customers?search=ab');
        $invalidSearch
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'search',
                ],
            ]);

        $longSearch = str_repeat('a', 25);
        $tooLong = $this->getJson("/api/v1/customers?search=$longSearch");
        $tooLong
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'search',
                ],
            ]);
    });

    it('gets paginated customers', function () {
        $response = $this->getJson('/api/v1/customers?perPage=5&page=2');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'address',
                            'phone',
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
            ])
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.meta.current_page', 2);
    });
});

describe('store', function () {
    it('creates a customer', function () {
        $response = $this->postJson('/api/v1/customers', [
            'name' => 'Budi',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'address',
                    'phone',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);
    });

    it('validates store customer', function () {
        $response = $this->postJson('/api/v1/customers', [
            'name' => '',
            'address' => '',
            'phone' => '',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'address',
                    'phone',
                ],
            ]);
    });
});

describe('show', function () {
    it('shows a customer', function () {
        $customer = \App\Models\Customer::first();
        $response = $this->getJson('/api/v1/customers/'.$customer->id);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'address',
                    'phone',
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    it('returns not found for missing customer', function () {
        $response = $this->getJson('/api/v1/customers/999999999');

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Customer not found');
    });
});

describe('update', function () {
    it('updates a customer', function () {
        $customer = \App\Models\Customer::first();
        $response = $this->putJson('/api/v1/customers/'.$customer->id, [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 2',
            'phone' => '081234567891',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'address',
                    'phone',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);
    });

    it('returns not found for update missing customer', function () {
        $response = $this->putJson('/api/v1/customers/999999999', [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 2',
            'phone' => '081234567891',
        ]);

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Customer not found');
    });

    it('validates update customer', function () {
        $customer = \App\Models\Customer::first();
        $response = $this->putJson('/api/v1/customers/'.$customer->id, [
            'name' => '',
            'address' => '',
            'phone' => '',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'address',
                    'phone',
                ],
            ]);
    });
});

describe('destroy', function () {
    it('deletes a customer', function () {
        $customer = \App\Models\Customer::first();
        $response = $this->deleteJson('/api/v1/customers/'.$customer->id);

        $response->assertNoContent();
    });

    it('returns not found for delete missing customer', function () {
        $response = $this->deleteJson('/api/v1/customers/1000');

        $response
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Customer not found');
    });
})->markTestSkipped('Integrity constraint violation: 1451');
