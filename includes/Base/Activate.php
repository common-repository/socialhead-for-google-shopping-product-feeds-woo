<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;
use App\Repository\Base;

class Activate{

	public static function activate(){

        $ssRequirement = new SsRequirement();
        $ssRequirement->checkRequirePlugins();
				
        $baseController = new BaseController();
        $check_requirement = $baseController->checkAllowInstall();
        if( $check_requirement == true )
            return;

        self::loadPluginTextdomain();
        self::initOptions();
        self::selfVerify();

				$ssApp = new SsApp();
				$ssApp->trackingData('activate');

        $ssMapping = new SsMapping();
        $ssMapping->createMappingTable();
        $ssMapping->initMappingData();

        $ssSetting = new SsSetting();
        $ssSetting->createSettingTable();
        $ssSetting->initSettingData();

        $ssProductAttribute =  new SsProductAttribute();
        $ssProductAttribute->createTable();
        $ssProductAttribute->syncProductAttributes();
	}

	private static function loadPluginTextdomain(){
		load_plugin_textdomain( SOCIALSHOP_PLUGIN_ID, false, SOCIALSHOP_PLUGIN_DIR_PATH.'/i18n/languages' );
	}

	/**
	* @description Mock key to check active plugin in table _options
	*/
	private static function initOptions(){
        $ssApp    = new SsApp();
        $ssConfig = new SsConfig();
        $env_config   = $ssConfig->read('Env');
        $global_config= $ssConfig->read('Global');
        $ssApp->updateTokenKey($env_config['token_key']);
        $ssApp->updateRestUrl($env_config['api_url'],true);
        $ssApp->updateAppUrl($env_config['app_url']);
        $ssApp->updateAppLiteUrl($env_config['app_lite_url']);

				$version = get_file_data( SOCIALSHOP_PLUGIN_PATH, ['Version']);
				$version = isset($version) && count($version) > 0 ? $version[0] : null;
        $ssApp->updateVersion( $version );

        $baseController = new BaseController();
        $baseController->updateOptionLimit($global_config['limit']);
        $baseController->enablePlugin();
	}

	private static function selfVerify(){
		$ssApp = new SsApp();
		$baseController = new BaseController();
		$token_value    = $baseController->getToken();
		$ssApp->verify($token_value);
	}
}
