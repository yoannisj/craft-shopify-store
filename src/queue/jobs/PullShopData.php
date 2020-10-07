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

namespace yoannisj\shopify\queue\jobs;

use yii\base\InvalidConfigException;
use yii\queue\Queue;

use Craft;
use craft\queue\QueueInterface;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\elements\Product;
use yoannisj\shopify\queue\BaseShopifyJob;
use yoannisj\shopify\queue\jobs\PullProductData;

/**
 * Job class used to pull a Shopify shop's data using the API, and store it in the Database
 */

class PullShopData extends BaseShopifyJob
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var Bool
     */

    public $includeProducts = false;

    /**
     * @var Array
     */

    public $productAdminIds;

    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function execute( $queue )
    {
        $shop = $this->getShop();
        $siteHandle = $this->getSiteHandle();

        $currentStep = 1;
        $totalSteps = 1;

        if ($this->includeProducts)
        {
            // first step: fetch the ids of products to include
            if (!isset($this->productAdminIds))
            {
                // add a step
                $totalSteps++;

                // very first step: fetch ids of products to include
                $this->setProgress($queue, $currentStep / $totalSteps, Craft::t('shopify-store',
                    'Fetching product ids for shop "{shopTitle}"', [
                        'shopTitle' => (string)$shop
                    ]
                ));

                $this->productAdminIds = Shopify::$plugin->adminApi->getAllProductIds($siteHandle, false);
                $currentStep++;
            }

            // add one step for each included product
            $totalSteps += count($this->productAdminIds);

            // second step: pull the shop's own data
            $this->setProgress($queue, $currentStep / $totalSteps, Craft::t('shopify-store',
                'Pulling data for shop "{shopTitle}', [
                    'shopTitle' => (string)$shop
                ]
            ));

            Shopify::$plugin->shops->pullShopData($shop, $siteHandle);
            $currentStep++;

            $productIndex = 1;
            foreach ($this->productAdminIds as $productAdminId)
            {
                // next steps: pull an included product's data
                $this->setProgress($queue, $currentStep / $totalSteps, Craft::t('shopify-store',
                    'Pull data for "{shopTitle}" product {index} of {total}', [
                        'shopTitle' => (string)$shop,
                        'index' => ($currentStep - 2),
                        'total' => ($totalSteps - 2),
                    ]
                ));

                $this->includeProduct($productAdminId);
                $currentStep++;                
            }
        }

        else {
            Shopify::$plugin->shops->pullShopData($shop, $siteHandle);
        }
    }

    // =Protected Methods
    // =========================================================================

    /**
     * 
     */

    protected function includeProduct( $productAdminId )
    {
        $shop = $this->getShop();
        $site = $this->getSite();

        $product = Product::find()
            ->siteId($site->id)
            ->shopId($shop->id)
            ->adminId($productAdminId)
            ->one();

        if (!$product)
        {
            $product = new Product([
                'siteId' => $site->id,
                'shopId' => $shop->id,
                'adminId' => $productAdminId,
            ]);
        }

        Shopify::$plugin->products->pullProductData($product, $site->handle);
    }

    /**
     * @inheritdoc
     */

    protected function defaultDescription(): string
    {
        $shop = $this->getShop();

        return Craft::t('shopify-store', 'Pull Shopify shop data for "{shopTitle}"', [
            'shopTitle' => (string)$shop
        ]);
    }

}