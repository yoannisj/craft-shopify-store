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

namespace yoannisj\shopify\elements;

use yii\base\InvalidConfigException;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\StringHelper;
use craft\helpers\ArrayHelper;
use craft\helpers\Json as JsonHelper;
use craft\helpers\Db as DbHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\models\PriceData;
use yoannisj\shopify\models\PriceRange;
use yoannisj\shopify\records\ProductRecord;
use yoannisj\shopify\records\ProductVariantRecord;
use yoannisj\shopify\helpers\ApiHelper;

use yoannisj\shopify\elements\db\ProductVariantQuery;

/**
 * Craft element class for Shopify product variants
 */

class ProductVariant extends Element
{
    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('shopify-store', 'Product Variant');
    }

    /**
     * @inheritdoc
     */

    public static function pluralDisplayName(): string
    {
        return Craft::t('shopify-store', 'Product Variants');
    }

    /**
     * @inheritdoc
     */

    public static function hasContent(): bool
    {
        // @todo: Add content with multiple field layouts to shopify products 
        return false;
    }

    /**
     * @inheritdoc
     */

    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */

    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */

    public static function hasStatuses(): bool
    {
        // @todo: Create statuses in Craft to map Shopify product's `published` status
        return false;
    }

    /**
     * @inheritdoc
     */

    public static function find(): ElementQueryInterface
    {
        return new ProductVariantQuery(static::class);
    }

    // =Properties
    // =========================================================================

    /**
     * @var Int
     */

    public $id;

    /**
     * @var Int
     */

    public $shopId;

    /**
     * @var String
     */

    public $adminId;

    /**
     * @var String
     */

    public $legacyResourceId;

    /**
     * @var String
     */

    public $storefrontId;

    /**
     * @var Int
     */

    public $productId;

    /**
     * @var string
     */

    private $_displayName;

    /**
     * @var int
     */

    public $position;

    /**
     * @var array
     */

    public $selectedOptions;

    /**
     * @var Money
     */

    public $price;

    /**
     * @var Money
     */

    public $compareAtPrice;

    /**
     * @var string
     */

    public $sku;

    /**
     * @var string
     */

    public $barcode;

    /**
     * @var bool
     */

    public $availableForSale;

    /**
     * @var string
     */

    public $inventoryPolicy;

    /**
     * @var int
     */

    public $inventoryQuantity;

    /**
     * @var bool
     */

    public $taxable;

    /**
     * @var string
     */

    public $taxCode;

    /**
     * @var float
     */

    public $weight;

    /**
     * @var string
     */

    public $weightUnit;

    /**
     * @var array
     */

    public $shopifyData;

    // =Public Methods
    // =========================================================================

    // =Attributes
    // -------------------------------------------------------------------------

    // =Fields
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    // public function extraFields()
    // {
    //     $fields = parent::extraFields();

    //     $fields['selectedOptions'];

    //     return $fields;
    // }

    // =Content
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function getSupportedSites(): array
    {
        $siteIds = $this->getShop()->getSupportedSites();

        return $siteIds;
    }


    /**
     * @inheritdoc
     */

    public function getUriFormat()
    {
        $siteHandle = $this->getSite()->handle ?? null;

        return Shopify::$plugin->getSettings()->getProductVariantUriFormat($siteHandle);
    }
}
