<?php

use OpenApi\Attributes as OA;

class CurrencyRoutes
{
    #[OA\Get(path: '/currency/rates', tags: ['Currency'], summary: 'Exchange rates for 1 BAM (external API: Frankfurter)',
        description: 'BAM to EUR uses the fixed peg (1 EUR = 1.95583 BAM). USD and GBP come live from Frankfurter. If Frankfurter is unavailable, the response still returns BAM and EUR with live = false, and the failure is written to the error log.',
        responses: [
            new OA\Response(response: 200, description: 'Rates', content: new OA\JsonContent(ref: '#/components/schemas/CurrencyRates')),
        ])]
    public static function rates()
    {
        Flight::json(Flight::currencyService()->getRates());
    }

    #[OA\Get(path: '/currency/convert', tags: ['Currency'], summary: 'Convert a price in BAM to another currency',
        parameters: [
            new OA\Parameter(name: 'amount', in: 'query', required: true, description: 'Amount in BAM',
                schema: new OA\Schema(type: 'number', example: 289)),
            new OA\Parameter(name: 'to', in: 'query', required: true, description: 'Target currency',
                schema: new OA\Schema(type: 'string', enum: ['BAM', 'EUR', 'USD', 'GBP'], example: 'EUR')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Converted amount', content: new OA\JsonContent(ref: '#/components/schemas/CurrencyConversion')),
            new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
            new OA\Response(response: 503, description: 'The external API is unavailable for this currency',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ])]
    public static function convert()
    {
        Flight::json(Flight::currencyService()->convert(Http::query('amount'), Http::query('to')));
    }
}

Flight::route('GET /currency/rates', [CurrencyRoutes::class, 'rates']);
Flight::route('GET /currency/convert', [CurrencyRoutes::class, 'convert']);
