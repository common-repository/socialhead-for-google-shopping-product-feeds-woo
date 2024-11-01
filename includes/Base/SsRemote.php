<?php
/**
 * @package  WooSocialshop
 */
namespace Socialshop\Base;
use __ as Lodash;
use GuzzleHttp\Client;

class SsRemote extends BaseController {

	protected $request_data, $api_url, $ssApp;

	public function __construct() {
		$this->ssApp = new SsApp();
		$this->request_data['headers'][$this->getTokenKey()] = $this->getToken() ;
		$this->request_data['headers']['Domain'] = $this->getDomain();
		$this->request_data['headers']['Shop']   = $this->getShopId();
	}

	public function setApiUrl( $endpoint ){
		$this->api_url = $this->ssApp->getApiUrl($endpoint);
	}

	function parseResponse( $response ){
		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		if( is_wp_error($result) || $result['status'] == false ){
			$error_message = isset($result['message']) ? $result['message'] : $this->getDefaultMessageError();
			return [
				'status'  => false,
				'message' => $error_message
			];
		}
		return $result;
	}

	function get( $endpoint, $params = array() ){
		$this->setApiUrl($endpoint);
		$response = wp_remote_get( $this->api_url, array(
			'body'=> $params,
			#$this->request_data,
			'headers' =>  [
				$this->getTokenKey() => $this->getToken(),
				'Shop'          => $this->getShopId(),
				'Domain' 		=> $this->getDomain(),
				'Content-Type'  => 'application/x-www-form-urlencoded'
			]
		) );
		return $this->parseResponse($response);
	}

	function post( $endpoint, $params = array() ){
		$this->setApiUrl($endpoint);
		$response = wp_remote_post( $this->api_url, array(
			'body'   => $params,
			'headers' =>  [
				'Domain' 		=> $this->getDomain(),
				'Shop'          => $this->getShopId(),
				$this->getTokenKey() => $this->getToken(),
				'Content-Type'  => 'application/x-www-form-urlencoded'
			]
		) );
		return $this->parseResponse($response);
	}

}