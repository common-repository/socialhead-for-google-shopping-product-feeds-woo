<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;

use Socialshop\Helpers\Common;

class SsApp extends BaseController{

    protected $baseController, $ssConfig;
    protected $api, $token_key, $domain;

    public function __construct()
    {
        parent::__construct();
        $this->domain   = $this->getDomain();
        $this->ssConfig = new SsConfig();
        $this->api      = parent::getRestUrl();
        $this->token_key= parent::getTokenKey();
    }

    /** Verify and save token
     * @since 1.0.0
     * @param string $token_value
     * @param boolean $body_status Plugin status
     * @return array [status,message]
     */
    public function verify( $token_value, $body_status = null ){
        $default_message = 'Something went wrong';
        $endpoint  = $this->getApiUrl('/verify');
        $body_data = [
        	'admin_path' => Common::getPathFromUrl(admin_url()),
            'email'      => get_option('admin_email',null)
        ];
        if($body_status == false || $body_status == true ){
            $body_data['status'] = $body_status;
        }

        $response = wp_remote_post( $endpoint, array(
            'headers'    => [
                $this->token_key => $token_value,
                'Domain'         => $this->domain
            ],
            'body' => $body_data
        ) );

        if( is_wp_error($response)){
            $messages = $response->get_error_messages();
            update_option('socialshop_notices',['messages'=>$messages]);
            return [
                'status' => false,
                'message' => $response->get_error_message()
            ];
        }

        delete_option('socialshop_notices');

        $response_body = json_decode(wp_remote_retrieve_body($response),true);
        $response_message = isset($response_body['message']) ? $response_body['message'] : $default_message;

        if( $response_body['status'] == true ){
            $shop_id = isset($response_body['data']['id']) ? wc_clean(wp_unslash($response_body['data']['id'])) : null;
            $this->saveToken($token_value, $shop_id);
            $this->updateTokenStatusActivated();
        }

        return [
            'status' => $response_body['status'],
            'message'=> $response_message
        ];
    }

    /** Send tracking data
     * @since 1.0.0
     * @param string $topic in ['activate','deactivate','uninstall']
     * @return void
     */
    public function trackingData( $topic ){
	    global $wp_version;
	    $endpoint  = $this->getApiUrl('/tracking_status');
	    $socialshop_data = $this->getData();
	    $parse_data = [];
		// Format data to [key => value]
	    foreach ( $socialshop_data as $datum ) {
	    	if( empty($datum['option_value']) || empty($datum['option_name']) ){
	    		continue;
		    }
		    $ss_key = explode('socialshop_',$datum['option_name']);
		    $parse_data[$ss_key[1]] = $datum['option_value'];
	    }
	    // Get version value in socialshop.php
	    $version = get_file_data( SOCIALSHOP_PLUGIN_PATH, ['Version']);
	    if( isset($version) && count($version) > 0 ){
		    $parse_data['socialshop_version'] = $version[0];
	    }

        $raw_domain = $this->getDomain();
	    $parse_data['datetime']    = date('Y-m-d H:i:s');
	    $parse_data['php_version'] = phpversion();
	    $parse_data['woocommerce_version'] = get_option('woocommerce_version');
	    $parse_data['wordpress_version']   = $wp_version;
        $parse_data['raw_domain']   = $raw_domain;
	    $token_value = !empty($parse_data['token'])      ? $parse_data['token']      : null;
	    $token_key   = !empty($parse_data['token_key'])  ? $parse_data['token_key']  : null;

        wp_remote_post($endpoint, array(
            'headers'   => [
                $token_key => $token_value,
	            'Domain'  => $raw_domain,
	            'Shop'    => $this->getShopId()
            ],
            'body'      => [
            	'topic' => $topic,
	            'data'  => $parse_data
            ]
        ));
    }

    /** Get owner email by domain
     * @since 2.0.0
     * @param string $email
     */
    function checkLinkedEmail() {
	    $ssRemote = new SsRemote();
	    $response = $ssRemote->get('/link_domain');
	    if( $response['status'] == true ){
	    	$linked_email = @$response['data']['email'];
            $this->updateLinkedEmail($linked_email);
            return $linked_email;
	    }
	    return null;
    }

	public function getTokenKey(){
        return $this->token_key;
    }

    /**
     * @since 1.0.0
     * @param string $endpoint
     * @return string
     */
    public function getApiUrl( $endpoint ){
        return $this->api.$endpoint;
    }

    public function getRestUrl(){
        return $this->api;
    }

}
