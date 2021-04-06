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

namespace yoannisj\shopify;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\CraftVariable;

use yoannisj\shopify\models\ShopifySettings;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\elements\Product;
use yoannisj\shopify\services\AdminApi;
use yoannisj\shopify\services\StorefrontApi;
use yoannisj\shopify\services\Shops;
use yoannisj\shopify\services\Products;
use yoannisj\shopify\services\ProductVariants;
use yoannisj\shopify\services\Checkouts;
use yoannisj\shopify\web\twig\variables\ShopifyVariable;

use yoannisj\shopify\fields\Products as ProductsField;

/**
 * Plugin class for Craft Shopify, loading all of the plugin's functionality in the system.
 * Gets instanciated at the beginning of every request to Craft, if the plugin is installed and enabled
 */

class Shopify extends Plugin
{

    // =Static
    // =========================================================================

    const TABLE_SHOPIFY_SHOPS = '{{%shopify_shops}}';
    const TABLE_SHOPIFY_PRODUCTS = '{{%shopify_products}}';

    /**
     * @var yoannisj\shopify\Shopify
     */

    public static $plugin;

    // =Properties
    // =========================================================================

    /**
     * @var bool
     */

    public $hasCpSection = true;

    /**
     * @var bool
     */

    // public $hasCpSettings = true;

    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function __construct( $id, $parent = null, array $config = [] )
    {
        // store reference to plugin instance
        self::$plugin = $this;

        // run inherited constructor
        return parent::__construct($id, $parent, $config);
    }

    /**
     * Method running when the plugin gets initialized
     * This is where all of the plugin's functionality gets loaded into the system
     */

    public function init()
    {
        parent::init();

        // register plugin services as components
        $this->setComponents([
            'adminApi' => AdminApi::class,
            'storefrontApi' => StorefrontApi::class,
            'shops' => Shops::class,
            'products' => Products::class,
            'productVariants' => ProductVariants::class,
            'checkouts' => Checkouts::class,
        ]);

        // register plugin fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event)
            {
                $event->types[] = ProductsField::class;
            }
        );

        // register plugin twig variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e)
            {
                /** @var CraftVariable $variable */
                $variable = $e->sender;
                $variable->set('shopify', ShopifyVariable::class);
            }
        );
    }

    /**
     * @inheritdoc
     */

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();

        $item['subnav'] = [
            'products' => [
                'label' => 'Products',
                'url' => 'shopify-store/products'
            ],
        ];

        return $item;
    }

    /**
     * Returns the plugin's `adminApi` component
     *
     * @return yoannisj\shopify\services\AdminApi
     */

    public function getAdminApi(): AdminApi
    {
        return $this->get('adminApi');
    }

    /**
     * Returns the plugin's `storefrontApi` component
     *
     * @return yoannisj\shopify\services\StorefrontApi
     */

    public function getStorefrontApi(): StorefrontApi
    {
        return $this->get('storefrontApi');
    }

    /**
     * Returns the plugin's `shops` component
     *
     * @return yoannisj\shopify\services\Shops
     */

    public function getShops(): Shops
    {
        return $this->get('shops');
    }

    /**
     * Returns the plugin's `products` component
     *
     * @return yoannisj\shopify\services\Products
     */

    public function getProducts(): Products
    {
        return $this->get('products');
    }

    /**
     * Returns the plugin's `products` component
     *
     * @return yoannisj\shopify\services\ProductVariants
     */

    public function getProductVariants(): ProductVariants
    {
        return $this->get('productVariants');
    }

    /**
     * Returns the plugin's `checkouts` component
     *
     * @return yoannisj\shopify\services\Checkouts
     */

    public function getCheckouts(): Checkouts
    {
        return $this->get('checkouts');
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    protected function createSettingsModel()
    {
        return new ShopifySettings();
    }

    /**
     * @inheritdoc
     */

    // protected function settingsHtml()
    // {
    //     return \Craft::$app->getView()->renderTemplate('shopify/settings', [
    //         'settings' => $this->getSettings()
    //     ]);
    // }
}