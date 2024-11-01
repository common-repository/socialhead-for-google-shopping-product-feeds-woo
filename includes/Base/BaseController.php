<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;

class BaseController{
    protected $setting_links = [];
    protected $support_link  = 'https://help.socialhead.io';
    protected $app_id  = 'socialshop';
    public function __construct(){
        $this->app_id = 'socialshop';
    }

    public function getDefaultPage(){
        return admin_url('admin.php?page='.SOCIALSHOP_DEFAULT_PAGE);
    }

    /**
     * Get home url without http/https
     * @since 1.0.0
     * @return string
     */
    function getDomain(){
        $home_url = get_option('siteurl');
        $ssConfig = new SsConfig();
        $debug = $ssConfig->getGlobal('debug');
        if( $debug == true ){
            return 'shop787.socialhead.site';
        }
        $explode  = explode('//',$home_url);
        return @$explode[1];
    }
    /**
     * Get home url without http/https
     * @since 1.0.0
     * @return string
     */
    function getToken(){
        $app_id   = $this->getAppId();
        return get_option($app_id.'_token');
    }
    function getTokenKey(){
        $app_id   = $this->getAppId();
        return get_option($app_id.'_token_key');
    }

    function getAppId(){
        return $this->app_id;
    }

    /**
     * Save token value when user verify token
     * @param string $token
     * @param string $shop_id
     * @since 1.0.0
     * @return boolean
     */
    function saveToken( string $token, string $shop_id = null ){
        $app_id = $this->getAppId();
        $key = $app_id.'_token';
        $this->updateTokenStatusActivated();
        $this->updateShopId($shop_id);
        return update_option($key,$token);
    }


    public function updateTokenStatusActivated(){
        return $this->updateOption($this->app_id.'_token_status', 'activated' );
    }

    public function updateTokenStatusDeactivated(){
        return $this->updateOption($this->app_id.'_token_status', 'deactivated' );
    }


    public function getTokenStatus(){
        return get_option($this->app_id.'_token_status');
    }



    /**  Get field socialshop_last_updated in _options table
     * @since  1.0.0
     * @return string
     */
    public function getLastUpdated(){
        return get_option($this->app_id.'_last_updated');
    }

    /**  Get field socialshop_last_updated in _options table
     * @since  1.0.0
     * @return string
     */
    public function getMaxLimit(){
        return get_option($this->app_id.'_limit');
    }

    /**  Update field max limit get products to _options table
     * @since  1.0.0
     * @param  integer $value
     * @param  boolean $override
     * @return boolean
     */
    public function updateOptionLimit( $value, $override = true ){
        return $this->updateOption($this->app_id.'_limit', $value, $override);
    }

    public function updateTokenKey( $value, $override = true ){
        return $this->updateOption($this->app_id.'_token_key', $value, $override);
    }

    public function updateRestUrl($value, $override = true){
        return $this->updateOption($this->app_id.'_api_url', $value, $override );
    }
    public function updateAppUrl($value, $override = true){
        return $this->updateOption($this->app_id.'_app_url', $value, $override );
    }
    public function updateAppLiteUrl($value, $override = true){
        return $this->updateOption($this->app_id.'_app_lite_url', $value, $override );
    }
    public function getRestUrl(){
        return get_option($this->app_id.'_api_url' );
    }
    public function getAppUrl(){
        return get_option($this->app_id.'_app_url' );
    }
    public function getAppLiteUrl(){
        return get_option($this->app_id.'_app_lite_url' );
    }

    public function updateVersion( $version, $override = true ){
        return $this->updateOption($this->app_id.'_version', $version, $override );
    }

    public function getVersion(){
        return get_option($this->app_id.'_version' );
    }

    public function updateShopId( $shop_id ){
        return $this->updateOption($this->app_id.'_shop_id', $shop_id );
    }

    public function getShopId(){
        return get_option($this->app_id.'_shop_id' );
    }

    /** Update status when user deactivate the plugin
     * @return bool
     */
    public function disablePlugin(){
        return $this->updateOption($this->app_id.'_activate', false );
    }

    /** Update status when user activate the plugin
     * @return bool
     */
    public function enablePlugin(){
        return $this->updateOption($this->app_id.'_activate', true );
    }
    /** Update status when user activate the plugin
     * @return bool
     */
    public function getStatusPlugin(){
        return get_option($this->app_id.'_activate', false );
    }

    /**
     * @since 2.0.0
     * @return bool
     */
    public function getAppSubmitChannel(){
        return get_option($this->app_id.'_submit_channel', false );
    }

    /**
     * @since 2.0.0
     * @param string $submit_channel
     * @return bool
     */
    public function updateAppSubmitChannel( $submit_channel){
        return $this->update_option($this->app_id.'_submit_channel', $submit_channel );
    }

    /**
     * @since 2.0.0
     * @return bool
     */
    public function getAppToken(){
        return get_option($this->app_id.'_app_token', false );
    }

    /**
     * @since 2.0.0
     * @param string $app_token
     * @return bool
     */
    public function updateAppToken( $app_token ){
        return $this->update_option($this->app_id.'_app_token', $app_token );
    }

    /** Get owner email by domain
     * @since 2.0.0
     * @return bool
     */
    public function getLinkedEmail(){
        return get_option($this->app_id.'_linked_email', false );
    }

    /** Update owner email by domain
     * @since 2.0.0
     * @return bool
     */
    public function updateLinkedEmail( $email ){
        return $this->updateOption($this->app_id.'_linked_email', $email );
    }

    /**  Update status check requirement, if TRUE is invalid else FALSE then continue setup
     * @since  1.0.0
     * @param  boolean $status
     * @param  boolean $override
     * @return boolean
     */
    public function updateRequirement( $status, $override = true ){
        return $this->updateOption($this->app_id.'_requirement', $status, $override );
    }

    /**  Update to table _options
     * @since  1.0.0
     * @param  string $key
     * @param  string $value
     * @param  string $override
     * @return boolean
     */
    public function updateOption($key, $value, $override = true ){
        if( $override == true ){
            return update_option($key,$value);
        }else{
            $option_value = get_option($key, null );
            if( !empty($option_value) ){
                return update_option($key,$value);
            }
            return false;
        }
    }

    /**  Get _options by key
     * @since  1.0.0
     * @param  string $key
     * @return boolean
     */
    public function getOption($key){
        return get_option( $this->app_id.'_'.$key );
    }

    public function getOptionLimit(){
        return $this->getOption( 'limit' );
    }

    public function checkAllowInstall(){
        return $this->getOption( 'requirement' );
    }


    /**  Check plugin activate
     * @since  1.0.0
     * @param  string $plugin_name
     * @return boolean
     */
    public function isPluginActivate( $plugin_name ){
        // check for plugin using plugin name
        if(in_array($plugin_name.'/'.$plugin_name.'.php', apply_filters('active_plugins', get_option('active_plugins')))){
            return true;
        }
        return false;
    }


    /**  Get all data from _option with prefix is socialshop_
     * @since  1.0.0
     * @return array
     */
    public function getData(){
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'socialshop%'";
        $results = $wpdb->get_results($query, ARRAY_A);
        if( empty($results))
            return [];
        return $results;
    }

    function getDefaultMessageError(){
        return 'Something went wrong!';
    }


}
