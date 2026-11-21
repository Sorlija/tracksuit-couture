<?php

use OpenApi\Attributes as OA;

class VariantRoutes
{
    #[OA\Get(path: '/variants', tags: ['Variants'], summary: 'List variants',
        parameters: [
            new OA\Parameter(name: 'product_id', in: 'query', required: false, description: 'Only variants of this product',
                schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'low_stock', in: 'query', required: false,
                description: 'Only variants with stock at or below this number (admin "low stock" list)',
                schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of variants',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Variant'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getAll()
    {
        $productId = Http::query('product_id');
        $lowStock = Http::query('low_stock');

        if ($productId !== null) {
            Flight::json(Flight::variantService()->getByProduct($productId));
        } elseif ($lowStock !== null) {
            Flight::json(Flight::variantService()->getLowStock((int) $lowStock));
        } else {
            Flight::json(Flight::variantService()->getAll());
        }
    }

    #[OA\Get(path: '/variants/{id}', tags: ['Variants'], summary: 'Get one variant',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The variant', content: new OA\JsonContent(ref: '#/components/schemas/Variant')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::variantService()->getById($id));
    }

    #[OA\Post(path: '/variants', tags: ['Variants'], summary: 'Create a variant (size + colour of a product)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/VariantInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/Variant')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::variantService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/variants/{id}', tags: ['Variants'], summary: 'Update a variant (e.g. change stock)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/VariantInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/Variant')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::variantService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/variants/{id}', tags: ['Variants'], summary: 'Delete a variant (not allowed if it was ordered)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::variantService()->delete($id);
        Flight::json(['message' => 'Variant deleted successfully']);
    }
}

Flight::route('GET /variants', [VariantRoutes::class, 'getAll']);
Flight::route('GET /variants/@id:[0-9]+', [VariantRoutes::class, 'getById']);
Flight::route('POST /variants', [VariantRoutes::class, 'create']);
Flight::route('PUT /variants/@id:[0-9]+', [VariantRoutes::class, 'update']);
Flight::route('DELETE /variants/@id:[0-9]+', [VariantRoutes::class, 'delete']);
