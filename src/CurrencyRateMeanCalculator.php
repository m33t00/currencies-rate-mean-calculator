<?php

namespace StillAlive\RateMeanCalculator;

use StillAlive\RateMeanCalculator\Exceptions\NoProvidersSpecifiedException;
use StillAlive\RateMeanCalculator\Providers\RateProviderInterface;

class CurrencyRateMeanCalculator
{
    private $rateProviders;

    /**
     * @throws NoProvidersSpecifiedException
     */
    public function __construct(RateProviderInterface... $rateProviders)
    {
        if (count($rateProviders) === 0) {
            throw new NoProvidersSpecifiedException;
        }
        $this->rateProviders = $rateProviders;
    }

    public function calculateEURRateMean(\DateTime $dateTime): float
    {
        $sum = 0.0;

        foreach ($this->rateProviders as $provider) {
            $sum += $provider->getEURRate($dateTime);
        }

        return round($sum/count($this->rateProviders), 4);
    }

    public function calculateUSDRateMean(\DateTime $dateTime): float
    {
        $sum = 0.0;

        foreach ($this->rateProviders as $provider) {
            $sum += $provider->getUSDRate($dateTime);
        }

        return round($sum/count($this->rateProviders), 4);
    }
}
