<?php 

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 */

namespace yoannisj\shopify\services;

use yii\base\Component;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\helpers\Db as DbHelper;
use craft\helpers\DateTimeHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\records\ShopRecord;
use yoannisj\shopify\events\ShopEvent;

/**
 * Service Class (singleton) to manage Shopify shops in Craft
 */

class Shops extends Component
{
    // =Static
    // =========================================================================

    const EVENT_BEFORE_PULL_SHOP_DATA = 'BEFORE_PULL_SHOP_DATA';
    const EVENT_AFTER_PULL_SHOP_DATA = 'AFTER_PULL_SHOP_DATA';

    const EVENT_BEFORE_SAVE_SHOP = 'BEFORE_SAVE_SHOP';
    const EVENT_AFTER_SAVE_SHOP = 'AFTER_SAVE_SHOP';

    const FEATURE_BRANDING_ROGERS = 'ROGERS';
    const FEATURE_BRANDING_SHOPIFY_GOLD = 'SHOPIFY_GOLD';
    const FEATURE_BRANDING_SHOPIFY_PLUS = 'SHOPIFY_PLUS';
    const FEATURE_BRANDING_SHOPIFY = 'SHOPIFY';

    const UNIT_SYSTEM_METRIC = 'METRIC_SYSTEM';
    const UNIT_SYSTEM_IMPERIAL = 'IMPERIAL_SYSTEM';

    const WEIGHT_UNIT_KILOGRAMS = 'KILOGRAMS';
    const WEIGHT_UNIT_GRAMS = 'GRAMS';
    const WEIGHT_UNIT_POUNDS = 'POUNDS';
    const WEIGHT_UNIT_OUNCES = 'OUNCES';

    const DIGITAL_WALLET_APPLE_PAY = 'APPLE_PAY';
    const DIGITAL_WALLET_ANDROID_PAY = 'ANDOID_PAY';
    const DIGITAL_WALLET_GOOGLE_PAY = 'GOOGLE_PAY';
    const DIGITAL_WALLET_SHOPIFY_PAY = 'SHOPIFY_PAY';

    // =Properties
    // =========================================================================

    /**
     * @var Array
     */

    protected $allShops;

    // =Public Methods
    // =========================================================================

    /**
     * Gets list of models for all Shopify shops saved in Craft
     *
     * @return Array
     */

    public function getAllShops(): array
    {
        if (!isset($this->allShops))
        {
            $this->allShops = [];

            $results = $this->createShopsQuery()
                ->all();

            foreach ($results as $record)
            {
                $shop = new Shop();
                $shop->setAttributes($record->getAttributes(), false);

                $this->allShops[] = $shop;
            }
        }

        return $this->allShops;
    }

    /**
     * Gets Shopify shop model for given id (as saved in craft)
     * 
     * @param Int $id
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getShopById( int $id )
    {
        $allShops = $this->getAllShops();
        return ArrayHelper::firstWhere($allShops, 'id', $id);
    }

    /**
     * Gets Shopify shop model for given id (as returned by Shopify's GraphQl Admin API)
     * 
     * @param String $id
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getShopByAdminId( string $id )
    {
        $allShops = $this->getAllShops();
        return ArrayHelper::firstWhere($allShops, 'adminId', $id);
    }

    /**
     * Gets Shopify shop model for given shop name
     * 
     * @param String $name
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getShopByName( string $name )
    {
        $allShops = $this->getAllShops();
        return ArrayHelper::firstWhere($allShops, 'name', $name);

    }

    /**
     * Gets Shopify shop model for given domain name
     * 
     * @param String $domain
     *
     * @return yoannisj\shopify\models\Shop | null
     */

    public function getShopByDomain( string $domain )
    {
        $allShops = $this->getAllShops();        

        // support `myshopifyDomain` property
        if (StringHelper::contains($domain, '.myshopify.com')) {
            return ArrayHelper::firstWhere($allShops, 'myshopifyDomain', $domain);
        }

        // support `primaryDomain` property
        return ArrayHelper::firstWhere($allShops, 'primaryDomain', $domain);
    }

    /**
     * Saves given Shopify shop in the database
     *
     * @param yoannisj\shopify\Shop $shop
     * @param Bool $runValidation
     *
     * @return Bool
     */

