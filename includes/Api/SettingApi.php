<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Api;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsApp;
use Socialshop\Base\SsConfig;
use Socialshop\Base\SsLogger;
use Socialshop\Base\SsMapping;
use Socialshop\Base\SsRemote;
use Socialshop\Base\SsSetting;

class SettingApi extends  BaseApi{

    protected $ssApp, $baseController, $ssConfig, $ssSetting, $ssMapping ;

    protected $debug;
    protected $prefix = '/setting';

    public function __construct(){
        parent::__construct();
        $this->ssApp = new SsApp();
        $this->baseController = new BaseController();
        $this->ssConfig   = new SsConfig();
        $this->ssMapping   = new SsMapping();
        $this->ssSetting   = new SsSetting();
        $this->debug      = $this->checkDebug();
    }

    /**
     * Read debug in Global Config
     * @since 1.0.0
     * @return boolean
     */
    public function checkDebug(){
        return $this->ssConfig->getGlobal('debug');
    }

    public function register(){
        $this->registerActions();
        add_action( 'rest_api_init', array( $this , 'registerEndpoints' ));
    }

    /**
     * Register all endpoint for setting page
     */
    public function registerEndpoints(){
        $this->registerEndpoint( '/health',                         'GET', 'healthCheck', false);
        $this->registerEndpoint( '/uninstall',                      'POST', 'appUninstall');
        $this->registerEndpoint( $this->prefix,                              'POST', 'saveSetting' );
        $this->registerEndpoint( $this->prefix,                              'GET', 'getSetting' );
        $this->registerEndpoint( $this->prefix.'/mapping',          'GET', 'getMappingData' );
        $this->registerEndpoint( $this->prefix.'/sync-setting',     'GET', 'getSettingData' );
        $this->registerEndpoint( $this->prefix.'/last-updated',     'GET', 'getLastUpdated' );
        $this->registerEndpoint( $this->prefix.'/info',             'GET', 'getOptions' );
    }

    public function registerActions(){
        add_action( 'wp_ajax_socialshop_verify_token', array($this,'verifyToken') );
        add_action( 'wp_ajax_nopriv_socialshop_verify_token', array($this,'verifyToken') );
        /**
         * @since 2.0.0
         */
        add_action( 'wp_ajax_socialshop_verify_code',           array($this,'ajaxVerifyCode') );
        add_action( 'wp_ajax_nopriv_socialshop_verify_code',    array($this,'ajaxVerifyCode') );

    }

    function ajaxVerifyCode(){

        if ( !check_admin_referer( 'socialshop_wpnonce_code','_wpnonce' ) ){
            return wp_send_json_error([
                'message' => 'Something went wrong!'
            ]);
        }
        $code = wp_unslash($_POST['code']);
        $verified = $this->verifyCode($code);
        if( $verified['status'] == false ){
            return wp_send_json_error( $verified );
        }
        return wp_send_json_success( $verified );
    }

    function verifyCode( string $code ){
        if( !is_user_logged_in() ){
            return wp_send_json_error(['message' => 'User is invalid!']);
        }
        $ssRemote = new SsRemote();
        $ssApp    = new SsApp();
        // Gọi kiểm tra code và lưu lại token
        $data = $ssRemote->post('/verify_code',['code' => $code] );
        if( empty($data['data']['token'])){
            return [
                'status'  => false,
                'message' => $data['message']
            ];
        }
        $token = $data['data']['token'];
        $ssApp->saveToken($token);
        $ssApp->updateShopId($data['data']['shop_id']);
        $this->baseController->updateTokenStatusActivated();

        // Lấy setting và lưu vào User DB khi có setting
        $source_response = $ssRemote->get('/source_setting' );
        $mapping  = isset($source_response['data']['mapping']) ?
            $source_response['data']['mapping'] : [];
        $setting  = isset($source_response['data']['setting']) ?
            $source_response['data']['setting'] : [];

        if( !empty($mapping)){
               $this->ssMapping->saveMappingData( $mapping );
        }

        if( !empty($setting)){
            $this->ssSetting->saveSettingData( $setting );
        }

        $ssRemote->get('/resync_source');
        // Send request sync product

        return [
            'status'  => true,
            'message' => 'Verify successful!'
        ];
    }

