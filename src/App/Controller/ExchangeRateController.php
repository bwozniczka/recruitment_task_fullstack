<?php

namespace App\Controller;

use App\Service\CurrencyCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExchangeRateController extends AbstractController
{
    private CurrencyCalculator $calculator;

    public function __construct(CurrencyCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    #[Route('/api/rates', name: 'api_rates', methods: ['GET'])]
    public function index(): JsonResponse
    {
        
        $mockFromNbp = [
            ['code' => 'EUR', 'mid' => 4.35],
            ['code' => 'USD', 'mid' => 3.98],
            ['code' => 'CZK', 'mid' => 0.17],
            ['code' => 'IDR', 'mid' => 0.00026],
            ['code' => 'BRL', 'mid' => 0.81],
        ];

        $data = [];

        foreach ($mockFromNbp as $currency) {
            $rates = $this->calculator->calculateRates($currency['code'], $currency['mid']);
            
            $data[] = [
                'currency' => $currency['code'],
                'mid_rate' => $currency['mid'],
                'buy_rate' => $rates['buy'],
                'sell_rate' => $rates['sell'],
            ];
        }

        return new JsonResponse([
            'generated_at' => date('Y-m-d H:i:s'),
            'rates' => $data
        ]);
    }
}