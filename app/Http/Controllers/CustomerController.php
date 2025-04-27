<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\QueryCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\CustomerResource;
use App\Interfaces\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Customer',
    description: 'API endpoints for managing customers',
)]
class CustomerController extends Controller
{
    private CustomerRepositoryInterface $customerRepositoryInterface;

    public function __construct(CustomerRepositoryInterface $customerRepositoryInterface)
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    #[
        OA\Get(
            path: '/customers',
            tags: ['Customer'],
            summary: 'Get all customers',
            description: 'Get all customers with optional query parameters to filter, sort, and search customers.',
            operationId: 'customer.index',
            parameters: [
                new OA\Parameter(
                    name: 'search',
                    in: 'query',
                    description: 'Search customers by name',
                    required: false,
                    schema: new OA\Schema(type: 'string'),
                ),
                new OA\Parameter(
                    name: 'perPage',
                    in: 'query',
                    description: 'Number of customers per page',
                    required: false,
                    schema: new OA\Schema(type: 'integer'),
                ),
                new OA\Parameter(
                    name: 'page',
                    in: 'query',
                    description: 'Page number',
                    required: false,
                    schema: new OA\Schema(type: 'integer'),
                ),
            ],
            responses: [
                new OA\Response(response: Response::HTTP_OK, description: 'The request has succeeded'),
                new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
                new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The request was well-formed but contains semantic errors'),
                new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
            ],
        ),
    ]
    public function index(QueryCustomerRequest $request)
    {
        $customers = $this->customerRepositoryInterface->index($request->validated());

        return ApiResponse::sendResponse(new CustomerCollection($customers), '', 200);
    }

    #[
        OA\Post(
            path: '/customers',
            tags: ['Customer'],
            summary: 'Create a new customer',
            description: 'Create a new customer with the provided data.',
            operationId: 'customer.store',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['name', 'address', 'phone'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'address', type: 'string'),
                            new OA\Property(property: 'phone', type: 'string'),
                        ],
                        example: [
                            'name' => 'Budi',
                            'address' => 'Jl. Merdeka No. 1, Jakarta',
                            'phone' => '081234567890',
                        ],
                    )
                )
            ),
            responses: [
                new OA\Response(response: Response::HTTP_CREATED, description: 'The resource has been created'),
                new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
                new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The request was well-formed but contains semantic errors'),
                new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
            ],
        )
    ]
    public function store(StoreCustomerRequest $request)
    {
        $request = $request->validated();

        DB::beginTransaction();

        try {
            $customer = $this->customerRepositoryInterface->store($request);

            DB::commit();

            return ApiResponse::sendResponse(new CustomerResource($customer), 'Customer created successfully', 201);
        } catch (\Exception $exception) {
            return ApiResponse::rollback($exception);
        }
    }

    #[
        OA\Get(
            path: '/customers/{id}',
            tags: ['Customer'],
            summary: 'Get customer by ID',
            description: 'Get customer by ID.',
            operationId: 'customer.show',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Customer ID',
                    required: true,
                    schema: new OA\Schema(type: 'integer'),
                ),
            ],
            responses: [
                new OA\Response(response: Response::HTTP_OK, description: 'The request has succeeded'),
                new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The requested resource could not be found'),
                new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
                new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The request was well-formed but contains semantic errors'),
                new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
            ],
        )
    ]
    public function show($id)
    {
        try {
            $customer = $this->customerRepositoryInterface->show($id);

            return ApiResponse::sendResponse(new CustomerResource($customer), '', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Customer not found');
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }

    #[
        OA\Put(
            path: '/customers/{id}',
            tags: ['Customer'],
            summary: 'Update customer by ID',
            description: 'Update customer by ID with the provided data.',
            operationId: 'customer.update',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Customer ID',
                    required: true,
                    schema: new OA\Schema(type: 'integer'),
                ),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'address', type: 'string'),
                            new OA\Property(property: 'phone', type: 'string'),
                        ],
                        example: [
                            'name' => 'Budi Santoso',
                            'address' => 'Jl. Merdeka No. 1, Jakarta Pusat',
                            'phone' => '081234567891',
                        ],
                    )
                )
            ),
            responses: [
                new OA\Response(response: Response::HTTP_OK, description: 'The request has succeeded'),
                new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The requested resource could not be found'),
                new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
                new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The request was well-formed but contains semantic errors'),
                new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
            ],
        )
    ]
    public function update(UpdateCustomerRequest $request, $id)
    {
        $request = $request->validated();

        DB::beginTransaction();

        try {
            $customer = $this->customerRepositoryInterface->update($request, $id);

            DB::commit();

            return ApiResponse::sendResponse(new CustomerResource($customer), 'Customer updated successfully', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Customer not found');
        } catch (\Exception $exception) {
            return ApiResponse::rollback($exception);
        }
    }

    #[
        OA\Delete(
            path: '/customers/{id}',
            tags: ['Customer'],
            summary: 'Delete customer by ID',
            description: 'Delete customer by ID.',
            operationId: 'customer.destroy',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Customer ID',
                    required: true,
                    schema: new OA\Schema(type: 'integer'),
                ),
            ],
            responses: [
                new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'The request has succeeded and the resource has been deleted'),
                new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'The requested resource could not be found'),
                new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The server cannot process the request due to client error'),
                new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Something went wrong on the server'),
            ],
        )
    ]
    public function destroy($id)
    {
        try {
            $this->customerRepositoryInterface->delete($id);

            return ApiResponse::sendResponse([], 'Customer deleted successfully', 204);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Customer not found');
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }
}
