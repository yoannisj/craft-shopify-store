<?php

namespace yoannisj\shopify\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table;

use yoannisj\shopify\Shopify;

/**
 * Migration class ran during plugin's (un-)installation
 */

class Install extends Migration
{
    /**
     * @inheritdoc
     */

    public function safeUp()
    {
        // get table names
        $elementsTable = Table::ELEMENTS;
        $shopsTable = Shopify::TABLE_SHOPIFY_SHOPS;
        $productsTable = Shopify::TABLE_SHOPIFY_PRODUCTS;

        // verify if tables already exist or not
        $hasShopsTable = $this->db->tableExists($shopsTable);
        $hasProductsTable = $this->db->tableExists($productsTable);

        // create the shops table
        if (!$hasShopsTable)
        {
            $this->createTable($shopsTable, [
                'id' => $this->primaryKey(),
                // 'id' => $this->integer()->notNull(),
                'shopifyData' => $this->longText()->notNull(),
                'adminId' => $this->string()->notNull(),
                'name' => $this->string()->notNull(),
                'primaryDomain' => $this->string()->notNull(),
                'myshopifyDomain' => $this->string()->notNull(),
                'currencyCode' => $this->char(3)->notNull(),
                'plan' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'dateDeleted' => $this->dateTime()->null(),
                'uid' => $this->uid(),
                // 'PRIMARY KEY([[id]])',
            ]);

            // Create search indexes for searchable columns
            $this->createIndex(null, $shopsTable, 'adminId', false);
            $this->createIndex(null, $shopsTable, 'name', false);
            $this->createIndex(null, $shopsTable, 'primaryDomain', false);
            $this->createIndex(null, $shopsTable, 'myshopifyDomain', false);
        }

        // create the products table
        if (!$hasProductsTable)
        {
            $this->createTable($productsTable, [
                'id' => $this->integer()->notNull(),
                'shopId' => $this->integer()->notNull(),
                'shopifyData' => $this->longText()->notNull(),
                'adminId' => $this->string()->notNull(),
                'storefrontId' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'variantAdminId' => $this->string()->notNull(),
                'variantStorefrontId' => $this->string()->notNull(),
                'title' => $this->string()->notNull(),
                'description' => $this->mediumText()->null(),
                'isGiftCard' => $this->boolean()->notNull(),
                'productType' => $this->string()->null(),
                'vendor' => $this->string()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'dateDeleted' => $this->dateTime()->null(),
                'uid' => $this->uid(),
                'PRIMARY KEY([[id]])',
            ]);

            // Create search indexes for searchable columns
            $this->createIndex(null, $productsTable, 'adminId', false);
            $this->createIndex(null, $productsTable, 'storefrontId', false);
            $this->createIndex(null, $productsTable, 'handle', false);
            $this->createIndex(null, $productsTable, 'variantAdminId', false);
            $this->createIndex(null, $productsTable, 'variantStorefrontId', false);
            $this->createIndex(null, $productsTable, 'title', false);
            $this->createIndex(null, $productsTable, 'productType', false);
            $this->createIndex(null, $productsTable, 'vendor', false);

            // Setup foreign keys to keep tables in sync
            $this->addForeignKey(null, $productsTable, ['id'], $elementsTable, ['id'], 'CASCADE', null);
            $this->addForeignKey(null, $productsTable, ['shopId'], $shopsTable, ['id'], 'CASCADE', null);
        }

        if (!$hasShopsTable || !$hasProductsTable)
        {
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */

    public function safeDown()
    {
        $this->dropTableIfExists(Shopify::TABLE_SHOPIFY_PRODUCTS);
        $this->dropTableIfExists(Shopify::TABLE_SHOPIFY_SHOPS);

        Craft::$app->db->schema->refresh();

        return true;
    }
}