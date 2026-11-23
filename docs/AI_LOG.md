# AI usage log
 
## Milestone 1: Setup, static frontend, ERD
 
### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated the initial folder structure, `.gitignore`, `config.example.php`, the SPA router (`router.js`), the static Bootstrap views, CSS theme, draft ERD (Mermaid) and the first drafts of README.md and AGENTS.md.
- All generated code was reviewed, run locally and adjusted by me.
### Most important prompts
1. "Here are the project requirements (PDF). The project is Tracksuit Couture, an online tracksuit shop. Do Milestone 1: folder structure, static SPA frontend with all pages, draft ERD with at least 5 entities, README draft."
2. _TODO: add any follow-up prompts you use (e.g. changes to pages, design, ERD)._
### Cases where the AI was wrong or produced poor code
<!-- Required: at least 2 real cases. Fill these in from your own review of the code. -->
 
1. **What was wrong:** _TODO_
   **How I noticed:** _TODO_
   **How I fixed it:** _TODO_
2. **What was wrong:** _TODO_
   **How I noticed:** _TODO_
   **How I fixed it:** _TODO_
## Milestone 2: Database and DAO layer
 
### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated the MySQL schema with sample data (`database/tracksuit_couture.sql`), `Database.php`, `BaseDao.php` and the seven entity DAOs, and updated the ERD, README and AGENTS.md.
- I imported the `.sql` file in DBeaver and tested the DAO methods locally.
### Most important prompts
1. "Do Milestone 2: MySQL schema with at least 5 entities, primary and foreign keys, sample data in a .sql file, and a DAO layer with a BaseDao and CRUD for every entity using prepared statements. Credentials come from config.php."
2. _TODO: add any follow-up prompts you use._
### Cases where the AI was wrong or produced poor code
<!-- Required: at least 2 real cases from your own review. -->
 
1. **What was wrong:** _TODO_
   **How I noticed:** _TODO_
   **How I fixed it:** _TODO_
2. **What was wrong:** _TODO_
   **How I noticed:** _TODO_
   **How I fixed it:** _TODO_
## Milestone 3: Services, routes, OpenAPI and external API
 
### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated `BaseService` and the seven entity services with validation and business rules, the `CurrencyService` (Frankfurter), the FlightPHP route files with OpenAPI attributes, `index.php` with error handling, the Swagger page, and the README/AGENTS.md updates. It also wrote and ran an end-to-end test script against every endpoint.
- I installed the dependencies with Composer, ran the API locally, and tested the endpoints in Swagger UI.
- After comparing with the course example repository, I had Claude replace our own hash router with the course's `jquery.spapp` library (sections in `index.html`, views loaded from `views/`).
### Most important prompts
1. "Do Milestone 3: a service per entity with validation and business rules, FlightPHP routes for all CRUD operations with correct status codes, OpenAPI documentation with Swagger, and a Frankfurter currency endpoint with timeout and error handling."
2. "Compare the project with the course example repository and switch the frontend to the jquery.spapp library used in the course."
### Cases where the AI was wrong or produced poor code
<!-- Required: at least 2 real cases. The two below happened while generating this milestone; replace or add your own if you find others. -->
 
1. **What was wrong:** For CORS preflight, the AI first added a catch-all route `Flight::route('OPTIONS *', ...)`. Because that route matches every URL, FlightPHP treated every unknown URL as "wrong method", so `GET /nope` and `GET /users/abc` returned `405 Method Not Allowed` instead of `404 Not Found`.
   **How I noticed:** The test run for unknown endpoints failed (expected 404, got 405).
   **How I fixed it:** Removed the catch-all route and answered OPTIONS requests in `Flight::before('start', ...)` instead.
2. **What was wrong:** The AI put all OpenAPI `#[OA\Schema]` attributes on one class. swagger-php only reads one schema per class, so all 21 schemas were missing from the generated spec, and every `$ref` to them was broken.
   **How I noticed:** Validating `openapi.json` failed with a missing `components/schemas` error, even though all 40 endpoints were listed.
   **How I fixed it:** Moved each schema to its own empty class in `backend/openapi/Schemas.php`. The spec now validates.
 