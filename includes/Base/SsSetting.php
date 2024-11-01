<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use App\Repository\Base;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsConfig;

class SsSetting{

    protected $ssConfig, $baseController;
    protected $app_id;

    public function __construct()
    {
        $this->ssConfig = new SsConfig();
        $this->baseController = new BaseController();
        $this->app_id   = $this->baseController->getAppId();
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
    public function getByKey($setting_key){
        global $wpdb;
        $table  = $this->getTableName();
        $sql    = 'SELECT * FROM '.$table.' WHERE ';
        $sql   .= $wpdb->prepare(" setting_key = %s ",$setting_key);
        $results = $wpdb->get_row( $sql ,ARRAY_A);
        if( empty($results)){
            return [];
        }
        return $results;
    }


    public function getTableName(){
        global $wpdb;
        return $wpdb->prefix.$this->app_id."_setting";
    }

    /**  Create table setting
    * @since  1.0.0
    * @return void
    */

   public function createSettingTable() {
        global $wpdb;
        $collate = $wpdb->get_charset_collate();

        $table_name = $this->getTableName();

        $sql = "CREATE TABLE {$table_name} (
                    setting_key VARCHAR(100) NOT NULL,
                    setting_value VARCHAR(100) NOT NULL,
                    PRIMARY KEY (setting_key)
                ) $collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**  Init data for mapping table
    * @since  1.0.0
    * @return void
    */
    public function initSettingData(){

        global $wpdb;
        $table  = $this->getTableName();
        $db_settings = $wpdb->get_row( 'SELECT * FROM '.$table ,ARRAY_A);
        if( !empty($db_settings))
            return false;

        $default_product_setting  = $this->ssConfig->getProduct('default');
        if( empty($default_product_setting) || !is_array($default_product_setting) )
            return false;

        if( !empty($default_product_setting['type_get_product'] )){
            $this->replaceDB('type_get_product',$default_product_setting['type_get_product']);
        }

        if( !empty($default_product_setting['include_variant'] )){
            $this->replaceDB('include_variant',$default_product_setting['include_variant']);
        }
        
        return true;
    }

    public function replaceDB($key,$value){
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
                'setting_key' => $key, 
                'setting_value' => $value, 
            ),
            array(
                '%s',
                '%s'
            )
        );
    }

    /**  Init data for mapping table
    * @since  1.0.0
    * @return void
    */
    public function saveSettingData( $data = array() ){
        global $wpdb;
        $this->truncateData();
        $wpdb->query('START TRANSACTION');
        foreach ($data as $key => $value ) {
            if( empty($key) )
                continue;

            $this->replaceDB($key,$value);
            
        }
        $wpdb->query('COMMIT');
        return true;
    }

    public function truncateData(){
        global $wpdb;
        $table_name = $this->getTableName();
        $wpdb->query("TRUNCATE TABLE {$table_name}");
    }

    /**
     * Update key socialshop_limit to _options table
     * @param $limit
     * @return void
     */
    public function updateMaxLimit($limit){
        return $this->baseController->updateOption($this->app_id.'_limit',$limit);
    }
    /**
     * Update key socialshop_limit to _options table
     * @param $limit
     * @return void
     */
    public function getMaxLimit($limit){
        return $this->baseController->updateOption($this->app_id.'_limit',$limit);
    }

}