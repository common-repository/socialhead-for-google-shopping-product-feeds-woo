<?php
/**
* @package  Socialshop
* Plugin Name: Socialshop
* Plugin URI: http://bit.ly/2KW0qsS
* Description: The Best WooCommerce Plugin for Facebook Shop & Google Shopping Feed. Sync & Optimize product feeds to Google Merchant & Facebook Catalog in 1 click.
* Version: 2.0.7
* Author: Socialhead
* Author URI: https://socialhead.io
* License: GPLv2 or later
* WC tested up to: 6.4.1
* Text Domain: socialshop
*/

if (! defined('ABSPATH') ) {
    die('Invalid request.');
}

if ( defined('SOCIALSHOP_PLUGIN_FILE') ) {
  die("<strong> Socialshop </strong> The app has been already installed. You can find the app in your app page.");
}

// Require once the Composer Autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php') ) {
    include_once dirname(__FILE__) . '/vendor/autoload.php';
}

define('SOCIALSHOP_PLUGIN_FILE', __FILE__);
define('SOCIALSHOP_PLUGIN_ID', 'socialshop');
define('SOCIALSHOP_PLUGIN_NAME', 'socialshop');
define('SOCIALSHOP_PLUGIN_SLUG', 'socialshop');
define('SOCIALSHOP_DEFAULT_PAGE', 'socialshop');
define('SOCIALSHOP_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('SOCIALSHOP_PLUGIN_PATH', __FILE__);
define('SOCIALSHOP_INCLUDE_PATH', plugin_dir_path(__FILE__).'includes/');
define('SOCIALSHOP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SOCIALSHOP_PLUGIN_BASENAME', plugin_basename(__FILE__));

$ssRequirement = new Socialshop\Base\SsRequirement();
$required = $ssRequirement->checkRequirePlugins();

if($required == true ) {

    /**
     * The code that runs during plugin activation
     */
    function socialshop_active_plugin()
    {
        Socialshop\Base\Activate::activate();
    }
    register_activation_hook(SOCIALSHOP_PLUGIN_FILE, 'socialshop_active_plugin');

    /**
     * The code that runs during plugin deactivation
     */
    function socialshop_deactivate_plugin()
    {
        Socialshop\Base\Deactivate::deactivate();
    }
    register_deactivation_hook(SOCIALSHOP_PLUGIN_FILE, 'socialshop_deactivate_plugin');

    /**
     * The code that runs during plugin deactivation
     */
    function socialshop_uninstall_plugin()
    {
        $ssApp = new \Socialshop\Base\SsApp();
        $ssApp->trackingData('uninstall');
    }
    register_uninstall_hook(SOCIALSHOP_PLUGIN_FILE, 'socialshop_uninstall_plugin');

    /**
     * Initialize all the core classes of the plugin
     */
    if (class_exists('Socialshop\\Init')  ) {
        Socialshop\Init::registerServices();
        $SsConfigInstance = Socialshop\Base\SsConfig::class;
        $ssConfig = new $SsConfigInstance();
        $global_config  =  $ssConfig->read('Env');
        $GLOBALS['SOCIALSHOP_ENV'] = $global_config;
    }
}
