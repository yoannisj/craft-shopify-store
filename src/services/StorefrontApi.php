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

namespace yoannisj\shopify\services;

use yii\base\Exception;
use yii\base\InvalidConfigException;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\JsonHelper;

use GuzzleHttp\Client;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\base\ApiService;

/**
 * Service class (singleton) implementing requests to Shopify's Storefront API
 */

class StorefrontApi extends ApiService
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    // =Public Methods
    // =========================================================================

    /**
     * @param $input
     */

    public function createCheckout( array $input, string $site = null )
    {
        $query = 'mutation CheckoutCreateMutation($input: CheckoutCreateInput!) {
            checkoutCreate(input: $input) {
                checkout {
                    storefrontId: id
                    webUrl
                }
            }
        }';

        $results = $this->request($query, [
            'input' => $input,
        ], $site, false);

        return ArrayHelper::getValue($results, 'data.checkoutCreate.checkout');
    }

    // =Protected Methods
    // =========================================================================

    /** 
     * @param string $site
     * @return string
     */

    protected function getClientBaseUri( string $site = null )
    {
        $settings = shopify::$plugin->getSettings();
        $storeDomain = $settings->getStoreDomain($site);
        $apiVersion = $settings->getStorefrontApiVersion($site);

        // trim store domain
        $storeDomain = trim ($storeDomain, '/');

        // set correct primary domain instead of any internal shopify domain
        switch ($storeDomain) {
            case 'aislings-sweets-treats.myshopify.com':
                $storeDomain = 'store.nanit.com';
                break;
            case 'nanituk.myshopify.com':
                $storeDomain = 'storeuk.nanit.com';
                break;
            case 'nanitcanada.myshopify.com':
                $storeDomain = 'storeca.nanit.com';
                break;
            default:
                // do nothing for anything else
        }

        return 'https://' . $storeDomain . '/api/' . $apiVersion . '/';
    }

    /**
     * @param string $site
     * @return array
     */

    protected function getClientHeaders( string $site = null )
    {
        $headers = parent::getClientHeaders($site);
        $accessToken = Shopify::$plugin->getSettings()->getAccessToken($site);

        return array_merge($headers, [
            'X-Shopify-Storefront-Access-Token' => $accessToken
        ]);
    }


}