    function updateApiUrl($request){
        $url  = esc_url($request->get_param('url'));
        if( empty($url))
            return wp_send_json_error(['message' => 'Url is required']);

        $this->ssApp->updateRestUrl($url);
        return wp_send_json_success(['url' => $url]);
    }

    function getOptions(){
        $data = $this->baseController->getData();
        return wp_send_json_success( $data );
    }

    /**
     * Get socialshop_last_updated field in _options
     */
    public function getLastUpdated(){
        $last_updated = $this->baseController->getLastUpdated();
        return wp_send_json_success(['last_updated' => $last_updated ]);
    }


    /**  Get list mapping data from DB
    * @since  1.0.0
    * @return json
    */
    public function getMappingData(){
        $mapping  = $this->ssMapping->get();
        return wp_send_json_success( $mapping );
    }

    /**  Get list filter setting from DB
     * @since  1.0.0
     * @return json
     */
    public function getSettingData(){
        $setting  = $this->ssSetting->get();
        return wp_send_json_success( $setting );
    }

    /**  Get list mapping and setting from DB
     * @since  1.0.0
     * @return json
     */
    public function getSetting(){
        $results['info']     = $this->baseController->getData();
        $results['setting']  = $this->ssSetting->get();
        $results['mapping']  = $this->ssMapping->get();
        return wp_send_json_success( $results );
    }


    /**  Save setting include: mapping data and setting filter
     * @since  1.0.0
     * @param  $request
     * @return void
     */
    public function saveSetting($request){

        $limit = $request->get_param('limit');
        if( is_numeric($limit) )
            $this->baseController->updateOptionLimit(absint($limit));

        $last_updated = $request->get_param('last_updated');
        if( empty($last_updated)){
            return wp_send_json_error( [ 'message' => 'The last updated field is required' ] );
        }else{
            $this->baseController->updateOption('socialshop_last_updated',$last_updated);
        }

        $mapping = $request->get_param('mapping');
        if( !empty($mapping) && is_array($mapping) ){
            $this->ssMapping->saveMappingData( $mapping );
        }

        $setting = $request->get_param('setting');
        if( !empty($setting) && is_array($setting) ){
            $this->ssSetting->saveSettingData( $setting );
        }
        #SsLogger::info('[Api][Setting] '.__FUNCTION__.' :: params:'.json_encode(['params' => $request->get_params() ]));
        return wp_send_json_success( [ 'message' => 'The data saved' ] );

    }

    /**
     * Response status 200
     * @return json
     */
    function healthCheck(){
        $version = $this->baseController->getVersion();
        return wp_send_json_success( [
            'message' => 'OK',
            'version' => $version
        ] );
    }

    /** Verify token match with main App
     * @param $request
     * @since 1.0.0
     */
    function verifyToken(){
        $token     = wp_unslash($_POST['token']);
        if( empty($token) ){
            return wp_send_json_error(['message' => 'Token is required!']);
        }
        if( !is_user_logged_in() ){
            return wp_send_json_error(['message' => 'User is invalid!']);
        }

        if ( !check_admin_referer( 'socialshop','_wpnonce' ) ){
            SsLogger::critical('check_ajax_referer[socialshop] - Problem security |
                $request:'.json_encode(['request'=>$_POST])
            );
            return wp_send_json_error(['message' => 'Something went wrong!']);
        }
        $verify = $this->ssApp->verify($token);
        if( $this->debug == true ){
            $verify['status'] = true;
        }

        if( $verify['status'] == false )
            return wp_send_json_error( $verify );

        return wp_send_json_success( $verify );
    }

}