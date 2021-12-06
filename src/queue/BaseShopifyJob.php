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

namespace yoannisj\shopify\queue;

use yii\base\InvalidConfigException;
use yii\queue\RetryableJobInterface;

use Craft;
use craft\queue\BaseJob;
use craft\helpers\StringHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\base\ApiRateLimitExceededException;
use yoannisj\shopify\models\Shop;

/**
 * Abstract class implementing base functionality of Shopify jobs
 */

abstract class BaseShopifyJob extends BaseJob implements RetryableJobInterface
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var Int
     */

    public $siteId;

    /**
     * @var Int
     */

    public $shopId;

    /**
     * @var String
     */

    public $shopDomain;


    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function init()
    {
        if (!isset($this->siteId))
        {
            $currentSite = Craft::$app->getSites()->getCurrentSite();
            $this->siteId = $currentSite->id;
        }

        parent::init();
    }

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * Getter method for the `site` property
     *
     * @return \craft\models\Site | null
     */

    public function getSite()
    {
        if (isset($this->siteId)) {
            return Craft::$app->getSites()->getSiteById($this->siteId);
        }

        return null;
    }

    /**
     * @return string|null
     */

    public function getSiteHandle()
    {
        $site = $this->getSite();
        return $site ? $site->handle : null;
    }

    /**
     * Getter method for the `shop` property
     *
     * @return yoannisj\shopify\models\Shop
     * @throws InvalidConfigException
     */

    public function getShop(): Shop
    {
        $shop = null;

        // $site = $this->getSite();
        // Craft::dd([
        //     'shopId' => $this->shopId,
        //     'shopDomain' => $this->shopDomain,
        //     'siteHandle' => ($site ? $site->handle : null),
        //     'siteDomain' => ($site ? Shopify::$plugin->getSettings()->getStoreDomain($site->handle) : null),
        // ]);

        if (isset($this->shopId))
        {
            $shop = Shopify::$plugin->shops->getShopById($this->shopId);

            if (!$shop)
            {
                throw new InvalidConfigException(Craft::t('shopify-store',
                    'Could not find Shopify shop for Job settings'
                ));
            }
        }

        else if (isset($this->shopDomain)) {
            $shop = Shopify::$plugin->shops->getShopByDomain($this->shopDomain);
        }

        // @todo: One shop could support multiple sites, and vice-versa
        // default to shop domain, configured for the job's site
        else if (($site = $this->getSite())
            && ($shopDomain = Shopify::$plugin->getSettings()->getStoreDomain($site->handle)))
        {
            $shop = Shopify::$plugin->shops->getShopByDomain($shopDomain);
            if (!$shop) $shop = new Shop();

            if (StringHelper::contains($shopDomain, '.myshopify.com')) {
                $shop->myshopifyDomain = $shopDomain;
            } else {
                $shop->primaryDomain = $shopDomain;
            }
        }

        return $shop;
    }

    /**
     * Getter method for the `shopId` property
     *
     * @return Int
     */

    public function getShopId()
    {
        if (!isset($this->shopId) && ($shop = $this->getShop())) {
            $this->$shopId = $shop->id;
        }

        return $this->shopId;
    }

    /**
     * Getter method for the `shopDomain` property
     *
     * @return Int
     */

    public function getShopDomain()
    {
        if (!isset($this->shopDomain) && ($shop = $this->getShop())) {
            $this->$shopDomain = ($shop->primaryDomain ?? $shop->myshopifyDomain);
        }

        return $this->shopDomain;
    }

    // =Execution
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function getTtr()
    {
        return 1000;
    }

    /**
     * @inheritdoc
     */

    public function canRetry( $attempt, $error )
    {
        return ($attempt < 5) && ($error instanceof ApiRateLimitExceededException);
    }

    // =Protected Methods
    // =========================================================================

}
