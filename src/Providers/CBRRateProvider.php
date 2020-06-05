<?php

namespace StillAlive\RateMeanCalculator\Providers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use StillAlive\RateMeanCalculator\Exceptions\BadProviderResponseFormatException;
use StillAlive\RateMeanCalculator\Exceptions\ServiceUnavailableException;

class CBRRateProvider implements RateProviderInterface
{
    private const URI = 'http://www.cbr.ru/scripts/XML_daily.asp';
    private const XML_ROOT_NODE = 'ValCurs';

    public const USD_ID = 'R01235';
    public const EUR_ID = 'R01239';

    /** @var ClientInterface */
    private $httpClient;

    /** @var array */
    private $cachedResponses = [];

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws ServiceUnavailableException
     * @throws BadProviderResponseFormatException
     */
    public function getUSDRate(\DateTime $date): float
    {
        return $this->getRate($date, self::USD_ID);
    }

    /**
     * @throws BadProviderResponseFormatException
     * @throws ServiceUnavailableException
     */
    public function getEURRate(\DateTime $date): float
    {
        return $this->getRate($date, self::EUR_ID);
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
                    'query' => ['date_req' => self::formatDateTime($date)]
                ]
            )->getBody();
        } catch (GuzzleException $exception) {
            throw new ServiceUnavailableException;
        }

        // avoid warnings
        $xml = @simplexml_load_string($response);
        self::ensureXMLIsValid($xml);

        $rate = null;
        foreach ($xml->children() as $valuteNode) {
            if ((string)$valuteNode->attributes() === $currencyCode) {
                $rate = (float) str_replace(',', '.', $valuteNode->Value);
                break;
            }
        }

        if ($rate === null) {
            throw new BadProviderResponseFormatException;
        }

        $this->cachedResponses[$currencyCode][self::formatDateTime($date)] = $rate;

        return $rate;
    }

    private static function formatDateTime(\DateTime $dateTime): string
    {
        return $dateTime->format('d/m/Y');
    }

    /**
     * @throws BadProviderResponseFormatException
     */
    private static function ensureXMLIsValid($xml): void
    {
        if ($xml instanceof \SimpleXMLElement && $xml->getName() === self::XML_ROOT_NODE) {
            return;
        }

        throw new BadProviderResponseFormatException;
    }
}
