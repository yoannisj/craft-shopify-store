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
use yoannisj\shopify\elements\ShopifyProduct;

/**
 * Class to build database queries for shopifyProduct elements
 */

class ProductQuery extends ElementQuery
{

    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     */

    public static function elementType(): string
    {
        return ShopifyProduct::class;
    }

    // =Properties
    // =========================================================================

    /**
     * @var int
     */

    public $shopId;

    /**
     * @var String
     */

    public $adminId;

    /**
     * @var String
     */

    public $storefrontId;

    /**
     * @var String
     */

    public $handle;

    /**
     * @var String
     */

    public $variantAdminId;

    /**
     * @var String
     */

    public $variantStorefrontId;

    /**
     * @var String
     */

    public $title;

    /**
     * @var Bool
     */

    public $giftCard;

    /**
     * @var String
     */

    public $productType;

    /**
     * @var String
     */

    public $vendor;

    // =Public Methods
    // =========================================================================

    /**
     * Setter method for the `shopId` criteria
     */

    public function shopId( $value ): ElementQuery
    {
        $this->shopId = $value;

        return $this;
    }

    /** 
     * Setter method for the `shop` criteria
     */

    public function shop( $value ): ElementQuery
    {
        // accept shop model
        if ($value instanceof Shop)
        {
            $this->shopId = $value->id;
        }

        // accept shop's primary domain
        else if (is_string($value))
        {
            $this->shopId = (new Query())
                ->select(['id'])
                ->from([ Shopify::TABLE_SHOPIFY_SHOPS ])
                ->where(DbHelper::parseParam('primaryDomain', $value))
                ->orWhere(DbHelper::parseParam('myshopifyDomain', $value))
                ->column();
        }

        // allow unsetting the shopId criteria
        else {
            $this->shopId = null;
        }

        return $this;
    }

    /**
     * Setter method for the `shpoifyId` criteria
     */

    public function adminId( $value ): ElementQuery
    {
        $this->adminId = $value;

        return $this;
    }

    /**
     * Setter method for the `shopifyId` criteria
     */

    public function storefrontId( $value ): ElementQuery
    {
        $this->storefrontId = $value;

        return $this;
    }

    /**
     * Setter method for the `handle` criteria
     */

    public function handle( $value ): ElementQuery
    {
        $this->handle = $value;

        return $this;
    }

    /**
     * 
     */

    public function variantAdminId( $value ): ElementQuery
    {
        $this->variantAdminId = $value;

        return $this;
    }

    /**
     * 
     */

    public function variantStorefrontId( $value ): ElementQuery
    {
        $this->variantStorefrontId = $value;

        return $this;
    }

    /**
     * Setter method for the `title` criteria
     */

    public function title( $value ): ElementQuery
    {
        $this->title = $value;

        return $this;
    }

    /**
     * Setter method for the `giftCard` criteria
     */

    public function giftCard( $value ): ElementQuery
    {
        $this->giftCard = !!($value);

        return $this;
    }

    /**
     * Setter method for the `productType` criteria
     */

    public function productType( $value ): ElementQuery
    {
        $this->productType = $value;

        return $this;
    }

    /**
     * Setter method for the `vendor` criteria
     */

    public function vendor( $value ): ElementQuery
    {
        $this->vendor = $value;

        return $this;
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    protected function beforePrepare(): bool
    {
        $productsTable = Shopify::TABLE_SHOPIFY_PRODUCTS;

        // does the same as '$this->joinTable', but without needing to know the table's actual name
        $joinTable = "{$productsTable} shopify_products";
        $this->query->innerJoin($joinTable, "[[shopify_products.id]] = [[subquery.elementsId]]");
        $this->subQuery->innerJoin($joinTable, "[[shopify_products.id]] = [[elements.id]]");

        // select the criteria columns
        $this->query->select([
            'shopify_products.shopId',
            'shopify_products.shopifyData',
            'shopify_products.adminId',
            'shopify_products.storefrontId',
            'shopify_products.handle',
            'shopify_products.title',
            'shopify_products.isGiftCard',
            'shopify_products.productType',
            'shopify_products.vendor',
        ]);

        // apply 'shopId' criteria as 'where' conditions to the subQuery
        if ($this->shopId) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.shopId', $this->shopId));
        }

        // apply 'adminId' criteria as 'where' conditions to the subQuery
        if ($this->adminId) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.adminId', $this->adminId));
        }

        // apply 'storefrontId' criteria as 'where' conditions to the subQuery
        if ($this->storefrontId) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.storefrontId', $this->storefrontId));
        }

        // apply 'handle' criteria as 'where' conditions to the subQuery
        if ($this->handle) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.handle', $this->handle));
        }

        // apply 'variantAdminId' criteria as 'where' conditions to the subQuery
        if ($this->variantAdminId) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.variantAdminId', $this->variantAdminId));
        }

        // apply 'variantStorefrontId' criteria as 'where' conditions to the subQuery
        if ($this->variantStorefrontId) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.variantStorefrontId', $this->variantStorefrontId));
        }

        // apply 'title' criteria as 'where' conditions to the subQuery
        if ($this->title) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.title', $this->title));
        }

        // apply 'productType' criteria as 'where' conditions to the subQuery
        if ($this->giftCard) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.isGiftCard', $this->giftCard));
        }

        // apply 'productType' criteria as 'where' conditions to the subQuery
        if ($this->productType) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.productType', $this->productType));
        }

            // apply 'vendor' criteria as 'where' conditions to the subQuery
        if ($this->vendor) {
            $this->subQuery->andWhere(DbHelper::parseParam('shopify_products.vendor', $this->productType));
        }

        return parent::beforePrepare();
    }

}
