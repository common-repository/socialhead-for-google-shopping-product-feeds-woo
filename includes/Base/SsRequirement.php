<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;

use Socialshop\Helpers\Common;

class SsRequirement extends BaseController {

    public function register(){
        $this->checkRequirePlugins();
	    add_action( 'admin_notices', array($this,'messagesNotice') );
    }

    public function checkRequirePlugins(){
        // Require base plugin
        if ( !$this->isPluginActivate( 'woocommerce' )) {
            unset( $_GET['activate'] );
            unset( $_GET['deactivate'] );
            // Stop activation redirect and show error
            add_action( 'admin_notices', array($this,'pluginNotice') );
            include_once ABSPATH . '/wp-admin/includes/plugin.php';
            $deactivate = Deactivate::class;
            $deactivate::updateAppStatus();
            $this->updateRequirement(true);
            deactivate_plugins( plugin_basename( SOCIALSHOP_PLUGIN_PATH ), true );
            return false;
        }else{
            $this->updateRequirement(false);
            return true;
        }
    }

    public function pluginNotice(){
        echo '<div class="notice notice-error  my-3">
					<p>'.Common::message('require_woocommerce').'</p>
				</div>';
    }
    
    public function messagesNotice(){
    	$notices = get_option('socialshop_notices');
    	if( !empty($notices['messages'])){
		    foreach ($notices['messages'] as $notice) {
			    echo '<div class="notice notice-error  my-3">
					<p> <b> '.SOCIALSHOP_PLUGIN_NAME.':</b> '.$notice.'</p>
				</div>';
    		}
	    }
    }


}