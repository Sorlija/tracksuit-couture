<?php

use OpenApi\Attributes as OA;

/*
 * Data schemas used in the OpenAPI documentation.
 * swagger-php allows one #[OA\Schema] per class, so each schema has its own empty class.
 */

#[OA\Schema(schema: 'Error', properties: [
    new OA\Property(property: 'error', type: 'string', example: 'Validation failed'),
    new OA\Property(property: 'details', type: 'object', example: ['price' => 'Price must be a number of 0 or more.']),
])]
class ErrorSchema
{
}

#[OA\Schema(schema: 'Message', properties: [
    new OA\Property(property: 'message', type: 'string', example: 'Deleted successfully'),
])]
class MessageSchema
{
}

#[OA\Schema(schema: 'User', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 2),
    new OA\Property(property: 'first_name', type: 'string', example: 'Amar'),
    new OA\Property(property: 'last_name', type: 'string', example: 'Hadžić'),
    new OA\Property(property: 'email', type: 'string', example: 'amar@example.com'),
    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'customer']),
    new OA\Property(property: 'phone', type: 'string', nullable: true),
    new OA\Property(property: 'address', type: 'string', nullable: true),
    new OA\Property(property: 'city', type: 'string', nullable: true),
    new OA\Property(property: 'preferred_currency', type: 'string', enum: ['BAM', 'EUR', 'USD', 'GBP']),
    new OA\Property(property: 'created_at', type: 'string', example: '2026-10-05 14:20:00'),
])]
class UserSchema
{
}

#[OA\Schema(schema: 'UserInput', description: 'On update (PUT) every field is optional', properties: [
    new OA\Property(property: 'first_name', type: 'string', example: 'Amina'),
    new OA\Property(property: 'last_name', type: 'string', example: 'Hodžić'),
    new OA\Property(property: 'email', type: 'string', example: 'amina@example.com'),
    new OA\Property(property: 'password', type: 'string', description: 'At least 8 characters. Stored hashed.', example: 'Secret123!'),
    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'customer'], example: 'customer'),
    new OA\Property(property: 'phone', type: 'string', example: '+387 61 000 000'),
    new OA\Property(property: 'address', type: 'string', example: 'Zmaja od Bosne 1'),
    new OA\Property(property: 'city', type: 'string', example: 'Sarajevo'),
    new OA\Property(property: 'preferred_currency', type: 'string', enum: ['BAM', 'EUR', 'USD', 'GBP'], example: 'BAM'),
])]
class UserInputSchema
{
}

#[OA\Schema(schema: 'Category', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Velour'),
    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Soft velour sets'),
    new OA\Property(property: 'product_count', type: 'integer', description: 'Only in the list endpoint', example: 2),
])]
class CategorySchema
{
}

#[OA\Schema(schema: 'CategoryInput', properties: [
    new OA\Property(property: 'name', type: 'string', example: 'Kids'),
    new OA\Property(property: 'description', type: 'string', example: 'Tracksuits for kids'),
])]
class CategoryInputSchema
{
}

#[OA\Schema(schema: 'Product', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'category_id', type: 'integer', example: 1),
    new OA\Property(property: 'category_name', type: 'string', description: 'Only in the list endpoint', example: 'Velour'),
    new OA\Property(property: 'name', type: 'string', example: 'Monaco Velour Tracksuit'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'fit', type: 'string', enum: ['men', 'women', 'unisex']),
    new OA\Property(property: 'price', type: 'string', description: 'In BAM', example: '289.00'),
    new OA\Property(property: 'image_url', type: 'string', nullable: true),
    new OA\Property(property: 'is_active', type: 'integer', enum: [0, 1], example: 1),
    new OA\Property(property: 'avg_rating', type: 'string', nullable: true, description: 'Only with ?active=1', example: '4.5'),
    new OA\Property(property: 'review_count', type: 'integer', description: 'Only with ?active=1', example: 2),
    new OA\Property(property: 'created_at', type: 'string'),
])]
class ProductSchema
{
}

