<?php
/**
 * @package  WooSocialshop
 */


namespace Socialshop\Base;

class Deactivate{
	
	public static function deactivate(){
		self::update_option();
		self::updateAppStatus();
		$ssApp = new SsApp();
		$ssApp->trackingData('deactivate');
	}

	/**
	* Mock key to check active plugin in table _options
	*/
    public static function update_option(){
		$baseController = new BaseController();
		$baseController->disablePlugin();
		$baseController->updateTokenStatusDeactivated();
	}


    /**  Send Request to Main App and update app status
     * @since  1.0.0
     * @return void
     */
    public static function updateAppStatus(){
        $ssRequest = new SsRequest();
        $ssRequest->setApiUrl('/uninstall');
        $ssRequest->post([],false);
    }

}