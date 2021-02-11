<?php

namespace Trendyol;

use GuzzleHttp;

/**
 * Trendyol REST API PHP Wrapper
 */
class Trendyol
{
    /**
     * @var string Trendyol integration API key
     */
    protected $_apiKey;

    /**
     * @var string Trendyol integration API secret
     */
    protected $_apiSecret;

    /**
     * @var string Trendyol supplier Id
     */
    protected $_supplierId;

    /**
     * @var array Options array
     */
    protected $_options;

    /**
     * @var TrendyolRestClient REST API client to use Trendyol integration API
     */
    protected $_client;

    /**
     * @var bool Use staging server
     */
    protected $_staging = false;

    /**
     * @var array Auth data to use listing methods by HTTP basic authentication
     */
    protected $_basicAuthInfo;

    /**
     * @var string Api call user agent
     */
    protected $_userAgent;

    /**
     * @var string Endpoint location to use methods
     */
    private $_webServicesUri = [
        'staging' => 'https://stageapi.trendyol.com/stagesapigw',
        'production' => 'https://api.trendyol.com/sapigw'
    ];

    /**
     * The Trendyol integration API key, API secret and supplier Id
     * should be passed to the constructor.
     *
     * Example usage:
     *
     *      $ty = new Trendyol('<API_KEY>', '<API_SECRET>', '<SUPPLIER_ID>');
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $supplierId
     */
    public function __construct($apiKey, $apiSecret, $supplierId, $integration, $staging = false)
    {
        $this->setStaging($staging);
        $this->setApiKey($apiKey);
        $this->setApiSecret($apiSecret);
        $this->setSupplierId($supplierId);
        $this->setUserAgent(\sprintf('%s - %s', $supplierId, $integration));

        $this->_client = new TrendyolRestClient();

        $this->_basicAuthInfo = [
            $this->_apiKey,
            $this->_apiSecret
        ];
    }

    /**
     * @return bool
     */
    public function isStaging()
    {
        return $this->_staging;
    }

    /**
     * @param bool $staging
     */
    public function setStaging(bool $staging)
    {
        $this->_staging = $staging;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent)
    {
        $this->_userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->_apiSecret;
    }

    /**
     * @param string $apiSecret
     */
    public function setApiSecret($apiSecret)
    {
        $this->_apiSecret = $apiSecret;
    }

    /**
     * @return string
     */
    public function getSupplierId()
    {
        return $this->_supplierId;
    }

