<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyCalculator
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly CacheInterface $cache,

        private readonly string $nbpApiUrl,
        private readonly array $supportedCurrencies
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

                if (in_array($code, ['USD', 'EUR'])) {
                    $buyRate = $mid - 0.15;
                    $sellRate = $mid + 0.11;
                } else {
                    $buyRate = null; 
                    $sellRate = $mid + 0.20;
                }

                $processedRates[] = [
                    'currency' => $rate['currency'],
                    'code' => $code,
                    'mid_rate' => $mid,
                    'buy_rate' => $buyRate ? round($buyRate, 4) : null,
                    'sell_rate' => round($sellRate, 4),
                ];
            }

            usort($processedRates, fn($a, $b) => $a['code'] <=> $b['code']);

            return $processedRates;
        });
    }
}