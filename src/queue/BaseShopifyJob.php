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
     * @var String
     */

    public $siteHandle;

    /**
     * @var \craft\models\Site | null
     */

    protected $site;

    /**
     * @var Int
     */

    public $shopId;

    /**
     * @var String
     */

    public $shopDomain;

    /**
     * @var yoannisj\shopify\models\Shop | null
     */

    protected $shop;

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
        if (!isset($this->site))
        {
            if (isset($this->siteId))
            {
                $site = Craft::$app->getSites()->getSiteById($this->siteId);
                if ($site === null) {
                    throw new InvalidConfigException('Invalid site ID: ' . $this->siteId);
                }
            }

            else if (isset($this->siteHandle))
            {
                $site = Craft::$app->getSites()->getSiteByHandle($this->siteHandle);
                if ($site === null) {
                    throw new InvalidConfigException('Invalid site handle: ' . $this->siteHandle);
                }
            }

            else {
                $site = Craft::$app->getSites()->getCurrentSite();
            }

            $this->site = $site;
        }

        return $this->site;
    }

    /**
     * Getter method for the `siteId` property
     *
     * @return Int
     */

    public function getSiteId()
    {
        if (!isset($this->siteId))
        {
            if (($site = $this->getSite())) {
                $this->$siteId = $site->id;
            }
        }

        return $this->siteId;
    }

    /**
     * Getter method for the `siteHandle` property
     *
     * @return String
     */

    public function getSiteHandle()
    {
        if (!isset($this->siteHandle))
        {
            if (($site = $this->getSite())) {
                $this->siteHandle = $site->handle;
            }
        }

        return $this->siteHandle;
    }

    /**
     * Getter method for the `shop` property
     *
     * @return yoannisj\shopify\models\Shop
     * @throws InvalidConfigException
     */

    public function getShop(): Shop
    {
        $site = $this->getSite();

        if (!isset($this->shop))
        {
            $shop = null;

            if (isset($this->shopId)) {
                $shop = Shopify::$plugin->shops->getShopById($this->shopId, $site->id);
            }

            else
            {
                // @todo: One shop could support multiple sites, and vice-versa
                // default to shop domain, configured for the job's site
                if (!isset($this->shopDomain) && $site) {
                    $this->shopDomain = Shopify::$plugin->getSettings()->getStoreDomain($site->handle);
                }

                if (isset($this->shopDomain))
                {
                    $shop = new Shop();

                    if (!StringHelper::contains($this->shopDomain, '.myshopify.com')) {
                        $shop->primaryDomain = $this->shopDomain;
                    } else {
                        $shop->myshopifyDomain = $this->shopDomain;
                    }

                    if (!in_array($site->id, $shop->getSupportedSites())) {
                        unset($shop);
                    }
                }

                if (!$shop) {
                    throw new InvalidConfigException(Craft::t('shopify-store',
                        'Could not find Shopify shop for Job settings'
                    ));
                }
            }

            $this->shop = $shop;
        }

        return $this->shop;
    }

    /**
     * Getter method for the `shopId` property
     *
     * @return Int
     */

    public function getShopId()
    {
        if (!isset($this->shopId))
        {
            if (($shop = $this->getShop())) {
                $this->$shopId = $shop->id;
            }
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
        if (!isset($this->shopDomain))
        {
            if (($shop = $this->getShop())) {
                $this->$shopDomain = $shop->primaryDomain ?? $shop->myshopifyDomain;
            }
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