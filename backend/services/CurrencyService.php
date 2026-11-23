<?php

require_once __DIR__ . '/../utils/ApiException.php';
require_once __DIR__ . '/../utils/Config.php';
require_once __DIR__ . '/../utils/Logger.php';

/**
 * External API: Frankfurter (https://frankfurter.dev), free currency rates from the
 * European Central Bank. No API key needed. Base URL and timeout are in config.php.
 *
 * All prices are stored in BAM. The ECB does not publish BAM rates, but BAM is pegged
 * to the euro at a fixed rate (1 EUR = 1.95583 BAM), so:
 *   - BAM -> EUR always works, even when the API is down
 *   - BAM -> USD / GBP uses the live EUR rates from Frankfurter
 * If the API fails, the error is logged and the shop keeps working with BAM and EUR.
 */
class CurrencyService
{
    public const SUPPORTED = ['BAM', 'EUR', 'USD', 'GBP'];
    public const BAM_PER_EUR = 1.95583;

    /**
     * Rates for 1 BAM, e.g. ['BAM' => 1, 'EUR' => 0.5113, 'USD' => 0.55, 'GBP' => 0.43].
     * "live" is false when the external API could not be reached.
     */
    public function getRates()
    {
        $rates = [
            'BAM' => 1.0,
            'EUR' => round(1 / self::BAM_PER_EUR, 6),
        ];
        $result = ['base' => 'BAM', 'date' => date('Y-m-d'), 'live' => true, 'rates' => $rates];

        try {
            $eurRates = $this->fetchEurRates(['USD', 'GBP']);
            foreach ($eurRates['rates'] as $currency => $perEur) {
                // 1 BAM = (1 / 1.95583) EUR, and 1 EUR = $perEur of that currency
                $result['rates'][$currency] = round($perEur / self::BAM_PER_EUR, 6);
            }
            $result['date'] = $eurRates['date'];
        } catch (Exception $e) {
            Logger::error('Currency API failed: ' . $e->getMessage());
            $result['live'] = false;
            $result['message'] = 'Live exchange rates are currently unavailable. Prices are shown in BAM and EUR only.';
        }

        return $result;
    }

    /** Converts an amount in BAM to another supported currency. */
    public function convert($amount, $to)
    {
        $to = strtoupper((string) $to);
        if (!is_numeric($amount) || $amount < 0) {
            throw new ApiException('Validation failed', 400, ['amount' => 'Amount must be a number of 0 or more.']);
        }
        if (!in_array($to, self::SUPPORTED, true)) {
            throw new ApiException('Validation failed', 400, ['to' => 'Currency must be one of: ' . implode(', ', self::SUPPORTED) . '.']);
        }

        $rates = $this->getRates();
        if (!isset($rates['rates'][$to])) {
            // Only happens for USD/GBP when the external API is down
            throw new ApiException("Converting to $to is temporarily unavailable. Please try again later.", 503);
        }

        return [
            'from'      => 'BAM',
            'to'        => $to,
            'amount'    => round((float) $amount, 2),
            'converted' => round($amount * $rates['rates'][$to], 2),
            'rate'      => $rates['rates'][$to],
            'date'      => $rates['date'],
        ];
    }

    /**
     * Calls GET {base_url}/latest?base=EUR&symbols=USD,GBP
     * Throws an Exception on timeout, HTTP error or unexpected response.
     */
    private function fetchEurRates($symbols)
    {
        $baseUrl = rtrim(Config::get('currency_api', 'base_url') ?? 'https://api.frankfurter.dev/v1', '/');
        $timeout = (int) (Config::get('currency_api', 'timeout') ?? 5);
        $url = $baseUrl . '/latest?base=EUR&symbols=' . implode(',', $symbols);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,     // return the body instead of printing it
            CURLOPT_CONNECTTIMEOUT => $timeout, // max seconds to connect
            CURLOPT_TIMEOUT        => $timeout, // max seconds for the whole request
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false) {
            throw new Exception("request to $url failed ($curlError)");
        }
        if ($httpCode !== 200) {
            throw new Exception("request to $url returned HTTP $httpCode");
        }

        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['rates']) || !is_array($data['rates'])) {
            throw new Exception("unexpected response from $url");
        }

        Logger::info("Currency API call OK: $url (date {$data['date']})");
        return $data;
    }
}
