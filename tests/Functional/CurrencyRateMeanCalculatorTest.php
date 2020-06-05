<?php

namespace StillAlive\RateMeanCalculator\Tests\Functional;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use StillAlive\RateMeanCalculator\CurrencyRateMeanCalculator;
use StillAlive\RateMeanCalculator\Providers\CBRRateProvider;
use StillAlive\RateMeanCalculator\Providers\RBCRateProvider;

class CurrencyRateMeanCalculatorTest extends TestCase
{

    public function testCalculate(): void
    {
        // arrange
        $calculator = new CurrencyRateMeanCalculator(
            new RBCRateProvider(new Client),
            new CBRRateProvider(new Client)
        );

        // act
        $usdRateMean = $calculator->calculateUSDRateMean(new \DateTime);
        $eurRateMean = $calculator->calculateEURRateMean(new \DateTime);

        // assert
        $this->assertGreaterThan(0, $usdRateMean);
        $this->assertGreaterThan(0, $eurRateMean);
    }
}
