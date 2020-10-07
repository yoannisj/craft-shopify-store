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
use craft\elements\actions\Delete;
use craft\elements\actions\Restore;
use craft\helpers\StringHelper;
use craft\helpers\ArrayHelper;
use craft\helpers\Json as JsonHelper;
use craft\helpers\Db as DbHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\models\PriceData;
use yoannisj\shopify\models\PriceRange;
use yoannisj\shopify\records\ProductRecord;
use yoannisj\shopify\helpers\ApiHelper;

use yoannisj\shopify\elements\db\ProductQuery;

class Product extends Element
{
    // =Static
    // =========================================================================

    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('shopify-store', 'Product');
    }

    /**
     * @inheritdoc
     */

    public static function pluralDisplayName(): string
    {
        return Craft::t('shopify-store', 'Products');
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
        return new ProductQuery(static::class);
    }

    /**
     * @inheritdoc
     */

    protected static function defineSources(string $context = null): array
    {
        $sources = [];

        $storeDomains = Shopify::$plugin->getSettings()->storeDomain;

        if (is_array($storeDomains))
        {
            $sources[] = [
                'key' => '*',
                'label' => Craft::t('shopify-store', 'All Products'),
                'criteria' => [],
                'defaultSort' => ['dateCreated', 'desc']
            ];

            foreach ($storeDomains as $domain)
            {
                $shop = Shopify::$plugin->shops->getShopByDomain($domain);

                if ($shop)
                {
                    $shopHandle = StringHelper::camelCase($shop->name);
                    $sources[] = [
                        'key' => $shopHandle,
                        'label' => $shop->name,
                        'criteria' => [
                            'shopId' => $shop->id
                        ],
                        'defaultSort' => ['dateCreated', 'desc']
                    ];
                }
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */

    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        $elementsService = Craft::$app->getElements();

        // @todo: Implement pull shopify product data action
        // @todo: Implement pull shopify product translations action
        // @todo Implement push shopify product data action
        // @todo: Implement push shopify product translations action

        // @todo: setup user permission for product deletion
        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('app', 'Are you sure you want to delete the selected products?'),
            'successMessage' => Craft::t('app', 'Products deleted.'),
        ]);

        // @todo: setup user permission for product creation / restoration
        $actions[] = $elementsService->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('app', 'Products restored.'),
            'partialSuccessMessage' => Craft::t('app', 'Some products restored.'),
            'failMessage' => Craft::t('app', 'Products not restored.'),
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => \Craft::t('app', 'Title'),
            'handle' => \Craft::t('shopify-store', 'Handle'),
            'shop' => \Craft::t('shopify-store', 'Shop'),
            'vendor' => \Craft::t('shopify-store', 'Vendor'),
            'productType' => \Craft::t('shopify-store', 'Product Type'),
            'tags' => \Craft::t('shopify-store', 'Tags'),
            'collections' => \Craft::t('shopify-store', 'Collections'),
            'dateCreated' => \Craft::t('app', 'Date Created'),
            'dateUpdated' => \Craft::t('app', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     */

    protected static function defineDefaultTableAttributes( string $source ): array
    {
        $attributes = [ 'title' ];

        if ($source == '*')
        {
            $attributes[] = 'shop';
        }

        $attributes = array_merge($attributes, [
            'vendor',
            'productType',
            'dateCreated',
            'dateUpdated',
        ]);

        return $attributes;
    }

    /**
     * @inheritdoc
     */

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'tags':
            case 'collections':
                $labels = ArrayHelper::getColumn($this->$attribute, 'handle');
                return implode(', ', $labels);
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */

    protected static function defineSearchableAttributes(): array
    {
        return [
            'id',
            'shop',
            'adminId',
            'handle',
            'variantAdminId',
            'variantStorefrontId',
            'title',
            'vendor',
            'productType',
            // 'tags',
            // 'collections'
        ];
    }

    /**
     *  @inheritdoc
     */

    protected static function defineSortOptions(): array
    {
        $sortOptions = parent::defineSortOptions();

        unset($sortOptions['shop']);

        return $sortOptions;
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
     * @var Array
     */

    protected $shopifyData;

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

    // public $legacyResourceId;

    /**
     * @var String
     */

    public $handle;

    /**
     * @var String
     */

    public $_variantAdminId;

    /**
     * @var String
     */

    public $_variantStorefrontId;

    /**
     * @var String
     */

    public $title;

    /**
     * @var String
     */

    // public $descriptionHtml;

    /**
     * @var String
     */

    // public $description;

    /**
     * @var Array
     */

    // public $seo;

    /**
     * @var Int
     */

    // public $featuredImageId;

    /**
     * @var String
     */

    // public $onlineStoreUrl;

    /**
     * @var Bool
     */

    public $isGiftCard;

    /**
     * @var String
     */

    public $productType;

    /**
     * @var String
     */

    public $vendor;

    /**
     * @var Array
     */

    // public $tags;

    /**
     * @var Bool
     */

    // public $hasOnlyDefaultVariant;

    /**
     * @var Int
     */

    // public $totalVariants;

    /**
     * @var Bool
     */

    // public $tracksInventory;

    /**
     * @var Int
     */

    // public $totalInventory;

    /**
     * @var Bool
     */

    // public $hasOutOfStockVariants;

    /**
     * @var Array
     */

    // public $options;

    /**
     * @var Array
     */

    // public $priceRange;

    /**
     * @var [ ProductVariant ]
     */

    private $_variants;

    /**
     * @var [ Int ]
     */

    private $_variantAdminIds;

    /**
     * @var [ Int ]
     */

    private $_variantStorefontId;

    /**
     * @var array [ Collection ]
     */

    private $_collections;

    /**
     * @var array [ string ]
     */

    private $_collectionHandles;

    /**
     * @var array [ string ]
     */

    protected $_collectionAdminIds;

    /**
     * @var array [ string ]
     */

    protected $_collectionStorefrontIds;

    // =Public Methods
    // =========================================================================

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function __get( $prop )
    {
        // if this is a valid property, that does not have its own getter
        if (!method_exists($this, 'get'.ucfirst($prop)))
        {
            // inspect shopifyData for unset properties
            if (isset($this->shopifyData)) {
                $value = ArrayHelper::getValue($this->shopifyData, $prop);
            }

            return $value;
        }

        return parent::__get($prop);
    }

    /**
     * @inheritdoc
     */

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes = array_unique(array_merge($attributes, [
            'shopifyData',
            'variantAdminId',
            'variantStorefrontId',
        ]));

        return $attributes;
    }

    /**
     * Getter method for the `shop` property
     *
     * @return \yoannisj\shopify\models\Shop
     *
     * @throws \yii\base\InvalidConfigException
     */

    public function getShop(): Shop
    {
        if ($this->shopId === null) {
            throw new InvalidConfigException('Product is missing its shop ID');
        }

        if (($shop = Shopify::$plugin->shops->getShopById($this->shopId)) === null) {
            throw new InvalidConfigException('Invalid shop ID: ' . $this->shopId);
        }

        return $shop;
    }

    /**
     * Setter method for the `shopifyData` property
     *
     * @param Array | String $shopifyData
     * @return yoannisj\shopify\elements\Product
     */

    public function setShopifyData( $shopifyData )
    {
        if (is_string($shopifyData)) {
            $shopifyData = JsonHelper::decode($shopifyData);
        }

        $this->shopifyData = $shopifyData;

        return $this;
    }

    /**
     * Getter method for the `shopifyData` attribute
     *
     * @return Array
     */

    public function getShopifyData(): array
    {
        return $this->shopifyData ?? [];
    }

    /**
     * Setter method for the `seo` property
     *
     * @param Array $seo
     * @return yoannisj\shopify\elements\Product
     */

    public function setSeo( array $seo ): Product
    {
        if (!isset($this->shopifyData)) {
            $this->shopifyData = [];
        }

        $this->shopifyData['seo'] = array_merge([
            'title' => $this->title,
            'description' => $this->description
        ], $this->getSeo());

        return $this;
    }

    /**
     * Getter method for the `seo` property
     *
     * @return Array
     */

    public function getSeo(): array
    {
        if (!isset($this->shopifyData)) {
            $this->shopifyData = [];
        }

        return ArrayHelper::getValue($this->shopifyData, 'seo') ?? [
            'title' => $this->title,
            'description' => $this->description
        ];
    }

    /**
     * Getter method for the '`variants` property
     *
     * @return Array
     */

    public function getVariants(): array
    {
        if (!isset($this->_variants))
        {
            $variantsData = ArrayHelper::getValue($this->shopifyData, 'variants');
            $variants = ApiHelper::getConnectionNodes($variantsData);

            $this->_variants = $variants;
        }

        return $this->_variants;
    }

    /**
     * Getter method for the `variantAdminIds` property
     *
     * @return Array
     */

    public function getVariantAdminIds(): array
    {
        if (!isset($this->_varaintAdminIds))
        {
            $variants = $this->getVariants();
            $this->_varaintAdminIds = ArrayHelper::getColumn($variants, 'adminId');
        }

        return $this->_varaintAdminIds;
    }

    /**
     * Getter method for the `variantStorefrontIds` property
     *
     * @return Array
     */

    public function getVariantStorefrontIds(): array
    {
        if (!isset($this->_variantStorefrontIds))
        {
            $variants = $this->getVariants();
            $this->_variantStorefrontIds = ArrayHelper::getColumn($variants, 'storefrontId');
        }

        return $this->_variantStorefrontIds;
    }

    /**
     * Getter method for the `defaultVariant` property
     *
     * @return Array
     */

    public function getDefaultVariant()
    {
        return ArrayHelper::firstValue($this->getVariants());
    }

    /**
     * @param String
     */

    public function setVariantAdminId( $adminId )
    {
        $this->_variantAdminId = $adminId;
    }

    /**
     * @return String
     */

    public function getVariantAdminId(): string
    {
        if (!isset($this->_variantAdminId))
        {
            $defaultVariant = $this->getDefaultVariant();
            $this->_variantAdminId = trim(ArrayHelper::getValue($defaultVariant, 'adminId'));
        }

        return $this->_variantAdminId;
    }

    /**
     * @param String | null
     */

    public function setVariantStorefrontId( $storefrontId )
    {
        $this->_variantStorefrontId = $storefrontId;
    }

    /**
     * @return String
     */

    public function getVariantStorefrontId(): string
    {
        if (!isset($this->_variantStorefrontId))
        {
            $defaultVariant = $this->getDefaultVariant();
            $this->_variantStorefrontId = trim(ArrayHelper::getValue($defaultVariant, 'storefrontId'));
        }

        return $this->_variantStorefrontId;
    }

    /**
     * Getter method for the `collections` property
     *
     * @return Array
     */

    public function getCollections(): array
    {
        if (!isset($this->_collections))
        {
            $collectionsData = ArrayHelper::getValue($this->shopifyData, 'collections');
            $collections = ApiHelper::getConnectionNodes($collectionsData);

            $this->_collections = $collections;
        }

        return $this->_collections;
    }

    /**
     * Getter method for the `collectionHandles` property
     *
     * @return Array
     */

    public function getCollectionHandles(): array
    {
        if (!isset($this->_collectionHandles))
        {
            $collections = $this->getCollections();
            $this->_collectionHandles = ArrayHelper::getColumn($collections, 'handle');
        }

        return $this->_collectionHandles;
    }

    /**
     * Getter method for the `adminCollectionIds` property
     *
     * @return Array
     */

    public function getCollectionAdminIds(): array
    {
        if (!isset($this->_collectionAdminIds))
        {
            $collections = $this->getCollections();
            $this->_collectionAdminIds = ArrayHelper::getColumn($collections, 'adminId');
        }

        return $this->_collectionAdminIds;
    }

    /**
     * Getter method for the `adminCollectionIds` property
     *
     * @return Array
     */

    public function getCollectionStorefrontIds(): array
    {
        if (!isset($this->_collectionStorefrontIds))
        {
            $collections = $this->getCollections();
            $this->_collectionStorefrontIds = ArrayHelper::getColumn($collections, 'storefrontId');
        }

        return $this->_collectionStorefrontIds;
    }

    // =Validation
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     *
     * @return Array
     */

    public function rules()
    {
        $rules = parent::rules();

        $rules['shopIdRequired'] = [ ['shopId'], 'required' ];
        $rules['integerIds'] = [ ['shopId'], 'number', 'integerOnly' => true ];

        return $rules;
    }

    // =Fields
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function fields()
    {
        return [
            'id',
            'createdAt',
            'updatedAt',
            'shopifyData',
            'defaultVariant',
        ];
    }

    /**
     * 
     */

    public function getDescriptionHtml()
    {
        if ($this->shopifyData) {
            return ArrayHelper::getValue($this->shopifyData, 'descriptionHtml');
        }

        return null;
    }

    // =Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function afterSave( bool $isNew )
    {
        if ($isNew) {
            $productRecord = new ProductRecord();
        }

        else {
            $productRecord = ProductRecord::findOne($this->id);
        }

        $productRecord->setAttributes($this->getAttributes(), false);
        $productRecord->save(false);

        parent::afterSave($isNew);
    }

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


    /**
     * @inheritdoc
     */

    public function getUriFormat()
    {
        $siteHandle = $this->getSite()->handle ?? null;

        return Shopify::$plugin->getSettings()->getProductUriFormat($siteHandle);
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    protected function route()
    {
        $siteHandle = $this->getSite()->handle ?? null;
        $template = Shopify::$plugin->getSettings()->getProductTemplate($siteHandle);

        return [
            'templates/render', [
                'template' => $template,
                'variables' => [
                    'product' => $this,
                ]
            ]
        ];
    }

}
