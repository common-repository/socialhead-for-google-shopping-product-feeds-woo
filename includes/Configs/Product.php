<?php

return [
	'removed' => [
	],
	'type_get_product' => [
		'ALL_PRODUCT',
		'SPECIFIC_COLLECTIONS'
	],
	'include_variant' => [
		'FIRST_VARIANT',
		'ALL_VARIANT'
	],
	'woocommerce_custom_fields' => [
		'_visibility', '_sku', '_price', '_regular_price', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_to', 'total_sales', '_tax_status', '_tax_class', '_manage_stock', '_stock', '_stock_status', '_backorders', '_low_stock_amount', '_sold_individually', '_weight', '_length', '_width', '_height', '_upsell_ids', '_crosssell_ids', '_purchase_note', '_default_attributes', '_product_attributes', '_virtual', '_downloadable', '_download_limit', '_download_expiry', '_featured', '_downloadable_files', '_wc_rating_count', '_wc_average_rating', '_wc_review_count', '_variation_description', '_thumbnail_id', '_file_paths', '_product_image_gallery', '_product_version', '_wp_old_slug', '_edit_last', '_edit_lock'
	],
	'default' => [
		'currency'    => get_option('woocommerce_currency'),
		'weight_unit' => get_option('woocommerce_weight_unit'),
		'hide_out_of_stock_items' => get_option('woocommerce_hide_out_of_stock_items'),
		'type_get_product'=> 'ALL_PRODUCT',
		'include_variant' => 'FIRST_VARIANT',
	],
	'mapping' => [
        'variant_id' 		 	=> 'variants.id',
        'product_id'			=> 'id',
        'product_title' 		=> 'name',
        'variant_title' 		=> 'variants.name',
        'product_description' 	=> 'description',
        'variant_price' 		=> 'variants.sale_price',
        'variant_compare_at_price' => 'variants.regular_price',
        'variant_shipping_weight'  => 'variants.weight',
        'variant_shipping_weight_unit' => 'variants.weight_unit',
        'product_product_url'  	=> 'product_url',
        'variant_variant_url'  	=> 'variants.url',
        'product_image_url'    	=> 'image_url',
        'variant_image_url'    	=> 'variants.image_url',
        'variant_additional_image_url' => 'variants.gallery_image_urls',
        'variant_sku' 					=> 'variants.sku',
        'variant_inventory_quantity' 	=> 'variants.stock_quantity',
        'product_currency' 				=> 'currency',
        'availability'                  => 'variants.stock_status',
        'variant_inventory_management' 	=> 'variants.manage_stock',
	]
];