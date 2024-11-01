<?php

namespace Socialshop\Pages;

use Socialshop\Api\SettingApi;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsApp;
use Socialshop\Base\SsConfig;
use Socialshop\Helpers\Common;
use Socialshop\Base\SsRemote;

class Dashboard extends BaseController{


    public function register(){
        add_action('admin_menu', array($this, 'registerPage'));
        add_filter('admin_footer_text', '__return_false');
    }

    function registerPage(){
        $baseController    = new BaseController();
        $check_requirement = $baseController->checkAllowInstall();
        if( $check_requirement == true )
            return;

        add_menu_page(
            'Socialshop',
            'Socialshop',
            'administrator',
            'socialshop',
            array(  $this,'dashboardHtml' ),
            SOCIALSHOP_PLUGIN_URL.'/assets/images/logo.svg',
            57
        );
    }

    /*
     * Update from version 2.0.0
     * Only show socialhead app when have token or verify code is valid
     */
    function dashboardHtml(){
        $redirectToApp  = false;
        $token          = '';
        if( isset($_GET['ss_code'])){
            $code = wp_unslash($_GET['ss_code']);
            $settingApi = new SettingApi();
            $verify = $settingApi->verifyCode($code);
            if( $verify['status'] == true ){
                $token         = $verify['token'];
                $redirectToApp = true;
            }
        }else{
            $baseController = new BaseController();
            $db_token = $baseController->getToken();
            $token_status = $baseController->getTokenStatus();
            if( !empty($db_token) && $token_status == 'activated' ){
                $redirectToApp = true;
                $token = $db_token;
            }
        }

        $ssConfig   = new SsConfig();
        $env        = $ssConfig->read('Env');
        $globalConfig  = $ssConfig->read('Global');
        $token_status  = $baseController->getTokenStatus();
        $domain        = $baseController->getDomain();
        $channel_id    = Common::generateRandomString( $domain );
        $app_url       = $this->getAppLiteUrl();
        $iframe_link   = $app_url.'/auth/register?domain='.$domain;

        if( !empty($token) && $token_status == 'activated' ){
            $ssRemote = new SsRemote();
            $response = $ssRemote->get('/auth/'.$token);
            $iframe_link = @$response['data']['url'];
            if( empty($iframe_link) || empty(strpos($iframe_link,'token')) ){
                $redirectToApp = false;
            }
        }else{
            $ssApp = new SsApp();
            $linked_email = $ssApp->checkLinkedEmail();
            if( !empty($linked_email)){
                $iframe_link = $app_url.'/auth/re-login?domain='.$domain.'&email='.$linked_email;
            }
        }
        $iframe_link .= '&submit_channel='.$channel_id;

        if( $globalConfig['debug'] ){
            $iframe_link = str_replace('https://uat.socialhead.dev/shoplite','http://localhost:3200',$iframe_link);
        }

        // TEST
        // $iframe_link = 'http://localhost:3200/auth/register?domain='.$domain.'&submit_channel=123123';

        if( $redirectToApp == false )
            return require_once( SOCIALSHOP_PLUGIN_DIR_PATH."/templates/dashboard.php" );

        return require_once( SOCIALSHOP_PLUGIN_DIR_PATH."/templates/socialhead.php" );
    }

}
