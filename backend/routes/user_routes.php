<?php

use OpenApi\Attributes as OA;

class UserRoutes
{
    #[OA\Get(path: '/users', tags: ['Users'], summary: 'List users',
        parameters: [new OA\Parameter(name: 'role', in: 'query', required: false, description: 'Only users with this role',
            schema: new OA\Schema(type: 'string', enum: ['admin', 'customer']))],
        responses: [
            new OA\Response(response: 200, description: 'List of users (without password hashes)',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ])]
    public static function getAll()
    {
        $role = Http::query('role');
        $users = $role === null ? Flight::userService()->getAll() : Flight::userService()->getByRole($role);
        Flight::json($users);
    }

    #[OA\Get(path: '/users/{id}', tags: ['Users'], summary: 'Get one user',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'The user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getById($id)
    {
        Flight::json(Flight::userService()->getById($id));
    }

    #[OA\Get(path: '/users/{id}/orders', tags: ['Users'], summary: "Get a user's orders",
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, description: 'Orders of the user, newest first',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Order'))),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function getOrders($id)
    {
        Flight::userService()->getById($id); // 404 if the user does not exist
        Flight::json(Flight::orderService()->getAll($id));
    }

    #[OA\Post(path: '/users', tags: ['Users'], summary: 'Create a user',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        ])]
    public static function create()
    {
        Flight::json(Flight::userService()->add(Http::body()), 201);
    }

    #[OA\Put(path: '/users/{id}', tags: ['Users'], summary: 'Update a user (only the sent fields change)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function update($id)
    {
        Flight::json(Flight::userService()->update($id, Http::body()));
    }

    #[OA\Delete(path: '/users/{id}', tags: ['Users'], summary: 'Delete a user (not allowed if the user has orders)',
        parameters: [new OA\Parameter(ref: '#/components/parameters/Id')],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/Deleted'),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ])]
    public static function delete($id)
    {
        Flight::userService()->delete($id);
        Flight::json(['message' => 'User deleted successfully']);
    }
}

Flight::route('GET /users', [UserRoutes::class, 'getAll']);
Flight::route('GET /users/@id:[0-9]+', [UserRoutes::class, 'getById']);
Flight::route('GET /users/@id:[0-9]+/orders', [UserRoutes::class, 'getOrders']);
Flight::route('POST /users', [UserRoutes::class, 'create']);
Flight::route('PUT /users/@id:[0-9]+', [UserRoutes::class, 'update']);
Flight::route('DELETE /users/@id:[0-9]+', [UserRoutes::class, 'delete']);
