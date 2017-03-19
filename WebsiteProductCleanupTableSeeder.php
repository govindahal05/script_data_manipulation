<?php

use Fdw\Cart\Models\Product;
use Fdw\Cart\Models\ProductVariant;
use Fdw\Core\Models\Media;
use Fdw\Cart\Models\ProductFeature;

class WebsiteProductCleanupTableSeeder extends Seeder
{
    private $cleanup_all = [
        // EBAY ---------------------------------------------------------------
        'ebay_call_log',
        'ebay_category',
        'ebay_orders',
        'ebay_order_products',
        'ebay_product',
        'ebay_queue',

        // CURRENCIES ---------------------------------------------------------
        'currencies_orders',
        'currencies_users',

        // DISCOUNTS ----------------------------------------------------------
        'discountable_user_group',
        'discountable_user',
        'discountable_product_category',
        'discountable_product',
        'discountable_oems',
        'discountable_brand',
        'discountable',
        'discount',

        // CART ---------------------------------------------------------------
        'cart_discount',
        'cart_item',
        'cart_item_type',
        'cart_order',
        'cart_order_item',
        'cart_quote',
        'cart_quote_item',
        'cart_wish_list',
        'cart',
    ];

    private $cleanup_warehouse_products = [

        // PRODUCTS -----------------------------------------------------------
        'product_association',
        'product_association_value',
        'product_attachment',
        'product_attribute',
        'product_bundle',
        'product_dimension',
        'product_enquiry',
        'product_feature',
        'product_image',
        'product_inventory',
        'product_price',
        'product_reviews',
        'product_variant',
    ];

    public function run()
    {
        $this->cleanup();
    }

    private function cleanup()
    {

        printf("\n=========Emptying Other Tables=====================");
        foreach ($this->cleanup_all as $table) {
            \DB::table($table)->delete();
        }

        \DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($this->cleanup_all as $table) {
            \DB::table($table)->truncate();
        }
        \DB::statement("SET FOREIGN_KEY_CHECKS = 1");
        printf("\n=========Deleted Other Tables=====================");

        $warehouse_id = 1;

        $product_id_for_given_warehouse = \Fdw\Core\Models\WarehouseItem::where(['itemable_type' => 'Fdw\Cart\Models\Product'])
            ->where(['warehouse_id' => $warehouse_id])
            ->lists('itemable_id');

        $product_id_for_other_warehouse = \Fdw\Core\Models\WarehouseItem::where(['itemable_type' => 'Fdw\Cart\Models\Product'])
            ->where('warehouse_id', '<>', $warehouse_id)
            ->lists('itemable_id');

        $product_ids = array_diff($product_id_for_given_warehouse, $product_id_for_other_warehouse);
        $media_ids = \Fdw\Cart\Models\ProductImage::whereIn('product_id', $product_ids)->lists('media_id');

        printf("\n=========Deleting Medias=====================");
        \DB::table('media')->whereIn('id', $media_ids)->delete();
        \DB::table('media')->whereIn('id', $media_ids)->truncate();

        foreach ($this->cleanup_warehouse_products as $table) {
            printf("\n=========Deleting $table=====================");
            \DB::table($table)->whereIn('product_id', $product_ids)->delete();
        }

        \DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($this->cleanup_warehouse_products as $table) {
            printf("\n=========Truncating $table=====================");
            \DB::table($table)->whereIn('product_id', $product_ids)->truncate();
        }
        \DB::statement("SET FOREIGN_KEY_CHECKS = 1");


        printf("\n=========Deleting and Truncating Product Table=====================");
        \DB::table('product')->whereIn('id', $product_ids)->delete();
        \DB::table('product')->whereIn('product_id', $product_ids)->truncate();



        printf("\n=========Deleting and Truncating Product Table=====================");
        \DB::table('meta')->whereIn('id', $product_ids)->delete();
        \DB::table('product')->whereIn('product_id', $product_ids)->truncate();


        printf("\n=========Deleting Meta =====================");
        \Fdw\Core\Models\Meta::where(['metable_type' => 'Fdw\Cart\Models\Product'])
            ->whereIn('metable_id', $product_ids)
            ->delete();

        printf("\n=========Deleting Warehouse =====================\n");
        \Fdw\Core\Models\WarehouseItem::where(['itemable_type' => 'Fdw\Cart\Models\Product'])
            ->whereIn('itemable_id', $product_ids)
            ->delete();
    }
}
