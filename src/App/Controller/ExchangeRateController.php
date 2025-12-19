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
        
        $rates = $this->calculator->getRates();

        return new JsonResponse([
            'generated_at' => date('Y-m-d H:i:s'),
            'rates' => $rates
        ]);
    }
}