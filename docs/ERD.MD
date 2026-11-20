# Tracksuit Couture: draft ERD (Milestone 1)

GitHub renders the diagram below automatically.

```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    USERS ||--o{ REVIEWS : writes
    CATEGORIES ||--o{ PRODUCTS : contains
    PRODUCTS ||--|{ PRODUCT_VARIANTS : "has sizes/colours"
    PRODUCTS ||--o{ REVIEWS : receives
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCT_VARIANTS ||--o{ ORDER_ITEMS : "is ordered as"

    USERS {
        int id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password_hash
        enum role "admin, customer"
        varchar phone
        varchar address
        varchar city
        char preferred_currency "BAM, EUR, USD, GBP"
        datetime created_at
    }

    CATEGORIES {
        int id PK
        varchar name UK
        varchar description
    }

    PRODUCTS {
        int id PK
        int category_id FK
        varchar name
        text description
        enum fit "men, women, unisex"
        decimal price "in BAM"
        varchar image_url
        boolean is_active
        datetime created_at
    }

    PRODUCT_VARIANTS {
        int id PK
        int product_id FK
        varchar sku UK
        enum size "XS, S, M, L, XL, XXL"
        varchar colour
        int stock_quantity
    }

    ORDERS {
        int id PK
        int user_id FK
        enum status "processing, shipped, delivered, cancelled"
        decimal total_amount "in BAM"
        varchar shipping_address
        varchar shipping_city
        varchar payment_method
        datetime created_at
    }

    ORDER_ITEMS {
        int id PK
        int order_id FK
        int variant_id FK
        int quantity
        decimal unit_price "price at time of purchase"
    }

    REVIEWS {
        int id PK
        int product_id FK
        int user_id FK
        tinyint rating "1 to 5"
        text comment
        datetime created_at
    }
```

## Entities and relationships

| Entity | Purpose | Relationships |
|---|---|---|
| **users** | Customers and admins | 1 user : N orders, 1 user : N reviews |
| **categories** | Velour, Retro, Lounge, Performance | 1 category : N products |
| **products** | A tracksuit model (name, price, description) | N products : 1 category, 1 product : N variants, 1 product : N reviews |
| **product_variants** | A sellable size + colour of a product, with its own stock | N variants : 1 product, 1 variant : N order items |
| **orders** | A placed order | N orders : 1 user, 1 order : N order items |
| **order_items** | One line of an order (variant, quantity, price) | N items : 1 order, N items : 1 variant |
| **reviews** | Rating and comment for a product | N reviews : 1 product, N reviews : 1 user |

## Design notes

- **Why variants?** One tracksuit comes in many sizes and colours, and stock is tracked per size and colour. Orders point to the variant, so we know exactly what was bought.
- **`order_items.unit_price`** stores the price at the moment of purchase, so changing a product price later does not change old orders.
- **Prices are stored in BAM.** Other currencies are calculated live using the external API (Frankfurter) and are never stored.
- **Cart** is kept on the frontend (localStorage) until checkout, so it is not a database table.
- **Roles:** `admin` (full CRUD on all entities) and `customer` (browse, order, review, manage own profile).
