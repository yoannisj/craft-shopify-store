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

/**
 * Active Record Class to work with Shopify shops in the DB
 *
 * @property Int $id
 * @property Array $shopifyData
 * @property String $adminId
 * @property String $name
 * @property String $primaryDomain
 * @property String $myshopifyDomain
 * @property String $plan
 * @property String $currencyCode
 * @property String $dateCreated
 * @property String $dateUpdated
 * @property String $dateDeleted
 * @property String $uid
 */

class ShopRecord extends ActiveRecord
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
        return Shopify::TABLE_SHOPIFY_SHOPS;
    }

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

    // =Properties
    // =========================================================================

    // =Public Methods
    // =========================================================================

}