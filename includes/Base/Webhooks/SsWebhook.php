<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base\Webhooks;

use Socialshop\Base\SsLogger;
use Socialshop\Base\SsMapping;
use Socialshop\Base\SsProduct;
use Socialshop\Base\SsRequest;
use Socialshop\Base\SsSetting;
use __ as Lodash;

class SsWebhook{

    protected $ssRequest, $ssProduct, $ssMapping, $ssSetting;

    public function __construct()
    {
        $this->ssRequest = new SsRequest();
        $this->ssProduct = new SsProduct();
        $this->ssMapping = new SsMapping();
        $this->ssSetting = new SsSetting();

    }

    /**  Register new Woocommerce Hook, trigger when product create, update, delete
     * @since  1.0.0
     * @return void
     */
    public function hookProductChange(){
        add_action('deleted_post', array($this, 'deleteProduct'), 5, 1);
        add_action('woocommerce_new_product', array($this, 'redirectCreate'), 5, 1);
        add_action('woocommerce_update_product', array($this, 'redirectUpdate'), 5, 1);
        add_action('trashed_post', array($this, 'redirectUpdate'), 5, 1);
    }


    /**  Validate before send webhook create
     * @since  1.0.0
     * @param  string $product_id
     * @return void
     */
    function redirectCreate( $product_id ){
        $product_converted = $this->ssProduct->getProduct( $product_id );
        $category_ids = isset($product_converted['category_ids']) ? $product_converted['category_ids'] : [];
        $validated = $this->validate($product_id, $category_ids);
        if( $validated == true ){
            SsLogger::info('[Webhook][Product][Create] ID:'.$product_id);
            $this->createProduct($product_converted);
        }
        SsLogger::info('[Webhook][Product] Skip ID:'.$product_id);
    }


    /**  Validate before send webhook update
     * @since  1.0.0
     * @param  string $product_id
     * @return void
     */
    function redirectUpdate( $product_id ){
        $product_converted = $this->ssProduct->getProduct( $product_id );
        $category_ids = isset($product_converted['category_ids']) ? $product_converted['category_ids'] : [];
        $validated = $this->validate($product_id, $category_ids );
        if( $validated == true ){
            $this->updateOrCreateProduct($product_converted);
        }else{
            $this->deleteProduct($product_id);
        }
    }

    /**  Check product is valid in setting
     * @since  1.0.0
     * @param  string $product_id
     * @param  array $product_cat_ids
     * @return boolean
     */
    function validate( $product_id, $product_cat_ids = [] ){
		if( get_post_type($product_id) != 'product' )
        	return false;

 		$setting = $this->ssSetting->get();
        if( empty($setting))
            return false;

        $collection_ids = [];
        $product_status = ['publish'];
        $type_get_product = 'ALL_PRODUCT';
        // Extract setting
        foreach ($setting as $setting) {
            if( $setting['setting_key'] == 'collection_ids' ){
                $collection_ids = json_decode($setting['setting_value'],true);
            }else if( $setting['setting_key'] == 'import_unpublished' && $setting['setting_value'] == true ){
                $product_status = ['publish','draft'];
            }else if( $setting['setting_key'] == 'type_get_product' && strtoupper($setting['setting_value']) == 'SPECIFIC_COLLECTIONS' ){
                $type_get_product = 'SPECIFIC_COLLECTIONS';
            }
        }

        if( $type_get_product == 'SPECIFIC_COLLECTIONS' &&
            ( empty($collection_ids) || Lodash::intersects($collection_ids,$product_cat_ids) == false )
        )
            return false;

        if( !in_array(get_post_status($product_id), $product_status) )
            return false;

        return true;
    }

    /**  Send webhook create product
     * @since  1.0.0
     * @param  array $product_converted
     * @return void
     */
    function createProduct( $product_converted  ) {
        $data     = [
                        'topic' => 'create',
                        'data'  => [
                            'type' => 'product',
                            'date' => date('Y-m-d h:i:s'),
                            'value'=> $product_converted
                        ]
                    ];
        SsLogger::info('[Webhook][Product][Create] ID: '.@$product_converted['id']);
        $this->ssRequest->post( $data );
    }


    /**  Send webhook update product
     * @since  1.0.0
     * @param  array $product_converted
     * @return void
     */
    function updateOrCreateProduct( $product_converted  ) {
        $data     = [
                        'topic' => 'update',
                        'data'  => [
                            'type' => 'product',
                            'date' => date('Y-m-d h:i:s'),
                            'value'=> $product_converted
                        ]
                    ];
        SsLogger::info('[Webhook][Product][Update] ID: '.@$product_converted['id']);
        $this->ssRequest->post( $data );
    }

    /**  Send webhook delete product
     * @since  1.0.0
     * @param  string $product_id
     * @return void
     */
    function deleteProduct($product_id  ) {
        $data     = [
                        'topic'=>'delete',
                        'data'=>[
                            'type' => 'product',
                            'date' => date('Y-m-d h:i:s'),
                            'value'=> [
                                'id' => $product_id
                            ]
                        ]
                    ];
        SsLogger::info('[Webhook][Product][Delete] ID: '.$product_id);
        $this->ssRequest->post( $data );
    }


}