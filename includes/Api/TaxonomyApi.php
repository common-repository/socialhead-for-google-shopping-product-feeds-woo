<?php
/**
* @package  WooSocialshop
*/
namespace Socialshop\Api;
use Socialshop\Api\BaseApi;
use Socialshop\Base\SsTaxonomy;

class TaxonomyApi extends BaseApi{

    protected $ssTaxonomy;

    /**
     * TaxonomyApi constructor.
     */
    public function __construct()
    {
        $this->ssTaxonomy = new SsTaxonomy();
    }

    public function register(){
        add_action( 'rest_api_init', array( $this , 'registerEndpoints' ));
    }

    public function registerEndpoints(){
        $this->registerEndpoint( '/taxonomies', 'GET', 'getTaxonomies' );
        $this->registerEndpoint( '/taxonomies-recursive', 'GET', 'getTaxonomiesRecursive' );
        $this->registerEndpoint( '/default-fields', 'GET', 'getDefaultFields' );
        $this->registerEndpoint( '/product-cats', 'GET', 'getProductCats' );
        $this->registerEndpoint( '/product-cats/recursive', 'GET', 'getProductCatsRecursive' );
        $this->registerEndpoint( '/product-tags/recursive', 'GET', 'getProductTagsRecursive' );
        $this->registerEndpoint( '/product-tag/products', 'GET', 'getProductsByTags' );
        $this->registerEndpoint( '/product-cat/products', 'GET', 'getProducts' );
    }

    /**
     * Get list default fields in WP_Term_Query
     */
    function getDefaultFields(){
        $fields = $this->ssTaxonomy->getDefaultFields();
        return wp_send_json_success( $fields );
    }

    /**
     * Get list product categories
     * @param $request WP_Request
    */
    public function getProductCats($request){

        $params = $request->get_params();
        $product_cat = $this->ssTaxonomy->getProductCats( $params );
        if( is_wp_error($product_cat)){
            return wp_send_json_error(['message' => $product_cat->get_error_messages()] );
        }
        return wp_send_json_success($product_cat);
    }


    /**
     * Get list taxonomies
     * @param $request WP_Request
    */
    public function getTaxonomies($request){

        $params = $request->get_params();
        $product_cat = $this->ssTaxonomy->getTaxonomies( $params );
        if( is_wp_error($product_cat)){
            return wp_send_json_error(['message' => $product_cat->get_error_messages()] );
        }
        return wp_send_json_success($product_cat);
    }

    /**
     * Get list taxonomies with recursive
     * @param $request WP_Request
    */
    public function getTaxonomiesRecursive($request){
        $taxonomy = $request->get_param('taxonomy');
        if( !taxonomy_exists($taxonomy) ){
            return wp_send_json_error(['message' => 'The taxonomy name is required' ] );
        }
        $product_cat = $this->ssTaxonomy->getTaxonomiesRecursive([],0, $taxonomy );
        if( is_wp_error($product_cat)){
            return wp_send_json_error(['message' => $product_cat->get_error_messages()] );
        }
        return wp_send_json_success($product_cat);
    }

    /**
     * Get list product categories with recursive
    */
    public function getProductCatsRecursive(){
        $product_cat = $this->ssTaxonomy->getTaxonomiesRecursive( [],0,'product_cat');
        if( is_wp_error($product_cat)){
            return wp_send_json_error(['message' => $product_cat->get_error_messages()] );
        }
        return wp_send_json_success($product_cat);
    }

    /**
     * Get list product tags with recursive
    */
    public function getProductTagsRecursive(){
        $product_tags = $this->ssTaxonomy->getTaxonomiesRecursive( [],0,'product_tag');
        if( is_wp_error($product_tags)){
            return wp_send_json_error(['message' => $product_tags->get_error_messages()] );
        }
        return wp_send_json_success($product_tags);
    }

    /**
    * Get list products by product categories id
    * @param id is product category id ( demo = 16 )
    */
    public function getProducts(){
        if( !isset($_GET['category']) )
            return wp_send_json_error(['message' => 'Product Category Ids is required']);

        $cat_ids = explode(',', sanitize_title($_GET['category']) );

        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'include'    => $cat_ids
        ));

        if( empty($terms) ){
            return wp_send_json_success([]);
        }

        $args['category'] = [];
        foreach ($terms as $key => $term) {
            $args['category'] = $term->slug;
        }

        $wc_products = wc_get_products($args);
        $products    = [];
        foreach ($wc_products as $product) {
            $products[] = $product->get_data();
        }
        return wp_send_json_success($products);
    }

    /**
    * @description Get list products by product tags id
    * @param id is product category id ( demo = 16 )
    */
    public function getProductsByTags(){
        if( !isset($_GET['tag']) )
            return wp_send_json_error(['message' => 'Product Category Id is required']);

        $cat_ids = explode(',', sanitize_title($_GET['tag']) );
        

        $terms = get_terms(array(
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
            'include'    => $cat_ids
        ));

        if( empty($terms) ){
            return wp_send_json_success([]);
        }

        $args['tag'] = [];
        foreach ($terms as $key => $term) {
            $args['tag'] = $term->slug;
        }

        $wc_products = wc_get_products($args);
        $products    = [];
        foreach ($wc_products as $product) {
            $products[] = $product->get_data();
        }
        return wp_send_json_success($products);
    }
}

