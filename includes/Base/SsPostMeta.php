<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use App\Repository\Base;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsConfig;
use Socialshop\Base\Woocommerce\SsProductData;
use Socialshop\Helpers\Common;

class SsPostMeta extends BaseController{

    protected $wsProductData, $ssConfig;
    protected $default_limit, $default_paginate;

    public function __construct()
    {
        $this->wsProductData = new SsProductData();
        $this->ssConfig      = new SsConfig();
        $this->default_limit = $this->getOptionLimit();
        $this->default_paginate = $this->ssConfig->getGlobal('paginate');
    }

    /**  Get all custom fields of products and exlude Woocommerce default fields
    * @since  1.0.0
    * @param  array $filters
    * @return array
    */
    public function getProductMetas( $filters = array() ){
        global $wpdb;
        $internal_fields = $this->wsProductData->getInternalMetaFields();


        $page   = !empty($filters['page'])  ? absint($filters['page'])   : 1;
        $limit  = !empty($filters['limit']) ? absint($filters['limit'])  : $this->getOptionLimit();
        $offset = ( $page - 1 ) * $limit;

        $sql = "SELECT DISTINCT meta.meta_key as 'key'
                    FROM {$wpdb->prefix}postmeta AS meta
                    INNER JOIN {$wpdb->prefix}posts AS post
                      ON post.ID = meta.post_id
                    INNER JOIN {$wpdb->prefix}term_relationships AS term
                      ON term.object_id = post_id
                    WHERE post_type = 'product'";

        if( !empty($internal_fields)){
            $exclude_fields  = '';
            // Convert array fields to string with single quote
            foreach ($internal_fields as $key => $internal_field) {
                $separator = ',';
                if( $key == ( count($internal_fields) - 1 ) ){
                    $separator = '';
                }
                $exclude_fields .= "'".$internal_field."'".$separator;
            }
            $sql .= "AND meta.meta_key NOT IN ( {$exclude_fields} )";
        }

        if ( isset($filters['keyword']) ) {
            $like  = '%' . $wpdb->esc_like( $filters['keyword'] ) . '%';
            $sql   .= $wpdb->prepare(" AND meta.meta_key LIKE %s ",$like);
        }

        $wpdb->get_results( $sql, ARRAY_A );
        $total   = $wpdb->num_rows;
        $sql .= "LIMIT {$limit} OFFSET {$offset}";
        $paginate = Common::formatPaging($page,$limit,$total);
        $metas = $wpdb->get_results( $sql, ARRAY_A );

        return [ 'data' => $metas , 'paginate' => $paginate ];

    }


    /**  Get list all attributes of all products
    * @since  1.0.0
    * @param  array $filters
    * @return array
    */
    public function getProductAttributes( $filters  = array() ){
        global $wpdb;
        $internal_fields = $this->wsProductData->getInternalMetaFields();
        $exclude_fields  = '';
        foreach ($internal_fields as $key => $internal_field) {
            $separator = ',';
            if( $key == ( count($internal_fields) - 1 ) ){
                $separator = '';
            }
            $exclude_fields .= "'".$internal_field."'".$separator;
        }
		
        $ssProductAttribute = new SsProductAttribute();
        $table_name = $ssProductAttribute->getTableName();
        $sql = "SELECT attribute_name FROM ".$table_name;

        if ( isset($filters['keyword']) ) {
            $like  = '%' . $wpdb->esc_like( $filters['keyword'] ) . '%';
            $sql   .= $wpdb->prepare(" AND meta_key LIKE %s ",$like);
        }
        
       
        $wpdb->get_results( $sql, ARRAY_A );
        $total   = $wpdb->num_rows;

        if( $total === 0 )
            return ['data' => [],'paginate' => []];

        $page   = !empty($filters['page'])  ? absint($filters['page'])   : 1;
        $limit  = !empty($filters['limit']) ? absint($filters['limit'])  : $this->getOptionLimit();
        $offset = ( $page - 1 ) * $limit;

        $paginate = Common::formatPaging($page,$limit,$total);
	    $sql .= " LIMIT {$limit} OFFSET {$offset}";
	    $results['data']     = $wpdb->get_results( $sql  , ARRAY_A );
	    $results['paginate'] = $paginate;
	    return $results;

    }


    /**  Get list all attributes of all products
    * @since  1.0.0
    * @param  array $filters
    * @return array
    */
    public function getCustomAttributes( $filters  = array() ){
        global $wpdb;
        $internal_fields = $this->wsProductData->getInternalMetaFields();
        $exclude_fields  = '';
        foreach ($internal_fields as $key => $internal_field) {
            $separator = ',';
            if( $key == ( count($internal_fields) - 1 ) ){
                $separator = '';
            }
            $exclude_fields .= "'".$internal_field."'".$separator;
        }

        $sql = "SELECT DISTINCT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_product_attributes' ";

        if ( isset($filters['keyword']) ) {
            $like  = '%' . $wpdb->esc_like( $filters['keyword'] ) . '%';
            $sql   .= $wpdb->prepare(" AND meta_key LIKE %s ",$like);
        }

        $wpdb->get_results( $sql, ARRAY_A );
        $total   = $wpdb->num_rows;

        if( $total === 0 )
            return ['data' => [],'paginate' => []];

        $page   = !empty($filters['page'])  ? absint($filters['page'])   : 1;
        $limit  = !empty($filters['limit']) ? absint($filters['limit'])  : $this->getOptionLimit();
        $offset = ( $page - 1 ) * $limit;

        $paginate = Common::formatPaging($page,$limit,$total);

        $sql .= "LIMIT {$limit} OFFSET {$offset}";

        $results['data']     = $wpdb->get_results( $sql  , ARRAY_A );
        $results['paginate'] = $paginate;
        return $results;

    }

    /**  Format request
    * @since  1.0.0
    * @param  array $request
    * @return array
    */
    public function parseParam($request){
        $page = isset($request['page']) &&
                is_numeric($request['page']) &&
                $request['page'] > 0 ?
                absint($request['page']) : 1;

        $filter['limit']   = isset($request['limit']) &&
                                is_numeric($request['limit']) &&
                                $request['limit'] > 0  ?
	                            absint($request['limit']) : $this->default_limit;

        $filter['offset']  = ( $page - 1) * $filter['limit'];
        $filter['keyword'] = wc_clean(wp_unslash(@$request['keyword']));
        return $filter;
    }


}
