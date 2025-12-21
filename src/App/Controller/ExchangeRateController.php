<?php

namespace App\Controller;

use App\Service\CurrencyCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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
        
        $rates = $this->calculator->getRates();

        return $this->json([
            'generated_at' => date('Y-m-d H:i:s'),
            'rates' => $rates
        ]);
    }

    #[Route('/api/rates/{code}/history', name: 'api_rates_history', methods: ['GET'])]
    public function getHistory(string $code, Request $request): JsonResponse
    {
        
        $requestedDate = $request->query->get('date');

        $historyData = $this->calculator->getCurrencyHistory($code, $requestedDate);

        return $this->json([
            'currency' => strtoupper($code),
            'target_date' => $requestedDate ?? date('Y-m-d'), 
            'history' => $historyData
        ]);
    }
}