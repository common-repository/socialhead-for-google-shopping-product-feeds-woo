<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use Socialshop\Helpers\Common;
use __ as Lodash;

class SsProduct extends BaseController {

	protected $fields = [];
	protected $ssConfig, $ssTaxonomy, $ssMapping, $ssSetting, $baseController;
	protected $date_format;
	protected $field_removed;

	public function __construct(){
		$this->ssTaxonomy = new SsTaxonomy();
		$this->ssConfig   = new SsConfig();
		$this->date_format   = $this->ssConfig->readKey('Global','date_format');
		$this->field_removed = $this->ssConfig->readKey('Product','removed');
		$this->ssMapping  = new SsMapping();
		$this->ssSetting  = new SsSetting();
		$this->baseController  = new BaseController();
    }

	function setArray(&$array, $keys, $value) {
		$keys = explode(".", $keys);
		$current = &$array;
		foreach($keys as $key) {
			$current = &$current[$key];
		}
		$current = $value;
	}
	
	function getImageSrc( $id, $size = 'large' ){
		$thumbnail_id = get_post_thumbnail_id( $id );
		if( !empty( $thumbnail_id ) ){
			$img = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size );
	    	return $img[0];
		}
		return null;
	}

	function getThumbnailSrc( $id = null , $size = 'large'){
	    $img = wp_get_attachment_image_src( $id, $size );
	    if( !empty($img[0]) ){
	    	return $img[0];
	    }
	    return null;
	}


	function getThumbnailsSrc( array $ids, $size = 'large'){
		if( empty($ids))
			return [];
		$thumbnails = [];
		foreach ($ids as $key => $id) {
			$thumbnails[$key] = $this->getThumbnailSrc( $id );
		}
	    return $thumbnails;
	}

	function getPlaceholderImgId(){
		return get_option( 'woocommerce_placeholder_image', 0 );
	}

    /**
     * Get WC Product by id
     * @since 1.0.0
     * @param array $filters
     * @return array
     */
	public function getProductsSimplify( $filters = array() ){
	    global $wpdb;
        $page   = !empty($filters['page'])      ? absint($filters['page'])  : 1;
        $orderby= !empty($filters['orderby'])   ? wp_unslash($filters['orderby'])       : 'rand';
		$order  = in_array(strtoupper($filters['order']), ['ASC','DESC'])  ? wp_unslash($filters['order'])   : 'DESC';
		$limit  = $filters['limit']  = !empty($filters['limit'])     ? absint($filters['limit']) : $this->getOptionLimit();
        $offset = ( $page - 1 ) * $filters['limit'];
        $setting= $this->parseParams( $filters );
        $product_status = "'".implode('\',\'',$setting['status'])."'";

        $sql = " SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
				LEFT JOIN $wpdb->term_relationships as term_relations ON
				(posts.ID = term_relations.object_id)
				LEFT JOIN $wpdb->term_taxonomy as term_taxonomy ON
				(term_relations.term_taxonomy_id = term_taxonomy.term_taxonomy_id)
				WHERE posts.post_type = 'product'
				AND posts.post_status in (".$product_status.")
				AND term_taxonomy.taxonomy = 'product_cat'  ";

		$setting_collection_ids = [];
        if( isset($filters['collection_ids'])){
	        $setting_collection_ids = $filters['collection_ids'];
        }else  if( !empty($setting['collection_ids'])){
	        $setting_collection_ids = is_array($setting['collection_ids']) ? $setting['collection_ids'] : json_decode($setting['collection_ids'],true);
        }

		if( !empty( $setting_collection_ids ) && is_array($setting_collection_ids) && count($setting_collection_ids) > 0 ){
			foreach ( $setting_collection_ids as $parent_category_id ) {
				$category_ids[] = $parse_category_id = Common::parseObjectId($parent_category_id);
				$ancestors = get_ancestors( $parse_category_id,'product_cat', 'taxonomy');
				$category_ids = wp_parse_args($category_ids, $ancestors);
			}
			$str_category_ids = implode(',',$category_ids);
			$sql .= " AND term_taxonomy.term_id in (".$str_category_ids.") ";
		}

		if ( isset($filters['keyword']) ) {
            $like  = '%' . $wpdb->esc_like( $filters['keyword'] ) . '%';
            $sql   .= $wpdb->prepare(" AND posts.post_title LIKE %s ",$like);
        }

        $wpdb->get_results( $sql, ARRAY_A );
        $total    = $wpdb->num_rows;
        $paginate = Common::formatPaging($page,$limit,$total);

        if( strtoupper($orderby) == 'RAND'){
	        $sql .= " ORDER BY RAND() ".$order." LIMIT {$limit} OFFSET {$offset} ";
        }else{
	        $sql .= " ORDER BY ".$orderby." ".$order." LIMIT {$limit} OFFSET {$offset} ";
        }

        $posts = $wpdb->get_results( $sql, ARRAY_A );
        $results = [ 'data' => [], 'paginate' => $paginate ];

        $shopId = $this->getShopId();
        if( !empty($posts) ){
            foreach ($posts as $key => $post_item) {
                $results['data'][$key]['id']   = $shopId.'_'.$post_item['ID'];
                $results['data'][$key]['name'] = $post_item['post_title'];
                $results['data'][$key]['image_src'] = $this->getImageSrc($post_item['ID'],'thumbnail');
            }
        }

        return $results;

	}

	/**
	* Get WC Product by id
	* @since 1.0.0
	* @param $id is product id
	* @return array
	*/
	function getProduct( $id ){
		$wc_product = wc_get_product($id);
		
		if( empty($wc_product) )
			return [];
		
		switch (  $wc_product->get_type()) {
			case 'simple':
				return $this->getProductSimple($wc_product);
				break;

			case 'variable':
				return $this->getProductVariable($wc_product);
				break;

			case 'variation':
				return $this->getProductVariation($wc_product);
				break;

			default:
                return $this->getProductSimple($wc_product);
        }
	}

	/**
	* Get WC Product Simple by id
	* @since   1.0.0
	* @param   $wc_product WC Product 
	* @return  array
	*/
	function getProductSimple ( $wc_product ){
		if( empty($wc_product) )
			return [];

		return $this->convertData( $wc_product );
	}

	/**
	* Get WC Product Simple by id
	* @since   1.0.0
	* @param   $wc_product WC Product
	* @return  array
	*/
	function getProductVariable( $wc_product ){
		if( empty($wc_product) )
			return [];

		return $this->convertData( $wc_product );
	}

	/**
	* Get WC Product Variation by id
	* @since   1.0.0
	* @param   $wc_product WC Product
	* @return  array
	*/
	function getProductVariation ( $wc_product ){
		if( empty($wc_product) )
			return [];

		return $this->convertVariationData( $wc_product );
	}

	/**
	* Get and append attributes for WC Product
	* @since 1.0.0
	* @param WC_Product $wc_product is WC_Product
	* @param array $filters
	* @return array
	*/
	function convertData( $wc_product, $filters = [] ){
		$include_variant = $this->ssSetting->getByKey('include_variant');
        $include_variant_value = @$include_variant['setting_value'];

		$default_config = $this->ssConfig->readKey('Product','default', null );

		$product_id	  = $wc_product->get_id();
		$product_data = $wc_product->get_data();
		$product_data['product_type']= $wc_product->get_type();
		$product_data['image_url']   = $this->getThumbnailSrc( $wc_product->get_image_id() );
		$product_data['gallery_image_urls'] = $this->getThumbnailsSrc( $wc_product->get_gallery_image_ids() );

		$product_data['variant_url'] = $product_data['product_url'] = get_permalink($product_id);
		$product_data['parent_id'] 	 = $wc_product->get_parent_id();
		$product_data['name'] 		 = $wc_product->get_name();
		$product_data['weight'] 	 = $wc_product->get_weight();
		$product_data['weight_unit'] = $default_config['weight_unit'];
		$product_data['currency'] 	 = $default_config['currency'];
		$product_data['hide_out_of_stock_items'] = $default_config['hide_out_of_stock_items'] == 'yes' ? true : false;
		$product_data['downloads']   = $this->parseDownloads($wc_product);
		$product_data['attributes']  = $this->parseAttributes($wc_product);
		$product_data['meta_data']    = $this->parseMeta($wc_product);
		$product_data['product_tags'] = $this->parseProductTags($wc_product);
		$product_data['stock_status'] = $this->getStockStatus($product_data['stock_status']);
		//Mapping fields from Woo and App
		$product_mapping  = $this->mapping($product_data);
		if( $product_data['product_type'] != 'variable' ){
        	$product_data['url']      = $product_data['product_url'];
			$product_data['variants'] = $product_data;
			$product_mapping  = $this->mapping($product_data);
			$product_variant  = $product_mapping['variants'];
			unset($product_mapping['variants']);
            $product_mapping['variants'][0] = $product_variant;
            $product_simple_attribute = @$product_mapping['variants'][0]['attributes'];
            $product_mapping['variants'][0]['name'] = $this->parseVariantName( $product_simple_attribute );
		}

		// Get list variations from main product
		$wc_children = $wc_product->get_children();
		if( $product_data['product_type'] == 'variable' ){
		    if( !is_array($wc_children) || empty($wc_children)  ){
		        return [];
            }
			foreach ($wc_children as $key => $children_id) {
				$parent_data['hide_out_of_stock_items'] = $product_data['hide_out_of_stock_items'];
				$parent_data['product_url']   = $product_data['product_url'];
				$parent_data['purchase_note'] = $product_data['purchase_note'];
				$parent_data['weight_unit']   = $product_data['weight_unit'];
				$product_mapping['variants'][$key] = $this->convertVariationData($children_id, $parent_data);
				$variant_attribute  = $product_mapping['variants'][$key]['attributes'];
				$product_mapping['variants'][$key]['name'] = $this->parseVariantName( $variant_attribute );
				if( strtoupper($include_variant_value) == 'FIRST_VARIANT' ){
					break;
				}
			}
		}
        // Validate by setting
        $product_mapping['category_ids'] = $this->parseCategoryIds($wc_product);
        $product_mapping['status'] = get_post_status($product_id);
		return $product_mapping;
	}

	/**
	* Get and append attributes for WC_Product_Variation
	* @since 1.0.0
     * @param integer $product_id
	* @param  array $parent_data
	* @return array
	*/
	function convertVariationData( $product_id, $parent_data = [] ){
		$wc_product_variation = new \WC_Product_Variation($product_id);
		$product_data = $wc_product_variation->get_data();
		$product_data['url'] = $wc_product_variation->get_permalink();
		$product_data['image_url'] = $this->getThumbnailSrc( $wc_product_variation->get_image_id());
		$product_data['gallery_image_urls'] = $this->getThumbnailsSrc( $wc_product_variation->get_gallery_image_ids() );
		$product_data['product_type']= $wc_product_variation->get_type();
		$product_data['id'] 	 	 = $wc_product_variation->get_id();
		$product_data['parent_id']   = $wc_product_variation->get_parent_id();
		$product_data['sku']         = $wc_product_variation->get_sku();
		$product_data['variant_id']  = $wc_product_variation->get_id();
		$product_data['weight']      = $wc_product_variation->get_weight();
		$product_data['stock_quantity'] = $wc_product_variation->get_stock_quantity();
		$product_data['attributes']  = $this->parseAttributes($wc_product_variation);
        $product_data['name']         = get_the_title( $product_id );
        $product_data['meta_data']    = $this->parseMeta($wc_product_variation);
        $product_data['product_tags'] = $this->parseProductTags($wc_product_variation);
		$product_data 	 = array_merge($product_data, $parent_data);
		// //Mapping fields from Woo and App
		return $this->mappingVariant($product_data);
	}

	/**
	* Parse request data to mapping with filter from app
	* @since   1.0.0
	* @param   array $params
	* @return  array
	*/
	function parseParams ( array $params = array() ){
		$sync_setting = $this->ssSetting->get();
		$filters = [];
		foreach ($sync_setting as $key => $setting_field) {
			$setting_key = strtolower($setting_field['setting_key']);
			$filters[ $setting_key ] = wp_unslash($setting_field['setting_value']);
		}

        if( isset($filters['import_unpublished']) && $filters['import_unpublished'] != true ){
            $filters['status'] = array( 'publish' );
        }else{
	        $filters['status'] = array( 'publish','draft','private','pending' );
        }

        // Priority collection_ids from request $params
		$collection_ids = [];
		if( isset($params['collection_ids']) && !empty($params['collection_ids']) ){

			$collection_ids = json_decode($params['collection_ids'],true);
		}else if( isset($filters['type_get_product']) &&
		          strtoupper($filters['type_get_product']) == 'SPECIFIC_COLLECTIONS' &&
		          !empty($filters['collection_ids']) ){
			$collection_ids = json_decode($filters['collection_ids'],true);
		}

		if( is_array( $collection_ids ) && count($collection_ids) > 0 ){
			$filters['category'] = [];
			$filters['category_ids'] = $collection_ids;
			foreach ($collection_ids as $collection_id) {
				$explode_collection_ids = explode('_',$collection_id);
				$cat_id = isset($explode_collection_ids[1]) ? $explode_collection_ids[1] : $collection_id;
				$product_cat = get_term($cat_id,'product_cat');
				if( empty( $product_cat ) )
					continue;

				$filters['category'][] = $product_cat->slug;
			}
		}
        $filters['page']     = isset($params['page'])     ? absint($params['page']) : 1;
        $filters['paginate'] = isset($params['paginate']) ? $params['paginate']     : true;
		$filters['orderby'] = !empty($filters['orderby']) ? $filters['orderby']     : 'date';
		$filters['order']   = in_array(strtoupper($filters['order']), ['ASC','DESC']) ? $filters['order']   : 'DESC';
		return $filters;

	}

	/**
	* Get protected data from WC_Product_Download
	* @since   1.0.0
	* @param   WC_Product $wc_product
	* @return  array
	*/
	function parseDownloads ($wc_product){
		$result = [];
		$wc_downloads = $wc_product->get_downloads();

		// Get list attributes protected
		if( is_array($wc_downloads) ){
			foreach ($wc_downloads as $download_key => $downloads) {
				if( is_object($downloads)){
					$result[$download_key] = $downloads->get_data();
				}
				else{
					$result[$download_key] = $downloads;
				}
			}
		}
		return $result;
	}

	/**
	* Get protected data from WC_Product_Attribute
	* @since   1.0.0
	* @param   WC_Product $wc_product
	* @return  array
	*/
	function parseAttributes ($wc_product){
		$result = [];
		$wc_attributes = $wc_product->get_attributes();
		// Get list attributes protected
		if( is_array($wc_attributes) ){
			foreach ($wc_attributes as $attr_key => $attributes) {
				if( is_object($attributes)){
					$attr_terms     = $attributes->get_terms();
					$attribute_value = [];
					if( !empty($attr_terms) ){
						foreach( $attr_terms as $term ){
							$attribute_value[] = $term->name;
						}
					}
					$result[$this->convertAttributeKey($attr_key)] = join(', ',$attribute_value);
				}
				else{
					$attr_term = get_term_by( 'slug', $attributes, $attr_key );
					if( !empty( $attr_term ) && isset($attr_term->name) ){
						$attributes = $attr_term->name;
					}
					$result[$this->convertAttributeKey($attr_key)] = $attributes;
				}
			}
		}
		return $result;
	}

	/**
	 * @since  1.0.0
	 * @param  WC_Product $wc_product
	 * @return array
	 */
    function parseCategoryIds( $wc_product ){
        $shop_id         = $this->getShopId();
        $category_ids    = $wc_product->get_category_ids();
        if( empty($category_ids) )
            return [];

        $results['category_ids'] = $results['category_parent_ids'] = [];
        foreach ($category_ids as $category_id) {
            $results['category_ids'][] = $shop_id.'_'.$category_id;
            $ancestors = get_ancestors($category_id,'product_cat', 'taxonomy');
            if( empty($ancestors))
                continue;

            foreach ($ancestors as $ancestor) {
                $results['category_ids'][] = $shop_id.'_'.$ancestor;
            }
        }
        return $results['category_ids'];
    }

	public function convertAttributeKey( $attribute_key ){
		$explode_key = explode('pa_',$attribute_key);
		if( count($explode_key) == 2 ){
			$attribute_key = $explode_key[1];
		}else{
			$attribute_key = $explode_key[0];
		}
		return $attribute_key;
	}

    /**
     * Get protected data from WC_Product_Download
     * @since   1.0.0
     * @param   WC_Product $wc_product
     * @return  array
     */
    function parseProductTags ($wc_product){
        $result = [];
        $product_tag_ids = $wc_product->get_tag_ids();

        // Get list attributes protected
        if( is_array($product_tag_ids) ){
            foreach ($product_tag_ids as $tag_key => $tag_id) {
                if( term_exists($tag_id,'product_tag')) {
                    $tag_item = get_term($tag_id,'product_tag');
                    $result[$tag_key] = $tag_item->name;
                }
            }
        }
        return $result;
    }

    function getStockStatus( $key = null ){
        if( empty($key))
            return null;

        $status_options = wc_get_product_stock_status_options();
        return $status_options[$key];
    }

	/**
	* Get protected post meta from WC_Product_Attribute
	* @since   1.0.0
	* @param   WC_Product $wc_product
	* @return  array
	*/
	function parseMeta($wc_product){
		$result    = [];
		$meta_data = $wc_product->get_meta_data();
		// Get list meta protected
        foreach ($meta_data as $meta_datum) {
            $meta_item = $meta_datum->get_data();
            if( empty($meta_item['key']) )
                continue;

            $result[$meta_item['key']] = $meta_item['value'];
        }
		return $result;
	}
	
	
	/**  Auto merge variant name with variant attribute by dash
	 * @since  1.1.0
	 * @param  string $name
	 * @param  array $attributes
	 * @return string
	 */
	function parseVariantName( $attributes ){
		if( empty($attributes))
			return null;

		$filter_attributes = [];
		foreach ( $attributes as $attribute ) {
			if( empty($attribute))
				continue;

			$filter_attributes[] = $attribute;
		}
		return implode(' - ', $filter_attributes );
	}

	function mapping ( $product_data ){

		$mapping_config = $this->ssMapping->get();
		if( empty($mapping_config) )
			return [];

		$results = [];

		foreach ($mapping_config as $mapping) {
			if( empty( $mapping['mapping_value'] )  ){
				continue;
			}

			if( Lodash::has( $product_data, $mapping['mapping_value'] ) ){
				$value = Lodash::get( $product_data, $mapping['mapping_value'] );
				$new_arr = Lodash::set( [] , $mapping['mapping_value'], $value );
				$results = Lodash::merge($new_arr, $results);
			}

		}

		return Lodash::sortKeys($results);
	}

	function mappingVariant( $product_data ){

		$mapping_config = $this->ssMapping->get();
		if( empty($mapping_config) )
			return [];

		$results = [];
		foreach ($mapping_config as $mapping) {
			if( empty( $mapping['mapping_value'] )  ){
				continue;
			}

			$variant_key = str_replace('variants.','',$mapping['mapping_value']);
			if( $variant_key == $mapping['mapping_value'] )
				continue;

			if( Lodash::has( $product_data, $variant_key ) ){
				$value = Lodash::get( $product_data, $variant_key );
				$new_arr = Lodash::set( [] , $variant_key, $value );
				$results = Lodash::merge($new_arr, $results);
			}
		}

		return Lodash::sortKeys($results);
	}


	/**  Count total products by setting
	 * @since  1.0.0
	 * @return number
	 */
	public function count(){

		$setting_param = $this->parseParams();

		$product_status= isset( $setting_param['status'] )   ? $setting_param['status'] : ['publish'];
		$category_slugs= isset( $setting_param['category'] ) ? $setting_param['category'] : [];
		$args = array(
			'status' => $product_status,
			'category' => $category_slugs,
			'paginate' => true,
			'limit'  => 1,
			'return' => 'ids',
		);
		$product_query = wc_get_products($args);
		return $product_query->total;
	}


}