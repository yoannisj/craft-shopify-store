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
use craft\helpers\Json as JsonHelper;
use craft\helpers\ArrayHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\helpers\ApiHelper;

/**
 * Class to build database queries for shopifyProduct elements
 */

class Shop extends Model
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var Int Unique identifier in Craft project (database's Primary Key)
     */

    public $id;

    /**
     * @var String Globally unique identifier for shop (as returne dby Shopify's GraphQl Admin Api)
     */

    public $adminId;

    /**
     * @var Array
     */

    protected $shopifyData;

    /**
     * @var String The shop's primary domain
     */

    public $name;

    /**
     * @var String The shop's description (used in HTML meta tags)
     */

    // public $description;

    /**
     * @var Array The configuration of the shop's primary domain
     */

    protected $primaryDomain;

    /**
     * @var String The shop's shopify subdomain
     */

    public $myshopifyDomain;

    /**
     * @var String The shop owner's email (for communication with Shopify)
     */

    // public $email;

    /**
     * @var String The shop owner's contact email (for communication with Customers)
     */

    // public $contactEmail;

    /**
     * @var Array The Shopify billing plan for shop
     */

    protected $plan;

    /**
     * @var Bool Whether the shop has outstanding setup steps
     */

    // public $setupRequired;

    /**
     * @var Array
     */

    // protected $features;

    /**
     * @var String
     */

    // public $ianaTimezone;

    /**
     * @var String The measuring unit system used by the shop
     */

    // public $unitSystem;

    /**
     * @var String The unit used to configure product weights in the shop
     */

    // public $weightUnit;

    /**
     * @var String The shop's primary currenty code
     */

    public $currencyCode;

    /**
     * @var Array List of currencies supported by the shop for presentment
     */

    // public $enabledPresentmentCurrencies;

    /**
     * @var Array The shop's currency formatting settings
     */

    // public $currencyFormats;

    /**
     * @var Array The shop's currency settings
     */

    // protected $currencySettings;

    /**
     * @var Bool Whether taxes are included in shop's product prices
     */

    // public $taxesIncluded;

    /**
     * @var Bool Whether the shop supports the checkout API
     */

    // public $checkoutApiSupported;

    /**
     * @var Array Countries configuration in the shop's shipping zones
     */

    // protected $countriesInShippingZones;

    /**
     * @var Array List of countries the shop's orders can be shipped to
     */

    // protected $shipsToCountries;

    /**
     * @var Bool Whether taxes apply to the shop's shipping costs
     */

    // public $taxShipping;

    /**
     * @var Array The shop's payment configuration
     */

    // protected $paymentSettings;

    /**
     * @var Array The shop's billing address information
     */

    // protected $billingAddress;

    /**
     * @var String String prepended at the beginning of order numbers
     */

    // public $orderNumberFormatPrefix = '#';

    /**
     * @var String String appended at the end of order numbers
     */
    
    // public $orderNumberFormatSuffix;

    /**
     * @var Array List of ids of sites supported by this shop
     */

    protected $supportedSites;

    /**
     * @var DateTime
     */

    public $dateCreated;

    /**
     * @var String
     */

    public $dateUpdated;

    /**
     * @var String
     */

    public $dateDeleted;

    /**
     * @var String
     */

    public $uid;

    // =Public Methods
    // =========================================================================

    public function __toString()
    {
        return $this->name ?? $this->primaryDomain ?? $this->myshopifyDomain ?? $this->amdinId;
    }

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function __get( $prop )
    {
        // if this is a valid property, that does not have its own getter
        if (!method_exists($this, 'get'.ucfirst($prop)))
        {
            // inspect shopifyData for unset properties
            if (isset($this->shopifyData)) {
                $value = ArrayHelper::getValue($this->shopifyData, $prop);
            }

            return $value;
        }

        return parent::__get($prop);
    }

    /**
     * @inheritdoc
     */

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes = array_unique(array_merge($attributes, [
            'shopifyData',
            'primaryDomain',
            'plan',
        ]));

        return $attributes;
    }

    /**
     * Setter method for the `shopifyData` property
     *
     * @param string | array $data
     *
     * @return yoannisj\shopify\models\Shop
     */

    public function setShopifyData( $data )
    {
        if (is_string($data)) {
            $data = JsonHelper::decodeIfJson($data);
        }

        $this->shopifyData = $data;

        return $this;
    }


    /**
     * Getter method for the `primaryDomain` property
     *
     * @return Array
     */

    public function getShopifyData()
    {
        return $this->shopifyData;
    }

    /**
     * Setter method for the `primaryDomain` property
     *
     * @param Array | String $domain
     * @return yoannisj\shopify\models\Shop
     */

    public function setPrimaryDomain( $domain )
    {
        if (is_string($domain)) {
            $domain = JsonHelper::decodeIfJson($domain);
        }

        if (is_array($domain)) {
            $domain = ArrayHelper::getValue($domain, 'host');
        }

        $this->primaryDomain = $domain;

        return $this;
    }

    /**
     * Getter method for the `primaryDomain` property
     *
     * @return String
     */

    public function getPrimaryDomain()
    {
        return $this->primaryDomain;
    }

    /**
     * Setter method for the `plan` property
     *
     * @param Array | String $plan
     * @return yoannisj\shopify\models\Shop
     */

    public function setPlan( $plan )
    {
        if (is_string($plan)) {
            $plan = JsonHelper::decodeIfJson($plan);
        }

        if (is_array($plan)) {
            $plan = ArrayHelper::getValue($plan, 'displayName');
        }

        $this->plan = $plan;

        return $this;
    }

    /**
     * Getter method for the `plan` property
     *
     * @return Array | null
     */

    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Setter method for the `features` property
     *
     * @param Array | String $features
     * @return yoannisj\shopify\models\Shop
     */

    // public function setFeatures( $features )
    // {
    //     if (is_string($features)) {
    //         $features = JsonHelper::decodeIfJson($features);
    //     }

    //     $this->features = $features;

    //     return $this;
    // }

    /**
     * Getter method for the `features` property
     *
     * @return Array | null
     */

    // public function getFeatures()
    // {
    //     return $this->features;
    // }

    /**
     * Setter method for the `currencySettings` property
     *
     * @param Array | String $features
     * @return yoannisj\shopify\models\Shop
     */

    // public function setCurrencySettings( $settings )
    // {
    //     if (is_string($settings)) {
    //         $settings = JsonHelper::decodeIfJson($settings);
    //     }

    //     if (is_array($settings) && array_key_exist($settings, 'edges')
    //         || is_object($settings) && property_exists($settings, 'edges')
    //     ) {
    //         $settings = ApiHelper::getConnectionNodes($settings);
    //     }

    //     $this->currencySettings = $settings;

    //     return $this;
    // }

    /**
     * Getter method for the `currencySettings` property
     *
     * @return Array | null
     */

    // public function getCurrencySettings()
    // {
    //     return $this->currencySettings;
    // }

    /**
     * Setter method for the `countriesInShippingZones` property
     *
     * @param Array | String $countries
     * @return yoannisj\shopify\models\Shop
     */

    // public function setCountriesInShippingZones( $countries )
    // {
    //     if (is_string($countries)) {
    //         $countries = JsonHelper::decodeIfJson($countries);
    //     }

    //     $this->countriesInShippingZones = $countries;

    //     return $this;
    // }

    /**
     * Getter method for the `countriesInShippingZones` property
     *
     * @return Array | null
     */

    // public function getCountriesInShippingZones()
    // {
    //     return $this->countriesInShippingZones;
    // }

    /**
     * Setter method for the `shipsToCountries` property
     *
     * @param Array | String $countries
     * @return yoannisj\shopify\models\Shop
     */

    // public function setShipsToCountries( $countries )
    // {
    //     if (is_string($countries)) {
    //         $countries = JsonHelper::decodeIfJson($countries);
    //     }

    //     $this->shipsToCountries = $countries;

    //     return $this;
    // }

    /**
     * Getter method for the `shipsToCountries` property
     *
     * @return Array | null
     */

    // public function getShipsToCountries()
    // {
    //     return $this->shipsToCountries;
    // }

    /**
     * Getter method for the 'supportedShippingCountries' property
     *
     * @return Array
     */

    // public function getSupportedShippingCountries(): array
    // {
    //     return $this->countriesInShippingZones['countryCodes'] ?? [];
    // }

    /**
     * Getter method for the 'shippingIncludesRestOfTheWorld' property
     *
     * @return Bool
     */

    // public function getShippingIncludesRestOfTheWorld(): bool
    // {
    //     return $this->countriesInShippingZones['includeRestOfTheWorld'] ?? false;
    // }

    /**
     * Setter method for the `paymentSettings` property
     *
     * @param Array | String $settings
     * @return yoannisj\shopify\models\Shop
     */

    // public function setPaymentSettings( $settings )
    // {
    //     if (is_string($settings)) {
    //         $settings = JsonHelper::decodeIfJson($settings);
    //     }

    //     $this->paymentSettings = $settings;

    //     return $this;
    // }

    /**
     * Getter method for the `paymentSettings` property
     *
     * @return Array | null
     */

    // public function getPaymentSettings()
    // {
    //     return $this->paymentSettings;
    // }

    /**
     * Getter method for the 'supportedDigitalWallets' property
     *
     * @return Array
     */

    // public function getSupportedDigitalWallets(): array
    // {
    //     return $this->paymentSettings['supportedDigitalWallets'] ?? [];
    // }

    /**
     * Setter method for the 'billingAddress' property
     *
     * @param Array | String $address
     * @return yoannisj\shopify\models\Shop
     */

    // public function setBillingAddress( $address )
    // {
    //     if (is_string($address)) {
    //         $address = JsonHelper::decodeIfJson($address);
    //     }

    //     $this->billingAddress = $address;

    //     return $this;
    // }

    /**
     * Getter method for the `billingAddress` property
     *
     * @return Array | null
     */

    // public function getBillingAddress()
    // {
    //     return $this->billingAddress;
    // }

    // =Validation
    // -------------------------------------------------------------------------


    // =Fields
    // -------------------------------------------------------------------------


    // =Content
    // -------------------------------------------------------------------------

    /**
     * Returns the list of site ids, for all sites supported by this shop
     *
     * @return array
     */

    public function getSupportedSites(): array
    {
        if (!isset($this->supportedSites))
        {
            $shopifySettings = Shopify::$plugin->getSettings();
            $allSites = Craft::$app->getSites()->getAllSites();

            $supportedSites = [];

            foreach ($allSites as $site)
            {
                $domain = $shopifySettings->getStoreDomain($site->handle);

                if ($domain == $this->primaryDomain
                    || $domain == $this->myshopifyDomain
                ) {
                    $supportedSites[] = $site->id;
                }
            }

            $this->supportedSites = $supportedSites;
        }

        return $this->supportedSites;
    }

    // =Protected Methods
    // =========================================================================

}