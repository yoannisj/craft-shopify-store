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

class m211227_165456_add_variant_legacyresourceid_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->tableExists(Shopify::TABLE_SHOPIFY_PRODUCTS)) {
            return false;
        }

        $this->addColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceId',
            (string)$this->bigInteger());

        $this->addColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceIds',
            (string)$this->longText());

        // resave products to populate the new column
        $allProducts = Product::find()
            ->limit(null)
            ->site('*')
            ->all();

        foreach ($allProducts as $product)
        {
            $time = $this->beginCommand("Resave record for product with ID '".$product->id."'");

            $record = ProductRecord::findOne([ 'id' => $product->id ]) ?: new ProductRecord();
            $attributes = $product->getAttributes();
            $attributes['legacyResourceIds'] = implode(',',
                $attributes['legacyResourceIds']);

            $record->setAttributes($attributes, false);
            if (!$record->save()) return false;

            $this->endCommand($time);
        }

        $this->createIndex(null, Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceId', false);
        $this->createIndex(null, Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceIds', false);

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
            $this->dropColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceId');
            $this->dropColumn(Shopify::TABLE_SHOPIFY_PRODUCTS, 'variantLegacyResourceIds');
        }

        return true;
    }
}
