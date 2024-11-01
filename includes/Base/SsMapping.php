<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use App\Repository\Base;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsConfig;

class SsMapping{

    protected $ssConfig, $baseController;
    protected $app_id;

    public function __construct()
    {
        $this->ssConfig = new SsConfig();
        $this->baseController = new BaseController();
        $this->app_id   = $this->baseController->getAppId();
    }

    public function getTableName(){
        global $wpdb;
        return $wpdb->prefix.$this->app_id."_mapping";
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

    public function getValue($mapping_value){
        global $wpdb;
        $table   = $this->getTableName();
        $results = $wpdb->get_row('SELECT * FROM '.$table.' WHERE mapping_value = "'.$mapping_value.'" ',ARRAY_A);
        if( empty($results)){
            return [];
        }
        return $results;
    }

    /**  Create table mapping
    * @since  1.0.0
    * @return boolean
    */

   public function createMappingTable() {
        global $wpdb;
        $collate = $wpdb->get_charset_collate();

        $table_name = $this->getTableName();

        $sql = "CREATE TABLE {$table_name} (
                    mapping_field VARCHAR(100) NOT NULL,
                    mapping_value VARCHAR(100) NOT NULL,
                    PRIMARY KEY (mapping_field)
                ) $collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**  Init data for mapping table
    * @since  1.0.0
    * @return boolean
    */
    public function initMappingData(){
        global $wpdb;
        $table  = $this->getTableName();
        $db_mappings = $wpdb->get_row( 'SELECT * FROM '.$table ,ARRAY_A);
        if( !empty($db_mappings))
            return false;

        $mapping  = $this->ssConfig->getProduct('mapping');
        if( empty($mapping) || !is_array($mapping) )
            return false;

        foreach ($mapping as $key => $value ) {
            if( empty($key) || empty($value)  )
                continue;


            $this->replaceDB($key,$value);
        }
        return true;
    }

    /**  Init data for mapping table
    * @since  1.0.0
    * @return boolean
    */
    public function saveMappingData( $data = array() ){
        global $wpdb;
        $this->truncateData();
        $wpdb->query('START TRANSACTION');
        foreach ($data as $key => $value ) {
            if( empty($key)  )
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
                'mapping_field' => $key,
                'mapping_value' => $value,
            ),
            array(
                '%s',
                '%s'
            )
        );
    }

}