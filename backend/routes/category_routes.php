<?php

use OpenApi\Attributes as OA;

class CategoryRoutes
{
    #[OA\Get(path: '/categories', tags: ['Categories'], summary: 'List categories with their number of products',
        responses: [
            new OA\Response(response: 200, description: 'List of categories',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Category'))),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ])]
    public static function getAll()
    {
        Flight::json(Flight::categoryService()->getAll());
    }

    #[OA\Get(path: '/categories/{id}', tags: ['Categories'], summary: 'Get one category',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The category', content: new OA\JsonContent(ref: '#/components/schemas/Category')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::categoryService()->getById($id));
    }

    #[OA\Post(path: '/categories', tags: ['Categories'], summary: 'Create a category',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/Category')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::categoryService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/categories/{id}', tags: ['Categories'], summary: 'Update a category',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/Category')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::categoryService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/categories/{id}', tags: ['Categories'], summary: 'Delete a category (not allowed while it has products)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::categoryService()->delete($id);
        Flight::json(['message' => 'Category deleted successfully']);
    }
}

Flight::route('GET /categories', [CategoryRoutes::class, 'getAll']);
Flight::route('GET /categories/@id:[0-9]+', [CategoryRoutes::class, 'getById']);
Flight::route('POST /categories', [CategoryRoutes::class, 'create']);
Flight::route('PUT /categories/@id:[0-9]+', [CategoryRoutes::class, 'update']);
Flight::route('DELETE /categories/@id:[0-9]+', [CategoryRoutes::class, 'delete']);
