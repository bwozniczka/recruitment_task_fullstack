<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExchangeRateControllerTest extends WebTestCase
{
    /**
     * Verifies that the main rates endpoint returns a 200 OK, a valid JSON structure with calculated fields (buy/sell), and includes USD.
     */
    public function testGetRatesEndpointReturnsSuccessfulResponseAndCorrectStructure(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/rates');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('generated_at', $data);
        $this->assertArrayHasKey('rates', $data);
        $this->assertIsArray($data['rates']);
        $this->assertNotEmpty($data['rates'], 'API returned empty rates list');

        $firstRate = $data['rates'][0];
        $this->assertArrayHasKey('currency', $firstRate);
        $this->assertArrayHasKey('code', $firstRate);
        $this->assertArrayHasKey('mid_rate', $firstRate);
        $this->assertArrayHasKey('buy_rate', $firstRate); 
        $this->assertArrayHasKey('sell_rate', $firstRate); 

        $codes = array_column($data['rates'], 'code');
        $this->assertContains('USD', $codes, 'USD is missing from the response');
    }

    /**
     * Verifies that the history endpoint returns correct metadata and data structure for a valid currency (USD).
     */
    public function testHistoryEndpointReturnsDataWithCorrectStructure(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/rates/USD/history');

        $this->assertResponseIsSuccessful();
        
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('currency', $data);
        $this->assertEquals('USD', $data['currency']);
        $this->assertArrayHasKey('history', $data);
        $this->assertIsArray($data['history']);

        if (count($data['history']) > 0) {
            $firstEntry = $data['history'][0];
            $this->assertArrayHasKey('date', $firstEntry);
            $this->assertArrayHasKey('rate', $firstEntry);
            $this->assertIsNumeric($firstEntry['rate']);
        }
    }

    /**
     * Verifies that requesting history for a non-existent currency returns an empty list gracefully (Fail-Safe), instead of an error.
     */
    public function testHistoryForUnknownCurrency(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/rates/XYZ/history'); 

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEmpty($data['history'] ?? []); 
    }
}