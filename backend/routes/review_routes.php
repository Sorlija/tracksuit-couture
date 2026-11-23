<?php

use OpenApi\Attributes as OA;

class ReviewRoutes
{
    #[OA\Get(path: '/reviews', tags: ['Reviews'], summary: 'List reviews',
        parameters: [new OA\Parameter(name: 'product_id', in: 'query', required: false, description: 'Only reviews of this product',
            schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'List of reviews, newest first',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Review'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getAll()
    {
        Flight::json(Flight::reviewService()->getAll(Http::query('product_id')));
    }

    #[OA\Get(path: '/reviews/{id}', tags: ['Reviews'], summary: 'Get one review',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The review', content: new OA\JsonContent(ref: '#/components/schemas/Review')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::reviewService()->getById($id));
    }

    #[OA\Post(path: '/reviews', tags: ['Reviews'], summary: 'Create a review (one per user per product)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReviewInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/Review')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::reviewService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/reviews/{id}', tags: ['Reviews'], summary: 'Update the rating or comment of a review',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReviewUpdate')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/Review')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::reviewService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/reviews/{id}', tags: ['Reviews'], summary: 'Delete a review',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::reviewService()->delete($id);
        Flight::json(['message' => 'Review deleted successfully']);
    }
}

Flight::route('GET /reviews', [ReviewRoutes::class, 'getAll']);
Flight::route('GET /reviews/@id:[0-9]+', [ReviewRoutes::class, 'getById']);
Flight::route('POST /reviews', [ReviewRoutes::class, 'create']);
Flight::route('PUT /reviews/@id:[0-9]+', [ReviewRoutes::class, 'update']);
Flight::route('DELETE /reviews/@id:[0-9]+', [ReviewRoutes::class, 'delete']);
