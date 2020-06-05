<?php

namespace StillAlive\RateMeanCalculator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StillAlive\RateMeanCalculator\CurrencyRateMeanCalculator;
use StillAlive\RateMeanCalculator\Exceptions\NoProvidersSpecifiedException;
use StillAlive\RateMeanCalculator\Providers\CBRRateProvider;
use StillAlive\RateMeanCalculator\Providers\RBCRateProvider;
use function StillAlive\RateMeanCalculator\Tests\random_float;

class CurrencyRateMeanCalculatorTest extends TestCase
{
    public function testCreateCalculatorWithoutProvidersWillThrowException(): void
    {
        // expects
        $this->expectException(NoProvidersSpecifiedException::class);

        // act
        new CurrencyRateMeanCalculator();
    }

    public function testCorrectness(): void
    {
        // arrange
        $firstUSDRate = random_float();
        $secondUSDRate = random_float();

        $firstEURRate = random_float();
        $secondEURRate = random_float();

        $firstProviderMock = $this->createMock(RBCRateProvider::class);
        $firstProviderMock->method('getUSDRate')->willReturn($firstUSDRate);
        $firstProviderMock->method('getEURRate')->willReturn($firstEURRate);

        $secondProviderMock = $this->createMock(CBRRateProvider::class);
        $secondProviderMock->method('getUSDRate')->willReturn($secondUSDRate);
        $secondProviderMock->method('getEURRate')->willReturn($secondEURRate);

        $expectedUSDRateMean = round(($firstUSDRate + $secondUSDRate)/2, 4);
        $expectedEURRateMean = round(($firstEURRate + $secondEURRate)/2, 4);

        $calculator = new CurrencyRateMeanCalculator(
            $firstProviderMock,
            $secondProviderMock
        );

        // act
        // assert
        $this->assertEquals($expectedUSDRateMean, $calculator->calculateUSDRateMean(new \DateTime));
        $this->assertEquals($expectedEURRateMean, $calculator->calculateEURRateMean(new \DateTime));
    }
}
