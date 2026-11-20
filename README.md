# Tracksuit Couture

Tracksuit Couture is an online shop for premium tracksuits. Customers can browse tracksuits by category, pick a size and colour, see prices in their own currency, place orders and leave reviews. Admins manage products, variants, categories, orders, users and reviews from an admin dashboard.

Built as a single-page application for **IT 2001 Web Programming** (International Burch University).

> Status: **Milestone 2** (database + DAO layer). This README is updated in every milestone.

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

Test accounts (created by the sample data in the `.sql` file):

| Role | Email | Password |
|---|---|---|
| admin | admin@tracksuitcouture.ba | Admin123! |
| customer | amar@example.com | Customer123! |
| customer | lejla@example.com | Customer123! |

## External API

**Frankfurter** (https://frankfurter.dev), free currency exchange rates, no API key needed.

Feature: prices are stored in BAM, and the customer can switch the currency (EUR, USD, GBP) in the navbar. Product, cart and checkout prices are shown converted with live rates. If the API is unavailable, prices are shown in BAM only and the shop keeps working.

## Project structure

```
tracksuit-couture/
├── backend/
│   ├── config/        # config.example.php (real config.php is not committed)
│   ├── dao/           # Database.php (PDO connection), BaseDao.php + one DAO per entity
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

## Links

- Swagger documentation: _Milestone 3_
- Deployed application: _Milestone 5_
