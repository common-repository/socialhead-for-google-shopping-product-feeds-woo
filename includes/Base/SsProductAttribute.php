<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use App\Repository\Base;

class SsProductAttribute extends BaseController {

    protected $app_id, $ssPostMeta;

    public function __construct()
    {
        parent::__construct();
        $this->ssPostMeta = new SsPostMeta();
    }

    public function getTableName(){
        global $wpdb;
        return $wpdb->prefix.$this->app_id."_product_attribute";
    }

    public function get(){
        global $wpdb;
        $table   = $this->getTableName();
        $results = $wpdb->get_results('SELECT * FROM '.$table,ARRAY_A);
        if( empty($results)){
            return [];
        }
        return $results;
    }

    /**  Get sync setting by key
     * @since  1.0.0
     * @param  string $setting_key
     * @return array
     */
    public function getByKey($attribute_name){
        global $wpdb;
        $table  = $this->getTableName();
        $sql    = 'SELECT * FROM '.$table.' WHERE ';
        $sql   .= $wpdb->prepare(" attribute_name = %s ",$attribute_name);
        $results= $wpdb->get_row( $sql ,ARRAY_A);
        if( empty($results)){
            return [];
        }
        return $results;
    }


    /**  Create table setting
    * @since  1.0.0
    * @return void
    */

   public function createTable() {
        global $wpdb;
        $collate = $wpdb->get_charset_collate();

        $table_name = $this->getTableName();

        $sql = "CREATE TABLE {$table_name} (
                    attribute_id INTEGER,
                    attribute_name VARCHAR(100) NOT NULL,
                    attribute_label VARCHAR(100) NOT NULL,
                    PRIMARY KEY (attribute_name)
                ) $collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**  Init data for mapping table
    * @since  1.0.0
    * @return void
    */
    public function syncProductAttributes(){
        // Get attribute defined attribute_
        $filter['page']  = 1;
        $filter['limit'] = 50;
        do{
            $custom_attributes  = $this->ssPostMeta->getProductAttributes( $filter );
            foreach ($custom_attributes['data'] as $key => $item) {
	            if( empty($meta_key))
	            	continue;
	            
                $meta_key = explode('pa_', $item['meta_key'] );
                if( count($meta_key) == 2  ){
                    $meta_key = $meta_key[1];
                }else{
                    $meta_key = explode('attribute_', $item['meta_key'] );
                    if( count($meta_key) == 2  ){
                        $meta_key = $meta_key[1];
                    }
                }
                $this->replaceDB( $meta_key , $meta_key );
            }
            $filter['page']++;
        }while(  !isset($custom_attributes['data']) ||  count($custom_attributes['data']) > 0   );

        // Get attribute defined _product_attributes
        $filter['page']  = 1;
        $filter['limit'] = 50;
        do{
            $custom_attributes  = $this->ssPostMeta->getCustomAttributes( $filter );
            foreach ($custom_attributes['data'] as $key => $item) {
                $meta_value = unserialize($item['meta_value']);
                foreach ($meta_value as $meta_key => $meta_item) {
	                if( !empty($meta_key)){
                        $convert_meta_key = explode('pa_', $meta_key );
                        if( count($convert_meta_key ) == 2  ){
                            $meta_key = $convert_meta_key [1];
                        }
		                $this->replaceDB($meta_key,$meta_item['name']);
	                }
                }
            }
            $filter['page']++;
        }while(  !isset($custom_attributes['data']) ||  count($custom_attributes['data']) > 0   );

        $attribute_taxonomies = wc_get_attribute_taxonomies();
        if( count($attribute_taxonomies)){
	        foreach ($attribute_taxonomies as $attribute_taxonomy) {
		        $this->replaceDB( $attribute_taxonomy->attribute_name ,
			        $attribute_taxonomy->attribute_label,
			        $attribute_taxonomy->attribute_id );
	        }
        }
        return true;
    }

    public function replaceDB($key,$value,$id = null){
        global $wpdb;
        $table_name = $this->getTableName();
        if( is_null($value) )
            return false;

        if( is_array($value) ){
            $value = json_encode($value);
        }
        return $wpdb->replace(
            $table_name,
            array(
                'attribute_name'  => $key,
                'attribute_label' => $value,
                'attribute_id'    => $id,
            ),
            array(
                '%s',
                '%s',
                '%s',
            )
        );
    }
    public function create($key,$value,$id = null){
        global $wpdb;
        $table_name = $this->getTableName();
        if( is_null($value) )
            return false;

        if( is_array($value) ){
            $value = json_encode($value);
        }
        return $wpdb->insert(
            $table_name,
            array(
                'attribute_name'  => $key,
                'attribute_label' => $value,
                'attribute_id'    => $id,
            ),
            array(
                '%s',
                '%s',
                '%s',
            )
        );
    }

    public function delete( $attribute_name ){
        global $wpdb;
        $table_name = $this->getTableName();
        $wpdb->delete( $table_name, array('attribute_name' => $attribute_name ) );
    }

    public function truncateData(){
        global $wpdb;
        $table_name = $this->getTableName();
        $wpdb->query("TRUNCATE TABLE {$table_name}");
    }

}