    /**
     * @param string $supplierId
     */
    public function setSupplierId($supplierId)
    {
        $this->_supplierId = $supplierId;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * @return string
     */
    public function getWebServicesUri()
    {
        return $this->_webServicesUri[$this->isStaging() ? 'staging' : 'production'];
    }


    /**
     * @return array
     * @throws TrendyolException
     */
    public function fetchCategoryTree()
    {
        $uri = \sprintf('%s/product-categories', $this->getWebServicesUri());

        $response = $this->_client->request('GET', $uri, [
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * @return array
     * @throws TrendyolException
     */
    public function fetchAddresses()
    {
        $uri = \sprintf('%s/suppliers/%s/addresses', $this->getWebServicesUri(), $this->_supplierId);

        $response = $this->_client->request('GET', $uri, [
            'auth' => $this->_basicAuthInfo,
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * @return array
     * @throws TrendyolException
     */
    public function fetchBrands($page = null, $size = null)
    {
        $query = [];
        if ($page) $query += [ 'page' => $page ];
        if ($size) $query += [ 'size' => $size ];

        $uri = \sprintf('%s/brands%s', $this->getWebServicesUri(), (!empty($query) ? '?' . http_build_query($query) : ''));

        $response = $this->_client->request('GET', $uri, [
            'auth' => $this->_basicAuthInfo,
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * @param string $brandName
     * @return array|null
     * @throws TrendyolException
     */
    public function fetchBrandByName($brandName)
    {
        $uri = \sprintf('%s/brands/by-name?name=%s', $this->getWebServicesUri(), $brandName);

        $response = $this->_client->request('GET', $uri, [
            'auth' => $this->_basicAuthInfo,
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * @param int|string $categoryId
     * @return array
     * @throws TrendyolException
     */
    public function fetchCategoryAttributes($categoryId)
    {
        $uri = \sprintf('%s/product-categories/%s/attributes', $this->getWebServicesUri(), $categoryId);

        $response = $this->_client->request('GET', $uri, [
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * Request to create products by JSON string.
     *
     * Example usage:
     *
     *      $ty->createProducts('{
     *          "items": [
     *              {
     *                  "barcode": "barkod-1234",
     *                  "title": "Ürün İsmi",
     *                  "productMainId": "1234BT",
     *                  "brandId": 1791,
     *                  "categoryId": 411,
     *                  "quantity": 100,
     *                  "stockCode": "STK-345",
     *                  "dimensionalWeight": 2,
     *                  "description": "Ürün açıklama bilgisi",
     *                  "currencyType": "TRY",
     *                  "listPrice": 250.99,
     *                  "salePrice": 120.99,
     *                  "vatRate": 18,
     *                  "cargoCompanyId": 10,
     *                  "images": [
     *                      {
     *                          "url": "https://www.sampleadress/path/folder/image_1.jpg"
     *                      }
     *                  ],
     *                  "attributes": [
     *                      {
     *                          "attributeId": 338,
     *                          "attributeValueId": 6980
     *                      },
     *                      {
     *                          "attributeId": 47,
     *                           "customAttributeValue": "TEST"
     *                      },
     *                      {
     *                          "attributeId": 346,
     *                          "attributeValueId": 4290
     *                      },
     *                      {
     *                          "attributeId": 343,
     *                          "attributeValueId": 4294
     *                      }
     *                  ]
     *              }
     *          ]
     *      }');
     *
     * @param string $productsJson
     * @return array
     * @throws TrendyolException
     */
    public function createProducts($productsJson)
    {
        $uri = \sprintf('%s/suppliers/%s/v2/products', $this->getWebServicesUri(), $this->_supplierId);

        $response = $this->_client->request('POST', $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => $this->getUserAgent()
            ],
            'body' => $productsJson,
            'auth' => $this->_basicAuthInfo
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * Request to create products by PHP array.
     *
     * Example usage =>
     *
     *      $ty->createProducts('[
     *          "items" => [
     *              [
     *                  "barcode" => "barkod-1234",
     *                  "title" => "Ürün İsmi",
     *                  "productMainId" => "1234BT",
     *                  "brandId" => 1791,
     *                  "categoryId" => 411,
     *                  "quantity" => 100,
     *                  "stockCode" => "STK-345",
     *                  "dimensionalWeight" => 2,
     *                  "description" => "Ürün açıklama bilgisi",
     *                  "currencyType" => "TRY",
     *                  "listPrice" => 250.99,
     *                  "salePrice" => 120.99,
     *                  "vatRate" => 18,
     *                  "cargoCompanyId" => 10,
     *                  "images" => [
     *                      [
     *                          "url" => "https =>//www.sampleadress/path/folder/image_1.jpg"
     *                      ]
     *                  ],
     *                  "attributes" => [
     *                      [
     *                          "attributeId" => 338,
     *                          "attributeValueId" => 6980
     *                      ],
     *                      [
     *                          "attributeId" => 47,
     *                          "customAttributeValue" => "TEST"
     *                      ],
     *                      [
     *                          "attributeId" => 346,
     *                          "attributeValueId" => 4290
     *                      ],
     *                      [
     *                          "attributeId" => 343,
     *                          "attributeValueId" => 4294
     *                      ]
     *                  ]
     *              ]
     *          ]
     *      ]');
     *
     * @param $productsArray
     * @return array
     * @throws TrendyolException
     */
    public function createProductsAsArray($productsArray)
    {
        $productsJson = \json_encode($productsArray);

        return $this->createProducts($productsJson);
    }

    /**
     * @param $trackingId
     * @return array
     * @throws TrendyolException
     */
    public function productCreatingStatus($trackingId)
    {
        $uri = \sprintf('%s/suppliers/%s/products/batch-requests/%s', $this->getWebServicesUri(), $this->_supplierId, $trackingId);

        $response = $this->_client->request('GET', $uri, [
            'auth' => $this->_basicAuthInfo,
            'headers' => [
                'User-Agent' => $this->getUserAgent()
            ]
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * Updates price and inventory data by JSON string.
     *
     * Example usage:
     *
     *      $ty->updatePriceAndInventory({
     *          "items": [
     *              {
     *                  "barcode": "8680000000",
     *                  "quantity": 100,
     *                  "salePrice": 112.85,
     *                  "listPrice": 113.85
     *              }
     *          ]
     *      });
     *
     * @param $dataJson
     * @return array
     * @throws TrendyolException
     */
    public function updatePriceAndInventory($dataJson)
    {
        $uri = \sprintf('%s/suppliers/%s/products/price-and-inventory', $this->getWebServicesUri(), $this->_supplierId);

        $response = $this->_client->request('POST', $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => $this->getUserAgent()
            ],
            'body' => $dataJson,
            'auth' => $this->_basicAuthInfo
        ]);

        return (array) \json_decode($response->getBody(), true);
    }

    /**
     * Updates price and inventory data by PHP array.
     *
     * Example usage =>
     *
     *      $ty->updatePriceAndInventory([
     *          "items" => [
     *              [
     *                  "barcode" => "8680000000",
     *                  "quantity" => 100,
     *                  "salePrice" => 112.85,
     *                  "listPrice" => 113.85
     *              ]
     *          ]
     *      ]);
     *
     * @param $dataArray
     * @return array
     * @throws TrendyolException
     */
    public function updatePriceAndInventoryAsArray($dataArray)
    {
        $dataJson = \json_encode($dataArray);

        return $this->updatePriceAndInventory($dataJson);
    }
}