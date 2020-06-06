<?php

namespace StillAlive\RateMeanCalculator\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use StillAlive\RateMeanCalculator\Exceptions\BadProviderResponseFormatException;
use StillAlive\RateMeanCalculator\Exceptions\ServiceUnavailableException;
use StillAlive\RateMeanCalculator\Providers\CBRRateProvider;
use StillAlive\RateMeanCalculator\Providers\RateProviderInterface;
use function StillAlive\RateMeanCalculator\Tests\get_fixture_content;
use function StillAlive\RateMeanCalculator\Tests\random_float;

class CBRRateProviderTest extends TestCase
{
    private const XML_SAMPLE_PATTERN = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs Date="01.05.2020" name="Foreign Currency Market">
    <Valute ID="%s">
        <Value>%s</Value>
    </Valute>
</ValCurs>
XML;

    public function testExceptionOnHTTPRequestError(): void
    {
        // arrange
        $httpClient = $this->createMock(Client::class);
        $httpClient->method('request')->willThrowException(
            new class extends \Exception implements GuzzleException {}
        );

        $provider = new CBRRateProvider($httpClient);

        // expects
        $this->expectException(ServiceUnavailableException::class);

        // act
        $provider->getEURRate(new \DateTime);
    }

    public function providerInvalidResponsePayloads(): \Generator
    {
        yield [get_fixture_content('providers/cbr_response_invalid_content.xml')];
        yield [get_fixture_content('providers/cbr_response_invalid_xml.xml')];
    }

    /**
     * @dataProvider providerInvalidResponsePayloads
     */
    public function testExceptionOnInvalidXMLResponse(string $responsePayload): void
    {
        // arrange
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('getBody')->willReturn($responsePayload);
        $httpClient = $this->createMock(Client::class);
        $httpClient->method('request')->willReturn($responseMock);

        $provider = new CBRRateProvider($httpClient);

        // expects
        $this->expectException(BadProviderResponseFormatException::class);

        // act
        $provider->getEURRate(new \DateTime);
    }

    public function providerCurrencies(): \Generator
    {
        $expectedRate = random_float();
        $responseRate = str_replace('.', ',', (string)$expectedRate);
        yield [
            $expectedRate,
            sprintf(self::XML_SAMPLE_PATTERN, CBRRateProvider::USD_ID, $responseRate),
            function (RateProviderInterface $provider): float
            {
                return $provider->getUSDRate(new \DateTime);
            }
        ];

        $expectedRate = random_float();
        $responseRate = str_replace('.', ',', (string)$expectedRate);
        yield [
            $expectedRate,
            sprintf(self::XML_SAMPLE_PATTERN, CBRRateProvider::EUR_ID, $responseRate),
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
        $provider = new CBRRateProvider($httpClient);

        // assert
        $this->assertEquals($expectedRate, $actualRate($provider));
    }

    public function testUseSoftCache(): void
    {
        // arrange
        $date = new \DateTime;
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('getBody')->willReturn(
            sprintf(self::XML_SAMPLE_PATTERN, CBRRateProvider::USD_ID, '99,99')
        );

        $httpClient = $this->createMock(Client::class);
        // expects
        $httpClient->expects($this->once())->method('request')->willReturn($responseMock);

        // act
        $provider = new CBRRateProvider($httpClient);
        $provider->getUSDRate($date);
        $provider->getUSDRate($date);
    }
}
