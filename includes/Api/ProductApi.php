<?php
/**
* @package  WooSocialshop
*/
namespace Socialshop\Api;
use Socialshop\Base\SsLogger;
use Socialshop\Base\SsTaxonomy;
use Socialshop\Base\SsProduct;
use Socialshop\Base\SsConfig;

class ProductApi extends BaseApi{
    protected $ssTaxonomy, $ssProduct, $ssConfig ;

    public function __construct(){
        parent::__construct(); 
        $this->ssTaxonomy  = new SsTaxonomy();
        $this->ssProduct   = new SsProduct();
        $this->ssConfig    = new SsConfig();
    }

    public function register(){        
        add_action( 'rest_api_init', array( $this , 'registerEndpoints' ));
    }

    /**
     * Register all products api
     * @since 1.0.0
     */
    public function registerEndpoints(){
        $this->registerEndpoint( '/products/(?P<id>\d+)',           'GET', 'getProduct' );
        $this->registerEndpoint( '/products',                       'GET', 'getProducts' );
        $this->registerEndpoint( '/products/count',                 'GET', 'countProducts' );
        $this->registerEndpoint( '/products-simplify',              'GET', 'getProductsSimplify' );
        $this->registerEndpoint( '/product/tags',                   'GET', 'getProductTags' );
        $this->registerEndpoint( '/product/metas',                  'GET', 'getMetas' );
        $this->registerEndpoint( '/product/attributes',             'GET', 'getAttributes' );
        $this->registerEndpoint( '/product/categories',             'GET', 'getProductCats' );
        $this->registerEndpoint( '/product/variants',               'GET', 'getProductVariants' );
        $this->registerEndpoint( '/product/variation-attributes',   'GET', 'getVariationAttributes' );
    }

    function countProducts(){
        $product_count = $this->ssProduct->count();
        return wp_send_json_success(['total'=>$product_count]);
    }

    /**
     * Get list products
     * @since 1.0.0
     * @param \WP_REST_Request $request
     * @return json
     */
    public function getProducts($request){
        $params = $request->get_params();

        $filters  = $this->ssProduct->parseParams( $params );
        $params['page']     = isset($filters['page']) ? $filters['page'] : 1;
        $params['paginate'] = false;
        $params['category'] = isset($filters['category']) ? $filters['category'] : [];
        $params['status']   = isset($filters['status']) ? $filters['status'] : [];
        $params['limit']    = isset($filters['limit']) ? $filters['limit'] : 50;
        if( empty($params['category']) && strtoupper($filters['type_get_product']) == 'SPECIFIC_COLLECTIONS' ){
            return wp_send_json_success([]);
        }

        $wc_products = wc_get_products($params);
        $results  = [];
        if( !empty($wc_products)){
            foreach ($wc_products as $key => $product) {
                $results[$key] = $this->ssProduct->convertData( $product, $filters );
            }
        }
        SsLogger::info('[Api][Products]'.json_encode([
                'total' => count($results),
                'params'=> $params
            ]));
        return wp_send_json_success($results);
    }

    /**
     * Get list products field: id, title, image_src
     * @since 1.0.0
     * @param \WP_REST_Request $request
     * @return json
     */
    public function getProductsSimplify($request){
        $filters = $request->get_params();
        $results = $this->ssProduct->getProductsSimplify($filters);
        SsLogger::info('[Api][Products]'.json_encode([
                'total' => count($results),
                'params'=> $filters
            ]));
        return wp_send_json_success($results);
    }

    /**
    * Get list products
     * @since 1.0.0
     * @param \WP_REST_Request $request
     * @return json
    */
    public function getProduct( $request ){
        $product_id = $request->get_param('id');
        if( !isset($product_id) )
            return wp_send_json_error(['Product Id is required']);

        $product = $this->ssProduct->getProduct($product_id);
        if( empty($product) ){
            return wp_send_json_error(['message' => 'Product not found']);
        }

        if( $product ){
            return wp_send_json_success( $product );
        }
        return wp_send_json_error([]);
    }
    
    /**
    * Get list products
     * @since 1.0.0
     * @return json
    */
    public function getMetas(){
        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = absint($_GET['id']);
        $wc_product = new \WC_Product( $product_id );
        return wp_send_json_success( $wc_product->get_meta_data());
    }

    /**
     * Get list product attributes
     * @since 1.0.0
     * @return json
     */
    public function getAttributes(){
        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = absint($_GET['id']);
        $wc_product = wc_get_product( $product_id );
        return wp_send_json_success( $wc_product->get_attributes());
    }

    /**
     * Get list product variations by product id
     * @since 1.0.0
     * @return json
     */
    public function getProductVariants(){

        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = absint($_GET['id']);
        $wc_product = new \WC_Product_Variable( $product_id );
        $product_variations = $wc_product->get_available_variations();
        return wp_send_json_success( $product_variations );
    }

    /**
     * Get list product variations by product id
     * @since 1.0.0
     * @return json
     */
    public function getVariationAttributes(){

        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = absint($_GET['id']);
        $wc_product = new \WC_Product_Variable( $product_id );
        $product_variations = $wc_product->get_variation_attributes();
        return wp_send_json_success( $product_variations );
    }

    /**
     * @description Get list product tags by product id
     * @param id is product id
     * @since 1.0.0
     * @return json
     */
    public function getProductTags(){

        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = sanitize_title($_GET['id']);
        $tags = wp_get_post_terms( $product_id, 'product_tag' );
        return wp_send_json_success( $tags );
    }

    /**
     * @description Get list product tags by product id
     * @param id is product id
     * @since 1.0.0
     * @return json
     */
    public function getProductCats(){
        if( !isset($_GET['id']) )
            return wp_send_json_error(['message' => 'Product Id is required']);

        $product_id = absint($_GET['id']);
        $tags = wp_get_post_terms( $product_id, 'product_cat' );
        return wp_send_json_success( $tags );
    }
}

