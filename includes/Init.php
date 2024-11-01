<?php
/**
* @package WooSocialshop
*/

namespace Socialshop;

final class Init{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function getServices()
	{
		return [
            // Register Base Function
            Base\SsRequirement::class,

			Base\Activate::class,
			Base\Deactivate::class,
            Base\Enqueue::class,
            Base\SsInstall::class,
            // Register API
            Api\ProductApi::class,
            Api\TaxonomyApi::class,
            Api\WebhookApi::class,
            Api\SettingApi::class,
            Api\PostMetaApi::class,

            // Register Page
            Pages\Dashboard::class
		];
	}
	/**
	 * Loop through the classes, initialize them,
	 * and call the register() method if it exists
	 * @return
	 */
	public static function registerServices()
	{

		add_action('init',function(){
			foreach (self::getServices() as $class) {
				$service = self::instantiate($class);
				if (method_exists($service, 'register')) {
					$service->register();
				}
			}
		});

	}

	/**
	 * Initialize the class
	 * @param  class $class    class from the services array
	 * @return class instance  new instance of the class
	 */
	private static function instantiate($class)
	{
		$service = new $class();
		return $service;
	}
}