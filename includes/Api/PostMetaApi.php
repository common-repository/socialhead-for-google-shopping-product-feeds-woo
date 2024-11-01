<?php
/**
 * @package  WooSocialshop
 */
namespace Socialshop\Api;
use Socialshop\Base\SsPostMeta;
use __ as Lodash;
use Socialshop\Base\SsProductAttribute;

class PostMetaApi extends BaseApi
{
    protected $ssPostMeta;

    public function __construct()
    {
        parent::__construct();
        $this->ssPostMeta = new SsPostMeta();
    }

    public function register()
    {
        add_action('rest_api_init', array($this, 'registerEndpoints'));
    }

    /**
     * 1, Lấy danh sách variants của từng product
     * 2, Lấy toàn bộ custom fields
     */
    public function registerEndpoints()
    {
        $this->registerEndpoint('/product-metas',      'GET', 'getProductMetas');
        $this->registerEndpoint('/product-attributes', 'GET', 'getProductAttributes');
    }

    public function getProductMetas($request){
        $filters    = $request->get_params();
        $postmetas  = $this->ssPostMeta->getProductMetas($filters);
        $key_metas = [];
        if( isset($postmetas['data']) &&  count($postmetas['data']) > 0 ){
            foreach ($postmetas['data'] as $key => $item) {
                $key_metas['meta_data.'.$item['key']] = $item['key'];
            }
        }
        return wp_send_json_success( [
            'data'     => $key_metas,
            'paginate' => $postmetas['paginate']
        ] );
    }

    /**
     * @param $request
     */
    public function getProductAttributes($request){
		
	    $ssProductAttribute =  new SsProductAttribute();
	    $ssProductAttribute->syncProductAttributes();
    	
        $filters    = $request->get_params();
        $attributes  = $this->ssPostMeta->getProductAttributes($filters);
        $key_attributes = [];
        if( isset($attributes['data']) &&  count($attributes['data']) > 0 ){
        	$attribute_data = Lodash::flatten($attributes['data']);
            foreach ( $attribute_data as $key => $attribute_name) {
                $key_attributes['variants.attributes.'.$attribute_name] = $attribute_name;
            }
        }
        return wp_send_json_success( [
            'data' => $key_attributes,
            'paginate' => $attributes['paginate']
        ] );
    }

}
