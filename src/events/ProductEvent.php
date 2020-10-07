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

use craft\events\ElementEvent;

/**
 *
 */

class ProductEvent extends ElementEvent
{
    /**
     * @var yoannisj\shopify\elements\Product
     */

    public $product;
}