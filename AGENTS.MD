# AGENTS.md

Context and rules for AI coding assistants working on this repository.

## Project

Tracksuit Couture is an online shop for tracksuits, built as a single-page application for the IT 2001 Web Programming course. Customers browse products, choose size and colour, order and review. Admins manage all data.

## Technology stack

- Frontend: HTML, CSS, JavaScript, jQuery, Bootstrap 5, Bootstrap Icons. No frontend frameworks (no React, Vue, Angular).
- Backend: plain PHP 8.1+ with the FlightPHP framework (v3) for the REST API, installed with Composer.
- Database: MySQL, accessed only through PDO with prepared statements.
- Auth: JWT. API docs: OpenAPI with Swagger UI.
- External API: Frankfurter (currency rates).

## Architecture rules

1. Frontend and backend are fully separated. The backend returns only JSON. The frontend contains only HTML, CSS and JS, with no PHP.
2. The frontend is a SPA. `frontend/js/router.js` loads files from `frontend/views/` into `<main id="app">` based on the URL hash. Never use full page reloads for navigation.
3. Backend layers, in this order: **route → middleware → service → DAO → database**.
   - Routes only read the request (`Http::body()`, `Http::query()`), call a service through `Flight::xxxService()` and return JSON with `Flight::json($data, $status)`. No SQL and no validation in routes.
   - Each route file defines a `XxxRoutes` class with static methods; each method has its `#[OA\...]` attribute above it, and the `Flight::route()` lines are at the bottom of the file.
   - Services hold business logic and validation, and are the only layer that calls DAOs. Every entity service extends `BaseService` and implements `validate($data, $isUpdate, $id)`, which returns only the fields that may be saved.
   - Expected errors are thrown as `ApiException($message, $status, $details)` from services. Never echo or `exit` from a service.
   - Steps that change several tables (placing an order, cancelling, changing order items) run in a transaction: `beginTransaction()`, `commit()`, and `rollBack()` in `catch`.
   - DAOs only run SQL. Every DAO extends `BaseDao` (getAll, getById, add, update, delete) and passes its table name and list of writable columns to the parent constructor. Only those columns may appear in INSERT/UPDATE.
   - The PDO connection is created only in `backend/dao/Database.php`, from `config/config.php`.
   - `UserDao` never returns `password_hash`, except `getByEmail()` which is used for login.
4. All SQL uses prepared statements. Never concatenate user input into SQL.
5. Secrets (DB credentials, JWT secret, API keys) live only in `backend/config/config.php`, which is gitignored. `config.example.php` is the committed template.
6. Middleware is registered in FlightPHP once and reused, never copied into individual routes.
7. Errors returned to the client never contain stack traces, SQL or file paths. The error handler in `index.php` turns `ApiException` into its status code and everything else into a generic 500, and writes the real error to `logs/error.log` with `Logger::error()`.
8. The external API (Frankfurter) is only called from `CurrencyService`, with a timeout from config. A failure is logged and must never break the shop.
9. Every new endpoint must be documented with OpenAPI attributes. Shared schemas live in `backend/openapi/Schemas.php` (one class per schema).

## Folder structure

```
backend/  config/ dao/ logs/ middleware/ openapi/ routes/ services/ swagger/ utils/ index.php composer.json
frontend/ assets/ css/ js/ views/ index.html
database/ tracksuit_couture.sql (schema + sample data)
docs/     ERD.md AI_LOG.md
```

## Naming conventions

- Database tables and columns: `snake_case`, plural table names (`users`, `product_variants`).
- PHP classes: `PascalCase` with a suffix for the layer: `ProductDao`, `ProductService`. One class per file, file name equals class name.
- Route files: `backend/routes/product_routes.php` containing class `ProductRoutes`.
- Services: `backend/services/ProductService.php`, registered in `index.php` as `Flight::register('productService', 'ProductService')`.
- API endpoints: plural nouns, lowercase, kebab-case: `/products`, `/products/{id}`, `/order-items`. Update is `PUT` and changes only the sent fields. Ids in routes use `@id:[0-9]+`.
- Status codes: 200 read/update/delete, 201 create, 400 validation, 404 not found, 500 server error, 503 external API down.
- JS: `camelCase` for variables and functions.
- HTML views: lowercase file names matching the route name (`#shop` → `views/shop.html`).
- CSS: custom classes are prefixed with `tc-` to avoid clashing with Bootstrap.

## Rules the AI must follow

- Keep code simple and readable. The author must be able to explain every line at the defense, so avoid clever one-liners and unnecessary abstractions.
- Do not add new libraries or frameworks without being asked.
- Do not put AJAX calls in views. From Milestone 5, only files in `frontend/js/services/` talk to the backend.
- Do not commit or suggest committing `config.php`, logs or `vendor/`.
- Do not invent database columns. The source of truth is `database/tracksuit_couture.sql`. When the schema changes, update the `.sql` file and `docs/ERD.md` together.
- Prices are stored in BAM. Converted prices from the external API are never stored in the database.
- Do not use em dashes in documentation text.
