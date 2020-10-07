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

namespace yoannisj\shopify\elements\db;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db as DbHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\elements\Product;
use yoannisj\shopify\elements\ProductVariant;

/**
 * Class to build database queries for shopifyProduct elements
 */

class ProductVariantQuery extends ElementQuery
{
    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     */

    public static function elementType(): string
    {
        return ProductVariant::class;
    }

    // =Properties
    // =========================================================================

    /**
     * @var String
     */

    public $adminId;


    /**
     * @var integer
     */

    public $shopifyId;

    /**
     * @var String
     */

    public $storefrontId;

   /**
     * @var int
     */

    public $shopId;

    /**
     * @var int
     */

    public $productId;

    /**
     * @var string
     */

    public $productAdminId;

    /**
     * @var int
     */

    public $productShopifyId;

    /**
     * @var string
     */

    public $productStorefrontId;

    // =Public Methods
    // ========================================================================

    /**
     * @param string | array | null $value
     */

    public function adminId( $value )
    {
        $this->adminId = $value;
    }

    /**
     * @param int | array | null $value
     */

    public function shopifyId( $value )
    {
        $this->shopifyId = $value;
    }

    /**
     * @param int | array | null $value
     */

    public function storefrontId( $value )
    {
        $this->storefrontId = $value;
    }

    /**
     * @param int | array | null $value
     */

    public function shopId( $value )
    {
        $this->shopId = $value;
        return $this;
    }

    /**
     * @param yoannisj\shopify\models\Shop | int | int[] | null $value
     */

    public function shop( $value )
    {
        if ($value instanceof Shop) {
            $this->shopId = $value->id;
        }

        else if ($value !== null)
        {
            $this->shopId = (new Query())
                ->select(['id'])
                ->from([ Shopify::TABLE_SHOPS ])
                ->where(Db::parseParam('primaryDomain', $value))
                ->orWhere(Db::parseParam('myshopifyDomain', $value))
                ->orWhere(Db::parseParam('name', $value))
                ->column();
        }

        else {
            $this->shopId = null;
        }

        return $this;
    }

    /**
     * @param int | string | array | null $value
     */

    public function productId( $value )
    {
        $this->productId = $value;
        return $this;
    }

    /**
     * @param int | string | array | null $value
     */

    public function productAdminId( $value )
    {
        $this->productAdminId = $value;
        return $this;
    }

    /**
     * @param int | string | array | null $value
     */

    public function productShopifyId( $value )
    {
        $this->productShopifyId = $value;
        return $this;
    }

    /**
     * @param int | string | array | null $value
     */

    public function productStorefrontId( $value )
    {
        $this->productStorefrontId = $value;
        return $this;
    }

    /**
     * @param \yoannisj\shopify\elements\Product | int | string | array | null $value
     */

    public function product( $value )
    {
        if ($value instanceof Product) {
            $this->productId = $value;
        }

        else if ($value !== null )
        {
            $this->productId = (new Query())
                ->select(['id'])
                ->from([ Shopify::TABLE_PRODUCTS ])
                ->where(Db::parseParam('handle', $value))
                ->orWhere(Db::parseParam('slug', $value))
                ->column();
        }

        else {
            $this->productId = null;
        }

        return $this;
    }

    // =Magic Methods
    // ========================================================================

    /**
     * @inheritdoc
     */

    public function __set( $name, $value )
    {
        switch ($name)
        {
            case 'shop':
                $this->shop($value);
                break;
            case 'product':
                $this->product($value);
                break;
            default:
                parent::__set($name, $value);
        }
    }

}