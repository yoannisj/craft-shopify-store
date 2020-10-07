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

namespace yoannisj\shopify\events;

use craft\events\ModelEvent;

/**
 *
 */

class ShopEvent extends ModelEvent
{
    /**
     * @var yoannisj\shopify\models\Shop
     */

    public $shop;
}