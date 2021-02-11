<?php

namespace Trendyol;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TrendyolRestClient
 * @package Trendyol
 */
class TrendyolRestClient extends Client
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws TrendyolException
     */
    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        try {
            return parent::request($method, $uri, $options);
        }
        catch (GuzzleException $e) {
            throw new TrendyolException($e);
        }
    }
}