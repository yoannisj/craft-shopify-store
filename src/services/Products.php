<?php 

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 */

namespace yoannisj\shopify\services;

use yii\base\Component;

use Craft;
use craft\helpers\ArrayHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\elements\Product;

/**
 * Service Class (singleton) to manage Shopify products in Craft
 */

class Products extends Component
{
    // =Static
    // =========================================================================

    const EVENT_BEFORE_PULL_PRODUCT_DATA = 'BEFORE_PULL_PRODUCT_DATA';
    const EVENT_AFTER_PULL_PRODUCT_DATA = 'AFTER_PULL_PRODUCT_DATA';

    const EVENT_BEFORE_SAVE_PRODUCT = 'BEFORE_SAVE_PRODUCT';
    const EVENT_AFTER_SAVE_PRODUCT = 'AFTER_SAVE_PRODUCT';

    // =Properties
    // =========================================================================

    // =Public Methods
    // =========================================================================

    /**
     * Gets Shopify product model for given id (as saved in craft)
     * 
     * @param Int $id
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getProductById( int $id, $site = null )
    {
        return Product::find()
            ->site($site)
            ->id($id)
            ->one();
    }

    /**
     * Gets Shopify shop model for given id (as returned by Shopify's GraphQl Admin API)
     * 
     * @param String $id
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getProductByAdminId( string $id, $site = null )
    {
        return Product::find()
            ->site($site)
            ->one();
    }

    /**
     * Gets Shopify shop model for given id (as returned by Shopify's GraphQl Storefront API)
     * 
     * @param String $id
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getProductByStorefrontId( string $id, $site = null )
    {
        return Product::find()
            ->site($site)
            ->storefrontId($id)
            ->one();
    }

    /**
     * Gets Shopify shop model for given Shopify handle
     * 
     * @param String $handle
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getProductByHandle( string $handle, $site = null )
    {
        return Product::find()
            ->site($site)
            ->handle($handle)
            ->one();
    }

    /**
     * 
     */

    public function getProductByVariantAdminId( string $adminId, $site = null )
    {
        return Product::find()
            ->site($site)
            ->variantAdminId($adminId)
            ->one();
    }

    /**
     * 
     */

    public function getProductByVariantStorefrontId( string $storefrontId, $site = null )
    {
        return Product::find()
            ->site($site)
            ->variantStorefrontId($storefrontId)
            ->one();
    }

    /**
     * Fetches data for given Product from Shopify API, and stores it in Craft's database
     *
     * @param yoannisj\shopify\elements\Product $product
     * @param String $site
     *
     * @return Bool
     */

    public function pullProductData( Product $product, string $site = null ): bool
    {
        if (!$site || $site == '*')
        {
            $allSites = Craft::$app->getSites()->getAllSites();
            $supportedSites = $product->getSupportedSites();

            foreach ($allSites as $site)
            {
                if (in_array($site->id, $supportedSites))
                {
                    if (!$this->pullProductData($product, $site->handle)) {
                        return false;
                    }
                }
            }

            return true;
        }

        // fetch product data using Shopify GraphQl Admin API
        if (isset($product->adminId)) {
            $data = Shopify::$plugin->adminApi->getProductDataById($product->adminId, $site, false);
        } else if (isset($product->handle)) {
            $data = Shopify::$plugin->adminApi->getProductDataByHandle($product->handle, $site, false);
        } else {
            throw new InvalidCallException('Given product is missing its adminId or handle');
        }

        // update product with pulled data
        $product->setAttributes([
            'shopifyData' => $data,
            'adminId' => ArrayHelper::getValue($data, 'adminId'),
            'storefrontId' => ArrayHelper::getValue($data, 'storefrontId'),
            'handle' => ArrayHelper::getValue($data, 'handle'),
            'title' => ArrayHelper::getValue($data, 'title'),
            'description' => ArrayHelper::getValue($data, 'description'),
            'isGiftCard' => ArrayHelper::getValue($data, 'isGiftCard'),
            'productType' => ArrayHelper::getValue($data, 'productType'),
            'vendor' => ArrayHelper::getValue($data, 'vendor'),
        ], false);

        // save product to the database
        return Craft::$app->getElements()->saveElement($product);
    }

    // =Protected Methods
    // =========================================================================

}