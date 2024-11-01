<?php
namespace Socialshop\Base\Woocommerce;

class SsProductData extends \WC_Product_Data_Store_CPT{

	/**
	* Get list woocommerce custom fields.
	* @since   1.0.0
	* @param
	* @return  array
	*/
	function getInternalMetaFields (){
		$extra_fields   = ['_children','_product_url','_button_text'];
		$exclude_fields = $this->internal_meta_keys ? $this->internal_meta_keys : [];
	    return array_merge($exclude_fields,$extra_fields);
	}
}