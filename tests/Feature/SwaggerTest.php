<?php

describe('Swagger API Documentation', function () {
    it('can generate Swagger documentation', function () {
        $this->artisan('l5-swagger:generate')->assertExitCode(0);
    });

    it('can access the Swagger UI', function () {
        $response = $this->get('/api/documentation');
        $response->assertStatus(200);
    });

    it('can access the Swagger JSON', function () {
        $response = $this->get('/docs?api-docs.json');
        $response->assertStatus(200);
    });

    it('has a api-docs.json file in storage', function () {
        $this->assertFileExists(storage_path('api-docs/api-docs.json'));
    });
});
