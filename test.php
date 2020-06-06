<?php


require_once 'vendor/autoload.php';

$client = new \GuzzleHttp\Client;

$calculator = new \StillAlive\RateMeanCalculator\CurrencyRateMeanCalculator(
    new \StillAlive\RateMeanCalculator\Providers\RBCRateProvider($client),
    new \StillAlive\RateMeanCalculator\Providers\CBRRateProvider($client)
);


$date = new DateTime($argv[1]);

echo "USD {$calculator->calculateUSDRateMean($date)}", PHP_EOL;
echo "EUR {$calculator->calculateEURRateMean($date)}" , PHP_EOL;
