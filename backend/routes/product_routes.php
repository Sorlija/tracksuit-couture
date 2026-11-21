<?php

use OpenApi\Attributes as OA;

class ProductRoutes
{
    #[OA\Get(path: '/products', tags: ['Products'], summary: 'List products',
        parameters: [
            new OA\Parameter(name: 'active', in: 'query', required: false,
                description: '1 = only products visible in the shop (also returns avg_rating and review_count)',
                schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, description: 'Only products of this category',
                schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of products',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Product'))),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ])]
    public static function getAll()
    {
        $activeOnly = Http::query('active') === '1';
        Flight::json(Flight::productService()->getAll($activeOnly, Http::query('category_id')));
    }

    #[OA\Get(path: '/products/{id}', tags: ['Products'], summary: 'Get one product',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The product', content: new OA\JsonContent(ref: '#/components/schemas/Product')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::productService()->getById($id));
    }

    #[OA\Get(path: '/products/{id}/variants', tags: ['Products'], summary: 'Get the sizes and colours of a product',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'Variants of the product',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Variant'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getVariants($id)
    {
        Flight::json(Flight::variantService()->getByProduct($id));
    }

    #[OA\Get(path: '/products/{id}/reviews', tags: ['Products'], summary: 'Get the reviews of a product',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'Reviews of the product, newest first',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Review'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getReviews($id)
    {
        Flight::json(Flight::reviewService()->getAll($id));
    }

    #[OA\Post(path: '/products', tags: ['Products'], summary: 'Create a product',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/Product')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::productService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/products/{id}', tags: ['Products'], summary: 'Update a product (only the sent fields change)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/Product')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::productService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/products/{id}', tags: ['Products'], summary: 'Delete a product (not allowed if it was ordered, deactivate it instead)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::productService()->delete($id);
        Flight::json(['message' => 'Product deleted successfully']);
    }
}

Flight::route('GET /products', [ProductRoutes::class, 'getAll']);
Flight::route('GET /products/@id:[0-9]+', [ProductRoutes::class, 'getById']);
Flight::route('GET /products/@id:[0-9]+/variants', [ProductRoutes::class, 'getVariants']);
Flight::route('GET /products/@id:[0-9]+/reviews', [ProductRoutes::class, 'getReviews']);
Flight::route('POST /products', [ProductRoutes::class, 'create']);
Flight::route('PUT /products/@id:[0-9]+', [ProductRoutes::class, 'update']);
Flight::route('DELETE /products/@id:[0-9]+', [ProductRoutes::class, 'delete']);
