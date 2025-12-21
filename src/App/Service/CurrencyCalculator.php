<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTimeImmutable;

class CurrencyCalculator
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly CacheInterface $cache,

        private readonly string $nbpApiUrl,
        private readonly string $nbpApiHistoryUrl,

        private readonly array $supportedCurrencies,
        private readonly array $majorCurrencies
    ) {
    }

    public function getRates(): array
    {
        
        return $this->cache->get('nbp_rates_data_v2', function (ItemInterface $item) {
            
            try {
                $response = $this->client->request('GET', $this->nbpApiUrl);
                $data = $response->toArray();
            } catch (\Exception $e) {
                $item->expiresAfter(60); 
                throw new \Exception('Error during fetching from NBP: ' . $e->getMessage());
            }

            $nbpDate = $data[0]['effectiveDate'] ?? '';
            $today = date('Y-m-d');
            
            if ($nbpDate === $today) {
                $item->expiresAt(new \DateTime('tomorrow 12:15'));
            } else {
                $item->expiresAfter(600);
            }

            $ratesFromNbp = $data[0]['rates'] ?? [];
            $processedRates = [];

            foreach ($ratesFromNbp as $rate) {
                $code = $rate['code'];
                $mid = (float) $rate['mid'];

                if (!in_array($code, $this->supportedCurrencies)) {
                    continue;
                }

                $calculated = $this->calculateBuySell($code, $mid);

                $processedRates[] = [
                    'currency' => $rate['currency'],
                    'code' => $code,
                    'mid_rate' => $mid,
                    'buy_rate' => $calculated['buy'],
                    'sell_rate' => $calculated['sell'],
                ];
            }

            usort($processedRates, fn($a, $b) => $a['code'] <=> $b['code']);

            return $processedRates;
        });
    }

    public function calculateBuySell(string $code, float $mid): array
    {
        if (in_array($code, $this->majorCurrencies)) {
            return [
                'buy' => round($mid - 0.15, 4),
                'sell' => round($mid + 0.11, 4),
            ];
        }
        return [
            'buy' => null, 
            'sell' => round($mid + 0.20, 4),
        ];
    }

    public function getCurrencyHistory(string $code, ?string $targetDate = null): array
    {
        $code = strtoupper($code);

        $endDate = $targetDate ? new DateTimeImmutable($targetDate) : new DateTimeImmutable();
        $startDate = $endDate->modify('-14 days');

        $endDateStr = $endDate->format('Y-m-d');
        $startDateStr = $startDate->format('Y-m-d');

        $cacheKey = sprintf('history_%s_%s_%s', $code, $startDateStr, $endDateStr);

        return $this->cache->get($cacheKey, function ($item) use ($code, $startDateStr, $endDateStr) {
    
            $item->expiresAfter(3600);

            $url = sprintf(
                $this->nbpApiHistoryUrl,
                $code,
                $startDateStr,
                $endDateStr
            );

            try {
                $response = $this->client->request('GET', $url);
                
                if ($response->getStatusCode() !== 200) {
                    return [];
                }

                $data = $response->toArray();


                $history = [];
                foreach ($data['rates'] as $rate) {
                    $history[] = [
                        'date' => $rate['effectiveDate'],
                        'rate' => $rate['mid']
                    ];
                }

                usort($history, fn($a, $b) => $b['date'] <=> $a['date']);

                return $history;

            } catch (\Exception $e) {
                return [];
            }
        });
    }
}