<?php

namespace yoannisj\shopify\migrations;

use Craft;
use craft\db\Migration;
use craft\queue\jobs\ResaveElements;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\elements\Product;
use yoannisj\shopify\records\ProductRecord;

/**
 * m211104_144923_add_variant_storefront_ids_column migration.
 */

class m211104_144923_add_variant_storefront_ids_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->tableExists(Shopify::TABLE_SHOPIFY_PRODUCTS)) {
            return false;
        }

        $this->addColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantStorefrontIds',
            (string)$this->text());

        // resave products to populate the new column
        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
        $allProducts = Product::find()
            ->siteId($primarySiteId)
            ->all();

        foreach ($allProducts as $product)
        {
            $time = $this->beginCommand("Resave record for product with ID '".$product->id."'");

            $record = ProductRecord::findOne([ 'id' => $product->id ]) ?: new ProductRecord();
            $attributes = $product->getAttributes();
            $attributes['variantStorefrontIds'] = implode(',',
                $attributes['variantStorefrontIds']);

            $record->setAttributes($attributes, false);
            if (!$record->save()) return false;

            $this->endCommand($time);
        }

        $this->createIndex(null, Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantStorefrontIds', false);

        // resave products to populate the new column
        // Craft::$app->getQueue()->push(new ResaveElements([
        //     'elementType' => Product::class,
        //     'updateSearchIndex' => true,
        // ]));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->db->tableExists(Shopify::TABLE_SHOPIFY_PRODUCTS)) {
            $this->dropColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantStorefrontIds');
        }

        return true;
    }
}
