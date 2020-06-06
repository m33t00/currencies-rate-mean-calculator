<?php

namespace StillAlive\RateMeanCalculator\Providers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use StillAlive\RateMeanCalculator\Exceptions\BadProviderResponseFormatException;
use StillAlive\RateMeanCalculator\Exceptions\ServiceUnavailableException;

class RBCRateProvider implements RateProviderInterface
{
    private const URI = 'https://cash.rbc.ru/cash/json/converter_currency_rate/';

    private const USD_CODE = 'USD';
    private const EUR_CODE = 'EUR';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var array
     */
    private $cachedResponses = [];

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws BadProviderResponseFormatException
     * @throws ServiceUnavailableException
     */
    public function getEURRate(\DateTime $date): float
    {
        return $this->getRate($date, self::EUR_CODE);
    }

    /**
     * @throws BadProviderResponseFormatException
     * @throws ServiceUnavailableException
     */
    public function getUSDRate(\DateTime $date): float
    {
        return $this->getRate($date, self::USD_CODE);
    }

    /**
     * @throws BadProviderResponseFormatException
     * @throws ServiceUnavailableException
     */
    private function getRate(\DateTime $date, string $currencyCode): float
    {
        if ($cached = $this->cachedResponses[$currencyCode][self::formatDateTime($date)] ?? null) {
            return $cached;
        }

        try {
            $response = (string) $this->httpClient->request(
                'GET',
                self::URI,
                [
                    'query' => [
                        'date' => self::formatDateTime($date),
                        'sum' => 1,
                        'source' => 'cbrf',
                        'currency_to' => 'RUR',
                        'currency_from' => $currencyCode
                    ]
                ]
            )->getBody();

            $rateData = \GuzzleHttp\json_decode($response, true);
        } catch (InvalidArgumentException $exception) {
            throw new BadProviderResponseFormatException;
        } catch (GuzzleException $exception) {
            throw new ServiceUnavailableException;
        }

        self::ensureResponseDataIsValid($rateData);

        $rate = (float) $rateData['data']['rate1'];
        $this->cachedResponses[$currencyCode][self::formatDateTime($date)] =  (float) $rateData['data']['rate1'];

        return $rate;
    }

    private static function formatDateTime(\DateTime $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }

    /**
     * @throws BadProviderResponseFormatException
     */
    private static function ensureResponseDataIsValid(array $rateData): void
    {
        $status = $rateData['status'] ?? null;
        if ($status === 200 && !empty($rateData['data']['rate1'])) {
            return;
        }

        throw new BadProviderResponseFormatException;
    }
}
