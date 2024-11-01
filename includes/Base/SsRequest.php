<?php
/**
 * @package  WooSocialshop
 */
namespace Socialshop\Base;
use __ as Lodash;
use GuzzleHttp\Client;

class SsRequest extends BaseController {

    protected $api_hook, $request_data, $ssApp, $client;

    public function __construct()
    {
        $this->ssApp = new SsApp();
        $this->api_hook = $this->ssApp->getApiUrl('/hook');
        $this->request_data['headers'][$this->getTokenKey()] = $this->getToken() ;
        $this->request_data['headers']['Domain'] = $this->getDomain();
        $this->request_data['headers']['Shop']   = $this->getShopId();
        $this->client = new Client();
    }

    public function setApiUrl( $endpoint ){
        $this->api_hook = $this->ssApp->getApiUrl($endpoint);
    }

    function setRequestData( $body = array() ){
        $this->request_data =  Lodash::merge($this->request_data, ['form_params'=>$body]);
    }

    /**  WP safe remote get
     * @since  1.0.0
     * @param  array $body
     * @return array
     */
    public function get( $body = array(), $logged = true ){
        $this->setRequestData($body);
        try{
            $response = $this->client->getAsync($this->api_hook, $this->request_data )->wait(true);
            return [ 'status' => true, 'data' => json_decode($response->getBody()->getContents(), true) ];
        }catch (\Exception $exception){
            if( $logged == true ){
                SsLogger::error( json_encode([
                    'method'  => 'POST',
                    'api'     => $this->api_hook,
                    'data'    => $this->request_data,
                    'message' => $exception->getMessage(),
                ]) );
            }
            return [ 'status'  => false, 'message' => $exception->getMessage() ];
        }
    }

    /**  WP safe remote post
     * @since  1.0.0
     * @param  array $body
     * @return array
     */
    public function post($body = array(), $logged = true ){
        $this->setRequestData($body);
        try{
            $response = $this->client->postAsync($this->api_hook, $this->request_data )->wait(true);
            return ['status' => true, 'data' => json_decode($response->getBody()->getContents(), true)];
        }catch (\Exception $exception){
            if( $logged == true ){
                SsLogger::error( json_encode([
                    'method'  => 'POST',
                    'api'     => $this->api_hook,
                    'data'    => $this->request_data,
                    'message' => $exception->getMessage(),
                ]) );
            }
            return ['status'  => false, 'message' => $exception->getMessage() ];
        }
    }

}