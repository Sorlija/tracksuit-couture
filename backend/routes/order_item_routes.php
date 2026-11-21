<?php

use OpenApi\Attributes as OA;

class OrderItemRoutes
{
    #[OA\Get(path: '/order-items', tags: ['Order items'], summary: 'List order items',
        parameters: [new OA\Parameter(name: 'order_id', in: 'query', required: false,
            description: 'Only the items of this order (also returns product name, size, colour and line total)',
            schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'List of order items',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItem'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getAll()
    {
        Flight::json(Flight::orderItemService()->getAll(Http::query('order_id')));
    }

    #[OA\Get(path: '/order-items/{id}', tags: ['Order items'], summary: 'Get one order item',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The order item', content: new OA\JsonContent(ref: '#/components/schemas/OrderItem')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::orderItemService()->getById($id));
    }

    #[OA\Post(path: '/order-items', tags: ['Order items'], summary: 'Add an item to an order that is still processing',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderItemInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/OrderItem')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function create()
    {
        Flight::json(Flight::orderItemService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/order-items/{id}', tags: ['Order items'], summary: 'Change the quantity of an order item',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderItemUpdate')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/OrderItem')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::orderItemService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/order-items/{id}', tags: ['Order items'], summary: 'Remove an item from an order (puts the stock back)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::orderItemService()->delete($id);
        Flight::json(['message' => 'Order item deleted successfully']);
    }
}

Flight::route('GET /order-items', [OrderItemRoutes::class, 'getAll']);
Flight::route('GET /order-items/@id:[0-9]+', [OrderItemRoutes::class, 'getById']);
Flight::route('POST /order-items', [OrderItemRoutes::class, 'create']);
Flight::route('PUT /order-items/@id:[0-9]+', [OrderItemRoutes::class, 'update']);
Flight::route('DELETE /order-items/@id:[0-9]+', [OrderItemRoutes::class, 'delete']);
