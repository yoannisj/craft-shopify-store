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

namespace yoannisj\shopify\models;

use Craft;
use craft\base\Model;
use craft\helpers\ConfigHelper;

class ShopifySettings extends Model
{
    // =Static
    // =========================================================================

    /**
     * API version pattern.
     *
     * @var string
     */
    const VERSION_PATTERN = '/([0-9]{4}-[0-9]{2})|unstable/';

    // =Properties
    // =========================================================================

    /** 
     * Domain of shopify store to which Shopify requests are sent
     * @accepts string | array (multi-site)
     */

    public $storeDomain;

    /** 
     * Version of Shopify APIs (private app, Admin API, Webhook API and Storefront API, unless overriden)
     * @accepts string | array (multi-site)
     */

    public $apiVersion;

    /** 
     * Version of Shopify Admin and Storefront API (private app)
     * @accepts string | array (multi-site)
     */

    public $adminApiVersion;

    /** 
     * Version of Shopify webhook API (private app)
     * @accepts string | array (multi-site)
     */

    public $webhookApiVersion;

    /** 
     * Version of Shopify webhook API (private app)
     * @accepts string | array (multi-site)
     */

    public $storefrontApiVersion;

    /** 
     * Key used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    public $apiKey;

    /** 
     * Password used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    public $apiPassword;

    /** 
     * Shared Secret used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    public $apiSecret;

    /** 
     * Secret used to verify Shopify webhooks
     * @accepts string | array (multi-site)
     */

    public $webhookSecret;

    /** 
     * Acces Token Secret used for requests to Shopify's Storefront API (private app)
     * @accepts string | array (multi-site)
     */

    public $accessToken;

    /**
     * Format used for shopify products on Craft sites
     * @accepts string | array (format supported by `Craft::$app->view->renderObjectTemplate`)
     */

    public $productUriFormat = 'products/{handle}';

    /**
     * Path to template file rendered when routing to a Shopify product
     *
     * @accepts string | array
     */

    public $productTemplate = '_shopify-product';

    // =Public Methods
    // =========================================================================

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * Getter method for the 'storeDomain' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getStoreDomain( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->storeDomain, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'apiVersion' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getApiVersion( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->apiVersion, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'adminApiVersion' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getAdminApiVersion( string $siteHandle = null )
    {
        if (!isset($this->adminApiVersion)) {
            return $this->getApiVersion($siteHandle);
        }

        $value = ConfigHelper::localizedValue($this->adminApiVersion, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'webhookApiVersion' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getWebhookApiVersion( string $siteHandle = null )
    {
        if (!isset($this->webhookApiversion)) {
            return $this->getApiVersion($siteHandle);
        }

        $value = ConfigHelper::localizedValue($this->webhookApiVersion, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'storefrontApiVersion' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getStorefrontApiVersion( string $siteHandle = null )
    {
        if (!isset($this->storefrontApiVersion)) {
            return $this->getApiVersion($siteHandle);
        }

        $value = ConfigHelper::localizedValue($this->storefrontApiVersion, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'apiKey' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getApiKey( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->apiKey, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'apiPassword' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getApiPassword( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->apiPassword, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'apiSecret' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getApiSecret( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->apiSecret, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'webhookSecret' setting
     *
     * @param string $siteHandle
     *
     * @return string
     */

    public function getWebhookSecret( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->webhookSecret, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'upsells' setting
     */

    public function getAccessToken( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->accessToken, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'productUriFormat' setting
     */

    public function getProductUriFormat( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->productUriFormat, $siteHandle);
        return Craft::parseEnv($value);
    }

    /**
     * Getter method for the 'productTemplate' setting
     */

    public function getProductTemplate( string $siteHandle = null )
    {
        $value = ConfigHelper::localizedValue($this->productTemplate, $siteHandle);
        return Craft::parseEnv($value);
    }

    // =Validation
    // -------------------------------------------------------------------------

    /**
     * @inheritdocs
     */

    public function rules(): array
    {
        $rules = parent::rules();

        return $rules;
    }


    // =Fields
    // -------------------------------------------------------------------------


}