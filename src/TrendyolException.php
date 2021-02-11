<?php

namespace Trendyol;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class TrendyolException
 * @package Trendyol
 */
class TrendyolException extends \Exception
{

    /**
     * @return mixed
     */
    public function getBody()
    {
        $innerException = $this->getPrevious();
        if (!!$innerException && $innerException instanceof RequestException && $innerException->hasResponse()) {
            return json_decode($innerException->getResponse()->getBody(), true);
        }
        return null;
    }

    /**
     * TrendyolException constructor.
     * @param \Exception $e
     */
    public function __construct(\Exception $e)
    {
        parent::__construct($e->getMessage(), (int) $e->getCode(), $e);
    }

}