#[OA\Schema(schema: 'ProductInput', description: 'On update (PUT) every field is optional', properties: [
    new OA\Property(property: 'category_id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Capri Velour Set'),
    new OA\Property(property: 'description', type: 'string', example: 'Light velour set for summer evenings.'),
    new OA\Property(property: 'fit', type: 'string', enum: ['men', 'women', 'unisex'], example: 'women'),
    new OA\Property(property: 'price', type: 'number', example: 259.9),
    new OA\Property(property: 'image_url', type: 'string', example: 'https://example.com/capri.jpg'),
    new OA\Property(property: 'is_active', type: 'boolean', example: true),
])]
class ProductInputSchema
{
}

#[OA\Schema(schema: 'Variant', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 2),
    new OA\Property(property: 'product_id', type: 'integer', example: 1),
    new OA\Property(property: 'sku', type: 'string', example: 'MON-BLK-M'),
    new OA\Property(property: 'size', type: 'string', enum: ['XS', 'S', 'M', 'L', 'XL', 'XXL']),
    new OA\Property(property: 'colour', type: 'string', example: 'Black'),
    new OA\Property(property: 'stock_quantity', type: 'integer', example: 12),
])]
class VariantSchema
{
}

#[OA\Schema(schema: 'VariantInput', description: 'On update (PUT) every field is optional. If sku is not sent on create, it is generated.', properties: [
    new OA\Property(property: 'product_id', type: 'integer', example: 1),
    new OA\Property(property: 'size', type: 'string', enum: ['XS', 'S', 'M', 'L', 'XL', 'XXL'], example: 'XL'),
    new OA\Property(property: 'colour', type: 'string', example: 'Navy'),
    new OA\Property(property: 'stock_quantity', type: 'integer', example: 10),
    new OA\Property(property: 'sku', type: 'string', example: 'MON-NAV-XL'),
])]
class VariantInputSchema
{
}

#[OA\Schema(schema: 'OrderItem', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 3),
    new OA\Property(property: 'order_id', type: 'integer', example: 3),
    new OA\Property(property: 'variant_id', type: 'integer', example: 2),
    new OA\Property(property: 'quantity', type: 'integer', example: 1),
    new OA\Property(property: 'unit_price', type: 'string', description: 'Price at the moment of purchase, in BAM', example: '289.00'),
    new OA\Property(property: 'product_name', type: 'string', description: 'Only when listed by order', example: 'Monaco Velour Tracksuit'),
    new OA\Property(property: 'size', type: 'string', description: 'Only when listed by order', example: 'M'),
    new OA\Property(property: 'colour', type: 'string', description: 'Only when listed by order', example: 'Black'),
    new OA\Property(property: 'line_total', type: 'string', description: 'Only when listed by order', example: '289.00'),
])]
class OrderItemSchema
{
}

#[OA\Schema(schema: 'Order', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 3),
    new OA\Property(property: 'user_id', type: 'integer', example: 2),
    new OA\Property(property: 'status', type: 'string', enum: ['processing', 'shipped', 'delivered', 'cancelled']),
    new OA\Property(property: 'total_amount', type: 'string', description: 'In BAM, calculated by the server', example: '508.00'),
    new OA\Property(property: 'shipping_address', type: 'string', example: 'Zmaja od Bosne 12'),
    new OA\Property(property: 'shipping_city', type: 'string', example: 'Sarajevo'),
    new OA\Property(property: 'shipping_zip', type: 'string', nullable: true, example: '71000'),
    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash_on_delivery', 'card_on_delivery']),
    new OA\Property(property: 'created_at', type: 'string'),
    new OA\Property(property: 'items', type: 'array', description: 'Only in single-order responses', items: new OA\Items(ref: '#/components/schemas/OrderItem')),
])]
class OrderSchema
{
}

