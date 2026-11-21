<?php

use OpenApi\Attributes as OA;

/*
 * Shared OpenAPI definitions: API info, tags and common responses/parameters.
 * The data schemas are in Schemas.php.
 * The endpoints themselves are documented next to their code in backend/routes/.
 * The JSON spec is generated at GET /openapi.json and shown in Swagger UI at /swagger/.
 */

#[OA\Info(
    version: '1.0.0',
    title: 'Tracksuit Couture API',
    description: 'REST API for the Tracksuit Couture online store. All prices are in BAM. Authentication (JWT) is added in Milestone 4.'
)]
#[OA\Tag(name: 'Users')]
#[OA\Tag(name: 'Categories')]
#[OA\Tag(name: 'Products')]
#[OA\Tag(name: 'Variants', description: 'Sizes and colours of a product, each with its own stock')]
#[OA\Tag(name: 'Orders')]
#[OA\Tag(name: 'Order items', description: 'Lines of an order')]
#[OA\Tag(name: 'Reviews')]
#[OA\Tag(name: 'Currency', description: 'External API: live exchange rates from Frankfurter (ECB)')]

// ---------- Common responses ----------
#[OA\Response(response: 'BadRequest', description: 'Validation failed or a business rule was broken',
    content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
#[OA\Response(response: 'NotFound', description: 'The resource does not exist',
    content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
#[OA\Response(response: 'ServerError', description: 'Unexpected server error (details are only in the error log)',
    content: new OA\JsonContent(ref: '#/components/schemas/Error'))]
#[OA\Response(response: 'Deleted', description: 'Deleted',
    content: new OA\JsonContent(ref: '#/components/schemas/Message'))]

// ---------- Common parameters ----------
#[OA\Parameter(parameter: 'Id', name: 'id', in: 'path', required: true, description: 'Id of the resource',
    schema: new OA\Schema(type: 'integer', example: 1))]
class OpenApiSpec
{
}
