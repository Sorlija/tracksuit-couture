# Tracksuit Couture
 
Tracksuit Couture is an online shop for premium tracksuits. Customers can browse tracksuits by category, pick a size and colour, see prices in their own currency, place orders and leave reviews. Admins manage products, variants, categories, orders, users and reviews from an admin dashboard.
 
Built as a single-page application for **IT 2001 Web Programming** (International Burch University).
 
> Status: **Milestone 3** (services, REST API, OpenAPI/Swagger, external API). This README is updated in every milestone.
 
## Tech stack
 
- **Frontend:** HTML, CSS, JavaScript, jQuery, spapp (hash-based SPA router), Bootstrap 5, Bootstrap Icons
- **Backend:** PHP, FlightPHP (REST API), PDO
- **Database:** MySQL
- **Auth:** JWT (Milestone 4)
- **API docs:** OpenAPI / Swagger (Milestone 3)
## Entities
 
| Entity | Description |
|---|---|
| users | Customers and admins |
| categories | Tracksuit categories (Velour, Retro, Lounge, Performance) |
| products | Tracksuit models, each in one category |
| product_variants | Size + colour of a product with its own stock |
| orders | Orders placed by users |
| order_items | Lines of an order (variant, quantity, price at purchase) |
| reviews | Product ratings and comments by users |
 
Relationships: a user has many orders and reviews, a category has many products, a product has many variants and reviews, an order has many order items, and each order item points to one product variant.
 
The full draft ERD is in [docs/ERD.md](docs/ERD.md).
 
## Roles
 
| Role | Can do |
|---|---|
| admin | Full CRUD on all entities, manage order status, admin dashboard |
| customer | Browse products, manage own cart, place orders, view own orders, write reviews, edit own profile |
 
Test accounts (created by the sample data in the `.sql` file):
 
| Role | Email | Password |
|---|---|---|
| admin | admin@tracksuitcouture.ba | Admin123! |
| customer | amar@example.com | Customer123! |
| customer | lejla@example.com | Customer123! |
 
## External API
 
