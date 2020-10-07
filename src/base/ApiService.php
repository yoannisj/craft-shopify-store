<?php

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 *
 */

namespace yoannisj\shopify\base;

use GuzzleHttp\Client;

use yii\base\Exception;
use yii\base\InvalidConfigException;

use Craft;
use craft\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\ArrayHelper;
use craft\helpers\Json as JsonHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\base\ApiRateLimitExceededException;
use yoannisj\shopify\helpers\ApiHelper;

/** 
 * Abstract class, implementing base functionality for Api service classes
 */

abstract class ApiService extends Component
{
    // =Static
    // =========================================================================

    const RATE_LIMIT_ERROR_IDENTIFIER_STR = 'exceeds the max cost of';

    // =Properties
    // =========================================================================

    /** 
     * @var array Resolved guzzle client for shopify's API, per site
     */

    protected $clients = [];

    /**
     * The current API call limits from last request.
     *
     * @var array
     */

    protected $apiCallLimits = [
        'left'          => 0,
        'made'          => 0,
        'limit'         => 1000,
        'restoreRate'   => 50,
        'requestedCost' => 0,
        'actualCost'    => 0,
    ];

    /**
     * Request timestamp for every new call.
     * Used for rate limiting.
     *
     * @var int
     */

    protected $requestTimestamp;

    // =Public Methods
    // =========================================================================

    /**
     * Sends given request to Shopify Storefront API, and returns results
     *
     * @param string $query
     * @param array $variabes
     * @param string $site
     * @param bool $useCache
     *
     * @return pbject 
     */

    public function request( string $query, $variables = [], string $site = null, bool $useCache = false )
    {
        // build request body
        $request = [ 'query' => $query ];
        if (!empty($variables)) {
            $request['variables'] = $variables;
        }

        $reqBody = json_encode($request);

        // optionally get result from data cache
        $cacheKey = null;
        if ($useCache)
        {
            $cacheKey = $site.'/'.md5($reqBody);
            $result = Craft::$app->getCache()->get($cacheKey);

            if ($result !== false) {
                return (object)$result;
            }
        }

        // get client for request
        $client = $this->getClient($site);

        // update timestamps, to measure request's time cost
        $tmpTimestamp = $this->requestTimestamp;
        $this->requestTimestamp = microtime(true);

        // run request and get result
        $response = $client->request('POST', 'graphql.json', [
            'body' => $reqBody
        ]);

        // grab the response body
        $body = JsonHelper::decode($response->getBody());
        $costs = ArrayHelper::getValue($body, 'extensions.cost');

        // process cost extensions
        if ($costs)
        {
            // Update the API call information
            $costsMaximum = (int)ArrayHelper::getValue($costs, 'throttleStatus.currentlyAvailable');
            $costsAvailable = (int)ArrayHelper::getValue($costs, 'throttleStatus.currentlyAvailable');
            $costsMade = $costsMaximum - $costsAvailable;

            $costsRestoreRate = (int)ArrayHelper::getValue($costs, 'throttleStatus.restoreRate');
            $requestedCost = (int)ArrayHelper::getValue($costs, 'requestedQueryCost');
            $actualCost = (int)ArrayHelper::getValue($costs, 'actualQueryCost');

            $this->apiCallLimits = [
                'limit' => $costsMaximum,
                'made' => $costsMade,
                'left' => $costsAvailable,
                'restoreRate' => $costsRestoreRate,
                'requestedCost' => $requestedCost,
                'actualCost' => $actualCost,
            ];
        }

        // handle errors returned by the API request
        $errors = ArrayHelper::getValue($body, 'errors');
        if (!empty($errors))
        {
            $message = implode('\\n', ArrayHelper::getColumn($errors, 'message'));

            if (StringHelper::contains($message, self::RATE_LIMIT_ERROR_IDENTIFIER_STR)) {
                throw new ApiRateLimitExceededException($message);
            }

            throw new Exception($message);
        }

        // format result, by including all useful information
        $result = [
            'response' => $response,
            'errors' => $errors,
            'data' => ArrayHelper::getValue($body, 'data'),
            'timestamps' => [ $tmpTimestamp, $this->requestTimestamp ],
        ];

        // optionally store result in data cache
        if ($useCache) {
            Craft::$app->getCache()->set($cacheKey, $result);
        }

        // @todo: better logging of Shopify API errors
        if (!empty($errors)) {
            Craft::error($errors);
        }

        return $result;
    }

    // =Protected Methods
    // =========================================================================

    /**
     * Returns guzzle client to handle requests to API
     *
     * @param string $site
     *
     * @return Client
     */

    protected function getClient( string $site = null )
    {
        if (is_null($site)) {
            $site = Craft::$app->getSites()->getCurrentSite();
        }

        else if (($site = Craft::$app->getSites()->getSiteByHandle($site)) === null)
        {
            throw new InvalidConfigException(
                Craft::t('shopify-store', 'Invalid $site argument {site}',[
                    'site' => $site
                ])
            );
        }

        if (!array_key_exists($site->handle, $this->clients))
        {
            // get shopify Api config settings
            $baseUri = $this->getClientBaseUri($site->handle);
            $headers = $this->getClientHeaders($site->handle);

            // create a default Guzzle client with our stack
            $client = new Client([
                'base_uri' => $baseUri,
                'headers'  => $headers,
            ]);

            $this->clients[$site->handle] = $client;
        }

        return $this->clients[$site->handle];
    }

    /** 
     * @param string $site
     *
     * @return string
     */

    protected function getClientBaseUri( string $site = null )
    {
        throw new Exception('Missing implementation for `getClientBaseUri` method');
    }

    /**
     * @param string $site
     *
     * @return array
     */

    protected function getClientHeaders( string $site = null )
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Decodes the JSON body from request responses.
     *
     * @param string $json The JSON body
     *
     * @return object The decoded JSON
     */

    protected function jsonDecode($json)
    {
        // From firebase/php-jwt
        if (!(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            /**
             * In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
             * to specify that large ints (like Steam Transaction IDs) should be treated as
             * strings, rather than the PHP default behaviour of converting them to floats.
             */
            $obj = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            // @codeCoverageIgnoreStart
            /**
             * Not all servers will support that, however, so for older versions we must
             * manually detect large ints in the JSON string and quote them (thus converting
             * them to strings) before decoding, hence the preg_replace() call.
             * Currently not sure how to test this so I ignored it for now.
             */
            $maxIntLength = strlen((string) PHP_INT_MAX) - 1;
            $jsonWithoutBigints = preg_replace('/:\s*(-?\d{'.$maxIntLength.',})/', ': "$1"', $json);
            $obj = json_decode($jsonWithoutBigints);
            // @codeCoverageIgnoreEnd
        }

        return $obj;
    }
}
