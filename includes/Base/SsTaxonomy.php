<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use WP_Term_Query;
use __ as Lodash;

class SsTaxonomy extends BaseController {

	public function getList(){
	    global $wpdb;
	    $results = $wpdb->get_results("SELECT DISTINCT taxonomy FROM {$wpdb->prefix}term_taxonomy",ARRAY_A);
	    if( empty($results)){
	        return [];
        }
	    return Lodash::flatten($results);
    }
	
	/**
	 * Get list taxonomy
	 * @param array $filter
	 * @since 1.0.0
     * @return array
	 */

    public function getTaxonomies( array $filter = array() ){
        $this->parseParams($filter );
        $term_query = new WP_Term_Query( $filter );
        $total_taxonomy = $this->countTaxonomies($filter);
        return [
            'terms' => $term_query->get_terms(),
            'paginate' => $this->formatPaging($total_taxonomy, $filter['number'], $filter['page'])
        ];
    }

	/**
	 * Get list taxonomy with recursive
	 * @param integer $parent_id
	 * @since 1.0.0
     * @return array Deep array
	 */

    public function getTaxonomiesRecursive( $results = [] , $parent_id = 0, $taxonomy = 'product_cat' ){
        // only 1 taxonomy
        $taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
        $terms = get_terms( $taxonomy, array(
            'parent'     => $parent_id,
            'hide_empty' => false,
        ) );

        $children = array();
        
        $shop_id = $this->getShopId();
        foreach( $terms as $term ) {
            $term->children = $this->getTaxonomiesRecursive( $taxonomy, $term->term_id );
            $children[ $term->term_id ]['name'] = $term->name;
            $children[ $term->term_id ]['id'] = $shop_id.'_'.$term->term_id;
            $children[ $term->term_id ]['parent_id'] = $shop_id.'_'.$term->parent;
            $children[ $term->term_id ]['children']  = $term->children;
        }

        return array_values($children);
    }

    /**
     * @param $filter
     * @since 1.0.0
     * @return array
     */
    public function countTaxonomies( array $filter = array() ){
        $this->parseParams($filter);
        unset($filter['offset']);
        unset($filter['limit']);
        $filter['fields'] = 'count';
        $term_query = new WP_Term_Query( $filter );
        return $term_query->get_terms();
    }

    /**
     * @param $total
     * @param $limit
     * @param $current_page
     * @return array
     */
    public function formatPaging( $total, $limit, $current_page ){
        if( $total == 0 ){
            return [];
        }

        $next = ( $current_page <= $total ) ? $current_page + 1 : 1;
        $previous = ( $current_page > 1 )   ? $current_page - 1 : 1;
        $last_page= ceil( $total / $limit );
        return [
            'total'        => $total,
            'last_page'    => $last_page,
            'current_page' => $current_page,
            'next'         => $next,
            'previous'     => $previous,
        ];
    }

    /**
     * Get term default fields
     * @return array
     */
    public function getDefaultFields(){
        $term_query = new \WP_Term_Query();
        return $term_query->query_var_defaults;
    }

    /**
     * @param array $filters
     * @return array
     */
	public function getProductCats( array $filters ){
        $filters['taxonomy'] = 'product_cat';
		return $this->getTaxonomies( $filters );
	}

	/**
	 * Retrieve a product's tags.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id       Post ID.
	 * @return string|false|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
	 */
	public function getTags( $id ){
		return $this->getTaxonomies($id,'product_tag');
	}

    /** Set user params and merge with query_var_defaults
     * @param array $filter
     * @return array
     */
    public function parseParams(array &$filter){
        $filter['page']   = isset($filter['page'])  && $filter['page'] > 0  ? absint($filter['page']) : 1;
        $filter['number'] = isset($filter['limit']) && $filter['limit'] > 0 ? absint($filter['limit']) : 10;
        $filter['offset'] = ( $filter['page'] - 1 ) * $filter['number'];
        $filter['taxonomy'] = 'product_cat';
        return $filter;
    }
}
