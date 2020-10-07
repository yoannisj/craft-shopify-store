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
 * Service Class (singleton) to manage Shopify product variants in Craft
 */

class ProductVariants extends Component
{
    /**
     * 
     */

    public function getVariantByAdminId( $adminId, $site = null )
    {
        $product = Shopify::$plugin->products->getProductByVariantAdminId($adminId, $site);
        
        if ($product)
        {
            $variants = $product->getVariants();
            return ArrayHelper::firstWhere($variants, 'adminId', $adminId);
        }

        return null;
    }

    /**
     * 
     */

    public function getVariantByStorefrontId( $storefrontId, $site = null )
    {
        $product = Shopify::$plugin->products->getProductByVariantStorefrontId($storefrontId, $site);

        if ($product)
        {
            $variants = $product->getVariants();
            return ArrayHelper::firstWhere($variants, 'storefrontId', $adminId);
        }

        return null;
    }
}