    public function saveShop( Shop $shop, bool $runValidation = true )
    {
        // make sure we don't create duplicates of existing shops
        $shopRecord = null;
        $conditions = [];

        if (isset($shop->adminId)) $conditions['adminId'] = $shop->adminId;
        if (isset($shop->myshopifyDomain)) $conditions['myshopifyDomain'] = $shop->myshopifyDomain;
        if (isset($shop->primaryDomain)) $conditions['primaryDomain'] = $shop->primaryDomain;

        if (!empty($conditions))
        {
            if (($shopRecord = ShopRecord::findOne($conditions))) {
                $shop->id = $shopRecord->id;
            }
        }

        // new shops don't have an id yet
        $isNewShop = !$shop->id;

        // fire a 'beforeSaveShop' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_SHOP))
        {
            $this->trigger(self::EVENT_BEFORE_SAVE_SHOP, new ShopEvent([
                'shop' => $shop,
                'isNew' => $isNewShop,
            ]));
        }

        // optionally validate shop before it gets saved in the Db
        if ($runValidation && !$shop->validate())
        {
            Craft::info('Shop not saved due to a validation error.', __METHOD__);
            return false;
        }

        // save shop using a transaction so no Db changes get saved if an error occurs
        $transaction = Craft::$app->getDb()->beginTransaction();

        try 
        {
            $now = new \DateTime();

            if ($isNewShop)
            {
                $shopRecord = new ShopRecord();

                $nowValue = DbHelper::prepareValueForDb($now);
                $shop->dateCreated = $nowValue;
                $shop->dateUpdated = $nowValue;
                $shop->uid = StringHelper::UUID();
            }

            else
            {
                $shop->dateCreated = $shopRecord->dateCreated;
                $shop->dateUpdated = $now;

                if (!$shop->uid) {
                    $shop->uid = DbHelper::uidById(Shopify::TABLE_SHOPIFY_SHOPS, $shop->id);
                }
            }

            // transfer shop attributes to a shop record model
            $shopRecord->setAttributes($shop->getAttributes(), false);
            // $shopRecord->shopifyData = DbHelper::prepareValueForDb($shopRecord->shopifyData);

            // avoid not-null violation error in PostrgeSQl, when trying to set `id` to `NULL` specifically
            // @link https://github.com/yiisoft/yii2/issues/7374
            if ($isNewShop) {
                unset($shopRecord->id);
            }

            // save record row in the Database (updates if there is a row with same id)
            $shopRecord->save(false);

            // we are through, commit the DB changes
            $transaction->commit();
        }

        catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($isNewShop) {
            $shop->id = $shopRecord->id;
        }

        // fire an 'afterSaveShop' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_SHOP))
        {
            $this->trigger(self::EVENT_AFTER_SAVE_SHOP, new ShopEvent([
                'shop' => $shop,
                'isNew' => $isNewShop,
            ]));
        }

        return true;
    }

    /**
     * Fetches data for given shop from Shopify API, and stores it in Craft's database
     *
     * @param yoannisj\shopify\models\Shop $shop
     * @param String $site
     *
     * @return Bool
     */

    public function pullShopData( Shop $shop, string $site = null ): bool
    {
        // @todo: Shops are not localized
        if (!$site || $site == '*')
        {
            $allSites = Craft::$app->getSites()->getAllSites();
            $supportedSites = $shop->getSupportedSites();

            foreach ($allSites as $site)
            {
                if (in_array($site->id, $supportedSites))
                {
                    if (!$this->pullShopData($shop, $site->handle)) {
                        return false;
                    }
                }
            }

            return true;
        }

        // fetch shop data using Shopify GraphQl Admin API
        $data = Shopify::$plugin->adminApi->getShopData($site, false);

        // update shop with pulled data
        $shop->shopifyData = $data;

        $shop->adminId = ArrayHelper::getValue($data, 'adminId');
        $shop->name = ArrayHelper::getValue($data, 'name');
        $shop->primaryDomain = ArrayHelper::getValue($data, 'primaryDomain');
        $shop->myshopifyDomain = ArrayHelper::getValue($data, 'myshopifyDomain');
        $shop->plan = ArrayHelper::getValue($data, 'plan');
        $shop->currencyCode = ArrayHelper::getValue($data, 'currencyCode');

        // save shop in the database
        return $this->saveShop($shop);
    }

    // =Protected Methods
    // =========================================================================

    /**
     * Creates a Query builder to fetch Shops from the db
     *
     * @return 
     */

    protected function createShopsQuery()
    {
        return ShopRecord::find();
    }

}