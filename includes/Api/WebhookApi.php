<?php
/**
* @package  WooSocialshop
*/
namespace Socialshop\Api;
use Socialshop\Base\SsTaxonomy;
use Socialshop\Base\Webhooks\SsWebhook;
use Socialshop\Base\Webhooks\SsWebhookAttribute;
use Socialshop\Base\Webhooks\SsWebhookTaxonomy;
use Socialshop\Base\SsRequest;

class WebhookApi extends BaseApi{
	public function register(){
        add_action( 'rest_api_init', array( $this , 'registerEndpoints' ));
        $ssWebhook = new SsWebhook();
        $ssWebhook->hookProductChange();

        new SsWebhookAttribute();

        $webhook = new SsWebhookTaxonomy();
        $webhook->register();

    }

    public function registerEndpoints(){
        $this->registerEndpoint( '/webhooks', 'GET', 'getWebhooks' );
    }

    /**
    * @defined wc-webhook-functions.php
    */

    public function getWebhook(){
    	if( !isset($_GET['id']) ){
            return wp_send_json_error(['message' => 'Webhook Id is required']);
    	}

    	$webhook_id = absint($_GET['id']);
    	$webhook = wc_get_webhook($webhook_id);
    	return wp_send_json_success($webhook->get_data());
    }
}
