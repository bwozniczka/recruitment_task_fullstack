<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CurrencyCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyCalculatorTest extends TestCase
{
    private CurrencyCalculator $calculator;

    /**
     * Sets up the CurrencyCalculator instance with mocks and configuration before each test.
     */
    protected function setUp(): void
    {
        /** @var HttpClientInterface&\PHPUnit\Framework\MockObject\MockObject $client */
        $client = $this->createMock(HttpClientInterface::class);

        /** @var CacheInterface&\PHPUnit\Framework\MockObject\MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);

        $this->calculator = new CurrencyCalculator(
            $client,
            $cache,
            '', 
            '',
            [], 
            ['USD', 'EUR']
        );
    }

    /**
     * Tests business logic for major currencies (USD, EUR): verifies specific spread and rounding precision.
     */
    public function testCalculateRatesForMajorCurrencies(): void
    {

        $usdResult = $this->calculator->calculateBuySell('USD', 4.00);
        
        $this->assertArrayHasKey('buy', $usdResult);
        $this->assertArrayHasKey('sell', $usdResult);
        $this->assertEquals(3.85, $usdResult['buy'], 'Incorrect buy rate for USD');
        $this->assertEquals(4.11, $usdResult['sell'], 'Incorrect sell rate for USD');

        $eurResult = $this->calculator->calculateBuySell('EUR', 4.50);
        
        $this->assertEquals(4.35, $eurResult['buy'], 'Incorrect buy rate for EUR (4.50 - 0.15)');
        $this->assertEquals(4.61, $eurResult['sell'], 'Incorrect sell rate for EUR (4.50 + 0.11)');

        $precisionResult = $this->calculator->calculateBuySell('USD', 4.123456);
        $this->assertEquals(3.9735, $precisionResult['buy'], 'Rounding logic check failed');
    }


    /**
     * Tests business logic for minor currencies: verifies they are not bought (null) and checks the sell spread.
     */
    public function testCalculateRatesForMinorCurrencies(): void
    {
        $czkResult = $this->calculator->calculateBuySell('CZK', 1.00);

        $this->assertArrayHasKey('buy', $czkResult);
        $this->assertNull($czkResult['buy'], 'The exchange office should not buy minor currencies');
        $this->assertEquals(1.20, $czkResult['sell'], 'Incorrect sell rate for CZK (1.00 + 0.20)');

        $brlResult = $this->calculator->calculateBuySell('BRL', 5.00);

        $this->assertNull($brlResult['buy']);
        $this->assertEquals(5.20, $brlResult['sell']);
    }
}