<?php

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 */

namespace yoannisj\shopify\web\twig\variables;

use Craft;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\services\Shops;
use yoannisj\shopify\services\Products;
use yoannisj\shopify\services\ProductVariants;


/**
 * 
 */

class ShopifyVariable
{
    /**
     * @return \yoannisj\shopify\services\Shops
     */

    public function getShops(): Shops
    {
        return Craft::$app->getPlugins()->getPlugin('shopify')->getShops();
    }

    /**
     * @return \yoannisj\shopify\services\Products
     */

    public function getProducts(): Products
    {
        return Craft::$app->getPlugins()->getPlugin('shopify')->getProducts();
    }

    /**
     * @return \yoannis\shopify\services\ProductVariants
     */

    public function getProductVariants(): ProductVariants
    {
        return Craft::$app->getPlugins()->getPlugin('shopify')->getProductVariants();
    }

}