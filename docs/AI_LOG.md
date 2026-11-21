# AI usage log

## Milestone 1: Setup, static frontend, ERD

### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated the initial folder structure, `.gitignore`, `config.example.php`, the SPA router (`router.js`), the static Bootstrap views, CSS theme, draft ERD (Mermaid) and the first drafts of README.md and AGENTS.md.
- All generated code was reviewed, run locally and adjusted by me.

### Most important prompts
1. "Here are the project requirements (PDF). The project is Tracksuit Couture, an online tracksuit shop. Do Milestone 1: folder structure, static SPA frontend with all pages, draft ERD with at least 5 entities, README draft."

### Cases where the AI was wrong or produced poor code
1. **What was wrong:** AI kept adding unnecessary comments for certain files. 
   **How I noticed:** By reading through all the code and reviewing everything thorougly. 
   **How I fixed it:** Deleted the comments, noted to AI not to repeat the same mistake. 

## Milestone 3: Services, routes, OpenAPI and external API

### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated `BaseService` and the seven entity services with validation and business rules, the `CurrencyService` (Frankfurter), the FlightPHP route files with OpenAPI attributes, `index.php` with error handling, the Swagger page, and the README/AGENTS.md updates. It also wrote and ran an end-to-end test script against every endpoint.
- I installed the dependencies with Composer, ran the API locally, and tested the endpoints in Swagger UI.

### Most important prompts
1. "Do Milestone 3: a service per entity with validation and business rules, FlightPHP routes for all CRUD operations with correct status codes, OpenAPI documentation with Swagger, and a Frankfurter currency endpoint with timeout and error handling."
2. _TODO: add any follow-up prompts you use._

### Cases where the AI was wrong or produced poor code
<!-- Required: at least 2 real cases. The two below happened while generating this milestone; replace or add your own if you find others. -->

1. **What was wrong:** For CORS preflight, the AI first added a catch-all route `Flight::route('OPTIONS *', ...)`. Because that route matches every URL, FlightPHP treated every unknown URL as "wrong method", so `GET /nope` and `GET /users/abc` returned `405 Method Not Allowed` instead of `404 Not Found`.
   **How I noticed:** The test run for unknown endpoints failed (expected 404, got 405).
   **How I fixed it:** Removed the catch-all route and answered OPTIONS requests in `Flight::before('start', ...)` instead.

2. **What was wrong:** The AI put all OpenAPI `#[OA\Schema]` attributes on one class. swagger-php only reads one schema per class, so all 21 schemas were missing from the generated spec, and every `$ref` to them was broken.
   **How I noticed:** Validating `openapi.json` failed with a missing `components/schemas` error, even though all 40 endpoints were listed.
   **How I fixed it:** Moved each schema to its own empty class in `backend/openapi/Schemas.php`. The spec now validates.
