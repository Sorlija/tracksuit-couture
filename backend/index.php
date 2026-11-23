<?php

/*
 * Entry point of the REST API. Every request is sent here (see .htaccess),
 * and FlightPHP sends it to the matching route.
 *
 * Request flow:  route (routes/*.php) -> service (services/*.php) -> DAO (dao/*.php) -> MySQL
 */

// PHP built-in server (php -S): let it serve real files like swagger/index.html directly
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

// Errors are returned as JSON and written to the error log, never printed into the response
ini_set('display_errors', '0');

// Log times in local time
date_default_timezone_set('Europe/Sarajevo');

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/utils/Config.php';
require_once __DIR__ . '/utils/ApiException.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/utils/Http.php';

require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/CategoryService.php';
require_once __DIR__ . '/services/ProductService.php';
require_once __DIR__ . '/services/ProductVariantService.php';
require_once __DIR__ . '/services/OrderService.php';
require_once __DIR__ . '/services/OrderItemService.php';
require_once __DIR__ . '/services/ReviewService.php';
require_once __DIR__ . '/services/CurrencyService.php';

// ---------- Services ----------
// Flight::productService() returns one shared ProductService for the whole request
Flight::register('userService', 'UserService');
Flight::register('categoryService', 'CategoryService');
Flight::register('productService', 'ProductService');
Flight::register('variantService', 'ProductVariantService');
Flight::register('orderService', 'OrderService');
Flight::register('orderItemService', 'OrderItemService');
Flight::register('reviewService', 'ReviewService');
Flight::register('currencyService', 'CurrencyService');

// ---------- CORS ----------
// Lets the frontend call the API when it runs on another port/domain (e.g. php -S during development)
Flight::before('start', function () {
    Flight::response()
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    // The browser sends an OPTIONS "preflight" request before some AJAX calls; answer it right away.
    // (Done here and not as an "OPTIONS *" route, because a catch-all route turns every unknown URL into 405.)
    if (Flight::request()->method === 'OPTIONS') {
        Flight::halt(204);
    }
});

// ---------- Error handling ----------
// ApiException (thrown by services) -> its own status code (400, 404, 503...) and message.
// Anything else -> 500 with a general message. The real error only goes to logs/error.log,
// so stack traces, SQL and file paths are never shown to the client.
Flight::map('error', function (Throwable $e) {
    $request = Flight::request();

    if ($e instanceof ApiException) {
        $status = $e->getStatusCode();
        $body = ['error' => $e->getMessage()];
        if (!empty($e->getDetails())) {
            $body['details'] = $e->getDetails();
        }
    } else {
        $status = 500;
        $body = ['error' => 'Something went wrong on the server. Please try again later.'];
        Logger::error(sprintf(
            '%s %s 500 %s: %s (%s:%d)',
            $request->method, $request->url, get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine()
        ));
    }

    Flight::response()->clearBody();
    Flight::jsonHalt($body, $status);
});

Flight::map('notFound', function () {
    Flight::jsonHalt(['error' => 'Endpoint not found'], 404);
});

Flight::map('methodNotFound', function ($route) {
    Flight::response()->header('Allow', implode(', ', $route->methods));
    Flight::jsonHalt(['error' => 'Method not allowed. Allowed: ' . implode(', ', $route->methods)], 405);
});

// ---------- Routes ----------
require_once __DIR__ . '/routes/user_routes.php';
require_once __DIR__ . '/routes/category_routes.php';
require_once __DIR__ . '/routes/product_routes.php';
require_once __DIR__ . '/routes/variant_routes.php';
require_once __DIR__ . '/routes/order_routes.php';
require_once __DIR__ . '/routes/order_item_routes.php';
require_once __DIR__ . '/routes/review_routes.php';
require_once __DIR__ . '/routes/currency_routes.php';
require_once __DIR__ . '/openapi/OpenApiSpec.php';
require_once __DIR__ . '/openapi/Schemas.php';

Flight::route('GET /', function () {
    Flight::json(['name' => 'Tracksuit Couture API', 'docs' => 'swagger/']);
});

// OpenAPI spec, generated from the #[OA\...] attributes in routes/ and openapi/
Flight::route('GET /openapi.json', function () {
    $generator = new \OpenApi\Generator(new \Psr\Log\NullLogger());
    $openapi = $generator->generate([__DIR__ . '/openapi', __DIR__ . '/routes']);

    $spec = json_decode($openapi->toJson(), true);
    $request = Flight::request();
    $spec['servers'] = [[
        'url' => $request->scheme . '://' . $request->host . rtrim($request->base, '/'),
        'description' => 'This server',
    ]];
    Flight::json($spec);
});

Flight::start();