#[OA\Schema(schema: 'OrderCreate', required: ['user_id', 'shipping_address', 'shipping_city', 'items'], properties: [
    new OA\Property(property: 'user_id', type: 'integer', example: 2),
    new OA\Property(property: 'shipping_address', type: 'string', example: 'Zmaja od Bosne 12'),
    new OA\Property(property: 'shipping_city', type: 'string', example: 'Sarajevo'),
    new OA\Property(property: 'shipping_zip', type: 'string', example: '71000'),
    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash_on_delivery', 'card_on_delivery'], example: 'cash_on_delivery'),
    new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [
        new OA\Property(property: 'variant_id', type: 'integer', example: 2),
        new OA\Property(property: 'quantity', type: 'integer', example: 1),
    ])),
])]
class OrderCreateSchema
{
}

#[OA\Schema(schema: 'OrderUpdate', description: 'Status can move processing -> shipped -> delivered, or to cancelled (puts stock back). Shipping fields only while processing.', properties: [
    new OA\Property(property: 'status', type: 'string', enum: ['processing', 'shipped', 'delivered', 'cancelled'], example: 'shipped'),
    new OA\Property(property: 'shipping_address', type: 'string'),
    new OA\Property(property: 'shipping_city', type: 'string'),
    new OA\Property(property: 'shipping_zip', type: 'string'),
    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash_on_delivery', 'card_on_delivery']),
])]
class OrderUpdateSchema
{
}

#[OA\Schema(schema: 'OrderItemInput', required: ['order_id', 'variant_id', 'quantity'], properties: [
    new OA\Property(property: 'order_id', type: 'integer', example: 3),
    new OA\Property(property: 'variant_id', type: 'integer', example: 16),
    new OA\Property(property: 'quantity', type: 'integer', example: 1),
])]
class OrderItemInputSchema
{
}

#[OA\Schema(schema: 'OrderItemUpdate', required: ['quantity'], properties: [
    new OA\Property(property: 'quantity', type: 'integer', example: 2),
])]
class OrderItemUpdateSchema
{
}

#[OA\Schema(schema: 'Review', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'product_id', type: 'integer', example: 1),
    new OA\Property(property: 'user_id', type: 'integer', example: 2),
    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
    new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Best fitting tracksuit I own.'),
    new OA\Property(property: 'created_at', type: 'string'),
])]
class ReviewSchema
{
}

#[OA\Schema(schema: 'ReviewInput', required: ['product_id', 'user_id', 'rating'], properties: [
    new OA\Property(property: 'product_id', type: 'integer', example: 2),
    new OA\Property(property: 'user_id', type: 'integer', example: 3),
    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
    new OA\Property(property: 'comment', type: 'string', example: 'Great retro look.'),
])]
class ReviewInputSchema
{
}

#[OA\Schema(schema: 'ReviewUpdate', properties: [
    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 4),
    new OA\Property(property: 'comment', type: 'string', example: 'Still great after a month.'),
])]
class ReviewUpdateSchema
{
}

#[OA\Schema(schema: 'CurrencyRates', properties: [
    new OA\Property(property: 'base', type: 'string', example: 'BAM'),
    new OA\Property(property: 'date', type: 'string', example: '2026-11-20'),
    new OA\Property(property: 'live', type: 'boolean', description: 'false if the external API could not be reached', example: true),
    new OA\Property(property: 'rates', type: 'object', example: ['BAM' => 1, 'EUR' => 0.511292, 'USD' => 0.55, 'GBP' => 0.43]),
    new OA\Property(property: 'message', type: 'string', description: 'Only when live is false'),
])]
class CurrencyRatesSchema
{
}

#[OA\Schema(schema: 'CurrencyConversion', properties: [
    new OA\Property(property: 'from', type: 'string', example: 'BAM'),
    new OA\Property(property: 'to', type: 'string', example: 'EUR'),
    new OA\Property(property: 'amount', type: 'number', example: 289),
    new OA\Property(property: 'converted', type: 'number', example: 147.76),
    new OA\Property(property: 'rate', type: 'number', example: 0.511292),
    new OA\Property(property: 'date', type: 'string', example: '2026-11-20'),
])]
class CurrencyConversionSchema
{
}
