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

namespace yoannisj\shopify\records;

use yii\db\ActiveQueryInterface;

use Craft;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\helpers\Json as JsonHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\records\ShopRecord;

/**
 * Active Record Class to work with Shopify products in the DB
 */

class ProductRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return string
     */

    public static function tableName(): string
    {
        return Shopify::TABLE_SHOPIFY_PRODUCTS;
    }

    // =Properties
    // =========================================================================

    // =Public Methods
    // =========================================================================

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * Getter method for the `shopifyData` attribute
     *
     * @return Array
     */

    public function getShopifyData(): array
    {
        if (is_string($this->shopifyData)) {
            $this->shopifyData = JsonHelper::decode($this->shopifyData);
        }

        return $this->shopifyData;
    }

    // =Relations
    // -------------------------------------------------------------------------

    /**
     * Returns the product's shop.
     *
     * @return ActiveQueryInterface The relational query object.
     */

    public function getShop(): ActiveQueryInterface
    {
        return $this->hasOne(ShopRecord::class, ['id' => 'shopId']);
    }
}
