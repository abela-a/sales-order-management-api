<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\QueryOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Order',
    description: 'API endpoints for managing orders',
)]
class OrderController extends Controller
{
    private OrderRepositoryInterface $orderRepositoryInterface;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
    }

    #[
        OA\Get(
            path: '/orders',
            tags: ['Order'],
            summary: 'Get all orders',
            description: 'Get all orders with optional query parameters to filter, sort, and search orders.',
            operationId: 'order.index',
            parameters: [
                new OA\Parameter(
                    name: 'reference_no',
                    in: 'query',
                    description: 'Filter orders by reference number',
                    required: false,
                    schema: new OA\Schema(type: 'string'),
                ),
                new OA\Parameter(
                    name: 'sales_id',
                    in: 'query',
                    description: 'Filter orders by sales ID',
                    required: false,
                    schema: new OA\Schema(type: 'integer'),
                ),
                new OA\Parameter(
                    name: 'customer_id',
                    in: 'query',
                    description: 'Filter orders by customer ID',
                    required: false,
                    schema: new OA\Schema(type: 'integer'),
                ),
                new OA\Parameter(
                    name: 'perPage',
                    in: 'query',
                    description: 'Number of orders per page',
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
    public function index(QueryOrderRequest $request)
    {
        $orders = $this->orderRepositoryInterface->index($request->validated());

        return ApiResponse::sendResponse(new OrderCollection($orders), '', 200);
    }

    #[
        OA\Post(
            path: '/orders',
            tags: ['Order'],
            summary: 'Create a new order',
            description: 'Create a new order with the provided data.',
            operationId: 'order.store',
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['reference_no', 'sales_id', 'customer_id', 'items'],
                        properties: [
                            new OA\Property(property: 'reference_no', type: 'string'),
                            new OA\Property(property: 'sales_id', type: 'integer'),
                            new OA\Property(property: 'customer_id', type: 'integer'),
                            new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
                        ],
                        example: [
                            'reference_no' => 'INV00000000000000000',
                            'sales_id' => 1,
                            'customer_id' => 1,
                            'items' => [
                                [
                                    'product_id' => 1,
                                    'quantity' => 2,
                                ],
                                [
                                    'product_id' => 2,
                                    'quantity' => 1,
                                ],
                            ],
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
    public function store(StoreOrderRequest $request)
    {
        $request = $request->validated();

        DB::beginTransaction();

        try {
            $order = $this->orderRepositoryInterface->store($request);

            DB::commit();

            return ApiResponse::sendResponse(new OrderResource($order), 'Order created successfully', 201);
        } catch (\Exception $exception) {
            return ApiResponse::rollback($exception);
        }
    }

    #[
        OA\Get(
            path: '/orders/{id}',
            tags: ['Order'],
            summary: 'Get order by ID',
            description: 'Get order by ID.',
            operationId: 'order.show',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Order ID',
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
            $order = $this->orderRepositoryInterface->show($id);

            return ApiResponse::sendResponse(new OrderResource($order), '', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Order not found');
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }

    #[
        OA\Put(
            path: '/orders/{id}',
            tags: ['Order'],
            summary: 'Update order by ID',
            description: 'Update order by ID with the provided data.',
            operationId: 'order.update',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Order ID',
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
                        required: ['reference_no', 'sales_id', 'customer_id', 'items'],
                        properties: [
                            new OA\Property(property: 'reference_no', type: 'string'),
                            new OA\Property(property: 'sales_id', type: 'integer'),
                            new OA\Property(property: 'customer_id', type: 'integer'),
                            new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
                        ],
                        example: [
                            'reference_no' => 'INV00000000000000001',
                            'sales_id' => 2,
                            'customer_id' => 2,
                            'items' => [
                                [
                                    'product_id' => 2,
                                    'quantity' => 4,
                                ],
                                [
                                    'product_id' => 3,
                                    'quantity' => 5,
                                ],
                            ],
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
    public function update(UpdateOrderRequest $request, $id)
    {
        $request = $request->validated();

        DB::beginTransaction();

        try {
            $order = $this->orderRepositoryInterface->update($request, $id);

            DB::commit();

            return ApiResponse::sendResponse(new OrderResource($order), 'Order updated successfully', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Order not found');
        } catch (\Exception $exception) {
            return ApiResponse::rollback($exception);
        }
    }

    #[
        OA\Delete(
            path: '/orders/{id}',
            tags: ['Order'],
            summary: 'Delete order by ID',
            description: 'Delete order by ID.',
            operationId: 'order.destroy',
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    description: 'Order ID',
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
            $this->orderRepositoryInterface->delete($id);

            return ApiResponse::sendResponse([], 'Order deleted successfully', 204);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::notFound('Order not found');
        } catch (\Exception $exception) {
            return ApiResponse::throw($exception);
        }
    }
}
