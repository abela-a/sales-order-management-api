<?php

use App\Helpers\AbstractApi;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // optional: you can setup some common stuff here
    $this->apiKey = 'fake-api-key';
    config()->set('services.abstract_api.phonevalidation_api_key', $this->apiKey);

    $this->api = new AbstractApi('phonevalidation');
});

describe('GET method', function () {
    it('successfully sends a GET request', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => Http::response(['valid' => true], 200),
        ]);

        $response = $this->api->get('validate', ['phone' => '1234567890']);

        expect($response)->toBeArray();
        expect($response['valid'])->toBeTrue();

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   $request->url() === 'https://phonevalidation.abstractapi.com/v1/validate?phone=1234567890&api_key=fake-api-key';
        });
    });

    it('handles a failed GET request', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => Http::response('Server Error', 500),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API request failed with status 500: Server Error');

        $this->api->get('validate', ['phone' => '1234567890']);
    });

    it('handles a connection exception during GET', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection failed'),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API connection error: Connection failed');

        $this->api->get('validate', ['phone' => '1234567890']);
    });
});

describe('POST method', function () {
    it('successfully sends a POST request', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->api->post('create', ['phone' => '1234567890']);

        expect($response)->toBeArray();
        expect($response['success'])->toBeTrue();

        Http::assertSent(function ($request) {
            return $request->method() === 'POST' &&
                   $request->url() === 'https://phonevalidation.abstractapi.com/v1/create' &&
                   $request['phone'] === '1234567890' &&
                   $request['api_key'] === 'fake-api-key';
        });
    });

    it('handles a failed POST request', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => Http::response('Server Error', 500),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API request failed with status 500: Server Error');

        $this->api->post('create', ['phone' => '1234567890']);
    });

    it('handles a connection exception during POST', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection failed'),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API connection error: Connection failed');

        $this->api->post('create', ['phone' => '1234567890']);
    });
});

describe('validatePhone method', function () {
    it('validates a phone number', function () {
        Http::fake([
            'https://phonevalidation.abstractapi.com/v1/*' => Http::response(['valid' => true], 200),
        ]);

        $response = $this->api->validatePhone('081234567890', 'ID');

        expect($response)->toBeArray();
        expect($response['valid'])->toBeTrue();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://phonevalidation.abstractapi.com/v1/?phone=081234567890&country=ID&api_key=fake-api-key';
        });
    });
});