**Frankfurter** (https://frankfurter.dev): free currency exchange rates from the European Central Bank. No API key is needed; the base URL and timeout are in `config.php`.
 
**Feature:** all prices are stored in BAM. The customer picks a currency (BAM, EUR, USD, GBP) in the navbar and sees product, cart and checkout prices converted.
 
**How it works:**
- The ECB does not publish BAM, but BAM is pegged to the euro (1 EUR = 1.95583 BAM). So BAM to EUR always works with the fixed rate, and USD/GBP use the live EUR rates from Frankfurter.
- Backend endpoints: `GET /currency/rates` and `GET /currency/convert?amount=289&to=USD` (see Swagger).
- **Failures:** the call has a 5 second timeout. On a timeout, HTTP error or bad response, the failure is written to `logs/error.log`, `/currency/rates` still returns BAM and EUR with `"live": false` and a friendly message, and converting to USD/GBP returns `503` with a friendly message. The shop keeps working in BAM and EUR.
## Project structure
 
```
tracksuit-couture/
├── backend/
│   ├── config/        # config.example.php (real config.php is not committed)
│   ├── dao/           # Database.php (PDO connection), BaseDao.php + one DAO per entity
│   ├── logs/          # info.log, error.log (not committed)
│   ├── middleware/    # auth, roles, logging (Milestone 4)
│   ├── openapi/       # shared OpenAPI definitions (info, schemas, common responses)
│   ├── routes/        # FlightPHP routes, one file per entity, documented with #[OA\...] attributes
│   ├── services/      # business logic and validation, one service per entity + CurrencyService
│   ├── swagger/       # Swagger UI page
│   ├── utils/         # Config, ApiException, Logger, Http helpers
│   ├── composer.json
│   └── index.php      # API entry point: registers services, error handling and routes
├── frontend/
│   ├── assets/img/
│   ├── css/           # style.css, spapp.css
│   ├── js/            # app.js (starts the SPA), lib/jquery.spapp.js (SPA router)
│   ├── views/         # HTML views, loaded by spapp into the <section>s of index.html
│   └── index.html     # SPA shell
├── database/
│   └── tracksuit_couture.sql   # schema + sample data
├── docs/
│   ├── AI_LOG.md
│   └── ERD.md
├── AGENTS.md
└── README.md
```
 
## Running locally
 
Milestone 1 is a static frontend. The views are loaded with `fetch`, so the project must be served over HTTP (opening `index.html` directly from the file system will not work).
 
**Option A (XAMPP):** put the project in `htdocs` and open `http://localhost/tracksuit-couture/frontend/`.
 
**Option B (PHP built-in server):**
```bash
cd frontend
php -S localhost:8000
```
Then open `http://localhost:8000`.
 
### Requirements
 
- XAMPP (PHP 8.1 or newer, MySQL/MariaDB)
- Composer
### Database setup
 
1. Start **MySQL** in the XAMPP Control Panel.
2. Import `database/tracksuit_couture.sql`:
   - **DBeaver:** connect to `localhost:3306` (user `root`, empty password by default), open a new SQL editor, open the file and run it as a script (**Alt+X**).
   - **phpMyAdmin:** Import tab, choose the file, Go.
   The script creates the `tracksuit_couture` database, all tables and sample data.
3. Create the config file:
```bash
cp backend/config/config.example.php backend/config/config.php
```
   Then fill in your database user and password in `config.php`. This file is in `.gitignore` and is never committed.
 
### Backend (REST API)
 
```bash
cd backend
composer install
php -S localhost:8080 index.php
```
 
- API: `http://localhost:8080/`
- Swagger documentation: `http://localhost:8080/swagger/index.html`
- OpenAPI spec (JSON): `http://localhost:8080/openapi.json`
The backend also runs under Apache (XAMPP `htdocs`): `backend/.htaccess` sends all requests to `index.php`.
 
### API overview
 
All responses are JSON. Errors look like `{"error": "Validation failed", "details": {"price": "Price must be a number of 0 or more."}}`.
 
| Resource | Endpoints |
|---|---|
| Users | `GET/POST /users`, `GET/PUT/DELETE /users/{id}`, `GET /users/{id}/orders` |
| Categories | `GET/POST /categories`, `GET/PUT/DELETE /categories/{id}` |
| Products | `GET/POST /products`, `GET/PUT/DELETE /products/{id}`, `GET /products/{id}/variants`, `GET /products/{id}/reviews` |
| Variants | `GET/POST /variants`, `GET/PUT/DELETE /variants/{id}` |
| Orders | `GET/POST /orders`, `GET/PUT/DELETE /orders/{id}` |
| Order items | `GET/POST /order-items`, `GET/PUT/DELETE /order-items/{id}` |
| Reviews | `GET/POST /reviews`, `GET/PUT/DELETE /reviews/{id}` |
| Currency (external API) | `GET /currency/rates`, `GET /currency/convert` |
 
Status codes: `200` OK, `201` created, `400` validation or business rule error, `404` not found, `405` wrong method, `500` server error (details only in the error log), `503` external API unavailable. `401` and `403` are added with JWT in Milestone 4.
 
### Business rules (examples)
 
- Placing an order: prices come from the products (never from the client), the total is calculated by the server, stock is checked and lowered, and everything is saved in one transaction.
- Order status can only move forward: processing → shipped → delivered, or to cancelled. Cancelling puts the stock back.
- Order items can only be changed while the order is processing. Every change updates stock and the order total.
- A category with products, a product or variant that was ordered, and a user with orders cannot be deleted (products can be hidden with `is_active = false` instead).
- One review per user per product, rating 1 to 5. Emails and category names are unique. Passwords are hashed with bcrypt.
## Links
 
- Swagger documentation: `http://localhost:8080/swagger/index.html` (local); deployed link in Milestone 5
- Deployed application: _Milestone 5_