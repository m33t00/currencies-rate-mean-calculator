<?php

namespace StillAlive\RateMeanCalculator\Providers;

interface RateProviderInterface
{
    public function getUSDRate(\DateTime $date): float;

    public function getEURRate(\DateTime $date): float;
}
