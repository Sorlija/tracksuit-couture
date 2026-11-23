<?php

use OpenApi\Attributes as OA;

class OrderRoutes
{
    #[OA\Get(path: '/orders', tags: ['Orders'], summary: 'List orders',
        parameters: [
            new OA\Parameter(name: 'user_id', in: 'query', required: false, description: 'Only orders of this user',
                schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Only orders with this status',
                schema: new OA\Schema(type: 'string', enum: ['processing', 'shipped', 'delivered', 'cancelled'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of orders, newest first',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Order'))),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function getAll()
    {
        Flight::json(Flight::orderService()->getAll(Http::query('user_id'), Http::query('status')));
    }

    #[OA\Get(path: '/orders/{id}', tags: ['Orders'], summary: 'Get one order with its items',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The order with items', content: new OA\JsonContent(ref: '#/components/schemas/Order')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::orderService()->getDetails($id));
    }

    #[OA\Post(path: '/orders', tags: ['Orders'], summary: 'Place an order',
        description: 'Prices come from the products and the total is calculated by the server. Stock is checked and lowered. Everything is saved in one transaction.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderCreate')),
        responses: [
            new OA\Response(response: 201, description: 'Created order with items', content: new OA\JsonContent(ref: '#/components/schemas/Order')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::orderService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/orders/{id}', tags: ['Orders'], summary: 'Update order status or shipping details',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderUpdate')),
        responses: [
            new OA\Response(response: 200, description: 'Updated order with items', content: new OA\JsonContent(ref: '#/components/schemas/Order')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::orderService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/orders/{id}', tags: ['Orders'], summary: 'Delete an order (stock is put back if it was not delivered or cancelled)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::orderService()->delete($id);
        Flight::json(['message' => 'Order deleted successfully']);
    }
}

Flight::route('GET /orders', [OrderRoutes::class, 'getAll']);
Flight::route('GET /orders/@id:[0-9]+', [OrderRoutes::class, 'getById']);
Flight::route('POST /orders', [OrderRoutes::class, 'create']);
Flight::route('PUT /orders/@id:[0-9]+', [OrderRoutes::class, 'update']);
Flight::route('DELETE /orders/@id:[0-9]+', [OrderRoutes::class, 'delete']);
