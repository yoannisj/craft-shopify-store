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
use craft\queue\BaseJob;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\elements\Product;
use yoannisj\shopify\queue\BaseShopifyJob;

/**
 * Job class used to pull a Shopify product's data using the API, and store it in the Database
 */

class PullProductData extends BaseShopifyJob
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var int
     */

    public $productId;
 
    /**
     * @var String
     */

    public $productAdminId;

    /**
     * @var String
     */

    public $productHandle;

    /**
     * @var \yoannisj\shopify\elements\Product
     */

    protected $product;

    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function execute()
    {
        $product = $this->getProduct();
        $siteHandle = $this->getSiteHandle();

        Shopify::$plugin->products->pullProductData($product, $siteHandle);
    }

    /**
     * Getter method for the `product` property
     */

    public function getProduct()
    {
        if (!isset($this->product))
        {
            $shopId = $this->getShopId();

            if (isset($this->productId))
            {
                $product = Shopify::$plugin->products->getProductById($this->productId, $shopId);
                if ($product === null)
                {
                    throw new InvalidConfigException(Craft::t(
                        'Invalid product ID: {productId}', [
                            'productId' => $this->productId
                        ]
                    ));
                }
            }

            else if (isset($this->productAdminId))
            {
                // check for product with amdinId in the database
                $product = Shopify::$plugin->products->getProductByAdminId($this->productAdminId, $shopId);
                if ($product === null)
                {
                    // or instanciate a new one
                    $product = new Product([
                        'shopId' => $shopId,
                        'adminId' => $this->productAdminId,
                    ]);
                }
            }

            else if (isset($this->productHandle))
            {
                // check for product with handle in the database
                $product = Shopify::$plugin->products->getProductByHandle($this->productHandle, $shopId);
                if ($product === null)
                {
                    // or instanciate a new one
                    $product = new Product([
                        'shopId' => $shopId,
                        'handle' => $this->productHandle,
                    ]);
                }
            }

            if (!$product)
            {
                throw new InvalidConfigException(Craft::t('shopify-store',
                    'Could not get or create a Shopify product with Job settings.'
                ));
            }

            // @todo: Pull Shop data if shop not found

            $this->product = $product;
        }

        return $this->product;
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    protected function defaultDescription(): string
    {
        $product = $this->getProduct();

        if ($product->title)
        {
            return Craft::t('shopify-store', 'Pull Shopify product data for "{productTitle}"', [
                'productTitle' => $product->title,
            ]);
        }

        else if ($product->handle)
        {
            return Craft::t('shopify-store', 'Pull Shopify product data for product with handle `{productHandle}`', [
                'productHandle' => $product->handle
            ]);
        }

        else if ($product->adminId)
        {
            return Craft::t('shopify-store', 'Pull Shopify product data for product with id `{productAdminId}`', [
                'productAdminId' => $product->adminId
            ]);
        }

        return Craft::t('shopify-store', 'Pull Shopify product data');
    }

}