<?php

namespace App\Service;

class CurrencyCalculator
{
    private const SPECIAL_CURRENCIES = ['EUR', 'USD'];

    private const SPECIAL_SPREAD_BUY = 0.15;
    private const SPECIAL_SPREAD_SELL = 0.11;

    private const STANDARD_SPREAD_SELL = 0.20;

    
    public function calculateRates(string $currencyCode, float $midRate): array
    {
        $code = strtoupper($currencyCode);

        if (in_array($code, self::SPECIAL_CURRENCIES)) {
            return [
                'buy' => round($midRate - self::SPECIAL_SPREAD_BUY, 4),
                'sell' => round($midRate + self::SPECIAL_SPREAD_SELL, 4),
            ];
        }

        return [
            'buy' => null, 
            'sell' => round($midRate + self::STANDARD_SPREAD_SELL, 4),
        ];
    }
}