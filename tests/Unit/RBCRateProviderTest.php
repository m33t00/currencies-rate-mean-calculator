<?php

namespace StillAlive\RateMeanCalculator\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use StillAlive\RateMeanCalculator\Exceptions\BadProviderResponseFormatException;
use StillAlive\RateMeanCalculator\Exceptions\ServiceUnavailableException;
use StillAlive\RateMeanCalculator\Providers\RateProviderInterface;
use StillAlive\RateMeanCalculator\Providers\RBCRateProvider;
use function StillAlive\RateMeanCalculator\Tests\get_fixture_content;
use function StillAlive\RateMeanCalculator\Tests\random_float;

class RBCRateProviderTest extends TestCase
{
    public function testExceptionOnHTTPRequestError(): void
    {
        // arrange
        $httpClient = $this->createMock(Client::class);
        $httpClient->method('request')->willThrowException(
            new class extends \Exception implements GuzzleException {}
        );

        $provider = new RBCRateProvider($httpClient);

        // expects
        $this->expectException(ServiceUnavailableException::class);

        // act
        $provider->getEURRate(new \DateTime);
    }

    public function providerInvalidResponsePayloads(): \Generator
    {
        yield [get_fixture_content('providers/rbc_response_invalid_content.json')];
        yield [get_fixture_content('providers/rbc_response_invalid_json.json')];
    }

    /**
     * @dataProvider providerInvalidResponsePayloads
     */
    public function testExceptionOnInvalidJSONResponse(string $responsePayload): void
    {
        // arrange
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('getBody')->willReturn($responsePayload);
        $httpClient = $this->createMock(Client::class);
        $httpClient->method('request')->willReturn($responseMock);

        $provider = new RBCRateProvider($httpClient);

        // expects
        $this->expectException(BadProviderResponseFormatException::class);

        // act
        $provider->getEURRate(new \DateTime);
    }

    public function providerCurrencies(): \Generator
    {
        $expectedRate = random_float();
        yield [
            $expectedRate,
            \GuzzleHttp\json_encode(['status' => 200, 'data' => ['rate1' => $expectedRate]]),
            function (RateProviderInterface $provider): float
            {
                return $provider->getUSDRate(new \DateTime);
            }
        ];

        $expectedRate = random_float();
        yield [
            $expectedRate,
            \GuzzleHttp\json_encode(['status' => 200, 'data' => ['rate1' => $expectedRate]]),
            function (RateProviderInterface $provider): float
            {
                return $provider->getEURRate(new \DateTime);
            }
        ];
    }

    /**
     * @dataProvider providerCurrencies
     */
    public function testCorrectness(float $expectedRate, string $responsePayload, \Closure $actualRate): void
    {
        // arrange
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('getBody')->willReturn($responsePayload);
        $httpClient = $this->createMock(Client::class);
        $httpClient->method('request')->willReturn($responseMock);

        // act
        $provider = new RBCRateProvider($httpClient);

        // assert
        $this->assertEquals($expectedRate, $actualRate($provider));
    }
}
