# AGENTS.md

Context and rules for AI coding assistants working on this repository.

## Project

Tracksuit Couture is an online shop for tracksuits, built as a single-page application for the IT 2001 Web Programming course. Customers browse products, choose size and colour, order and review. Admins manage all data.

## Technology stack

- Frontend: HTML, CSS, JavaScript, jQuery, Bootstrap 5, Bootstrap Icons. No frontend frameworks (no React, Vue, Angular).
- Backend: plain PHP with the FlightPHP framework for the REST API.
- Database: MySQL, accessed only through PDO with prepared statements.
- Auth: JWT. API docs: OpenAPI with Swagger UI.
- External API: Frankfurter (currency rates).

## Architecture rules

1. Frontend and backend are fully separated. The backend returns only JSON. The frontend contains only HTML, CSS and JS, with no PHP.
2. The frontend is a SPA. `frontend/js/router.js` loads files from `frontend/views/` into `<main id="app">` based on the URL hash. Never use full page reloads for navigation.
3. Backend layers, in this order: **route → middleware → service → DAO → database**.
   - Routes only read the request, call a service and return JSON with the correct status code.
   - Services hold business logic and validation, and are the only layer that calls DAOs.
   - DAOs only run SQL. Every DAO extends `BaseDao` (getAll, getById, add, update, delete) and passes its table name and list of writable columns to the parent constructor. Only those columns may appear in INSERT/UPDATE.
   - The PDO connection is created only in `backend/dao/Database.php`, from `config/config.php`.
   - `UserDao` never returns `password_hash`, except `getByEmail()` which is used for login.
4. All SQL uses prepared statements. Never concatenate user input into SQL.
5. Secrets (DB credentials, JWT secret, API keys) live only in `backend/config/config.php`, which is gitignored. `config.example.php` is the committed template.
6. Middleware is registered in FlightPHP once and reused, never copied into individual routes.
7. Errors returned to the client never contain stack traces, SQL or file paths.

## Folder structure

```
backend/  config/ dao/ logs/ middleware/ routes/ services/
frontend/ assets/ css/ js/ views/ index.html
database/ tracksuit_couture.sql (schema + sample data)
docs/     ERD.md AI_LOG.md
```

## Naming conventions

- Database tables and columns: `snake_case`, plural table names (`users`, `product_variants`).
- PHP classes: `PascalCase` with a suffix for the layer: `ProductDao`, `ProductService`. One class per file, file name equals class name.
- Route files: `backend/routes/product_routes.php`.
- API endpoints: plural nouns, lowercase: `/products`, `/products/{id}`, `/orders`.
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
