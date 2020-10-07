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

namespace yoannisj\shopify\fields;

use Craft;
use craft\fields\BaseRelationField;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\elements\Product;

class Products extends BaseRelationField
{
    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('shopify-store', 'Shopify Products');
    }

    /**
     * @inheritdoc
     */

    protected static function elementType(): string
    {
        return Product::class;
    }

    /**
     * @inheritdoc
     */

    public static function defaultSelectionLabel(): string
    {
        return Craft::t('shopify-store', 'Add a product');
    }

    // =Properties
    // =========================================================================

    /**
     * @inheritdoc
     */

    public $allowMultipleSources = false;

}
