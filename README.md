# Tracksuit Couture

Tracksuit Couture is an online shop for premium tracksuits. Customers can browse tracksuits by category, pick a size and colour, see prices in their own currency, place orders and leave reviews. Admins manage products, variants, categories, orders, users and reviews from an admin dashboard.

Built as a single-page application for **IT 2001 Web Programming** (International Burch University).

> Status: **Milestone 1** (project setup, static SPA frontend, draft ERD). This README is a draft and is updated in every milestone.

## Tech stack

- **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap 5, Bootstrap Icons
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

Test accounts: _added in Milestone 4._

## External API

**Frankfurter** (https://frankfurter.dev), free currency exchange rates, no API key needed.

Feature: prices are stored in BAM, and the customer can switch the currency (EUR, USD, GBP) in the navbar. Product, cart and checkout prices are shown converted with live rates. If the API is unavailable, prices are shown in BAM only and the shop keeps working.

## Project structure

```
tracksuit-couture/
├── backend/
│   ├── config/        # config.example.php (real config.php is not committed)
│   ├── dao/           # data access objects (Milestone 2)
│   ├── logs/          # info.log, error.log (not committed)
│   ├── middleware/    # auth, roles, logging, error handling (Milestone 4)
│   ├── routes/        # FlightPHP routes (Milestone 3)
│   └── services/      # business logic (Milestone 3)
├── frontend/
│   ├── assets/img/
│   ├── css/
│   ├── js/            # router.js (SPA routing), app.js
│   ├── views/         # HTML views loaded by the router
│   └── index.html     # SPA shell
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

Database import and config setup: _added in Milestone 2._

## Links

- Swagger documentation: _Milestone 3_
- Deployed application: _Milestone 5_
