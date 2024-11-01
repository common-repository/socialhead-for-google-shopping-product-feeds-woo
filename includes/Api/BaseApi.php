<?php
/**
* @package  WooSocialshop
*/
namespace Socialshop\Api;
use Socialshop\Base\BaseController;
use Socialshop\Base\SsApp;
use Socialshop\Base\SsConfig;


class BaseApi{
    
    protected $namespace = 'socialshop';
    protected $ssConfig;

    public function __construct(){
        $this->ssConfig = new SsConfig();
    }
    /**
    * Set namepsace
    * @since 1.0.0
    * @param  string $namespace
    * @return void    
    */
    public function setNameSpace($namespace){
        $this->namespace = $namespace;
    }

    /**
    * Get namespace
    * @since 1.0.0
    * @return string
    */
    public function getNameSpace(){
        return $this->namespace;
    }

    /**
     * @since 1.0.0
     * @param string $endpoint
     * @param string $method
     * @param string $callback
     * @param boolean $permission
     * @return void
     */
    public function registerEndpoint( $endpoint, $method, $callback, $permission = true  ){
	
	    $rest_params = array(
		    'methods' => $method,
            'permission_callback' => '__return_true',
		    'callback' => array( $this, $callback ),
	    );

	    if( $permission == true ){
	    	$rest_params['permission_callback'] = array( $this , 'privilegedPermissionCallback' );
	    }
	    
        register_rest_route( $this->namespace, $endpoint , array(
            $rest_params
        ) );
    }

    public function parseParams( $request ){
        $limit    = $this->ssConfig->readKey('Global','limit');
        $paginate = $this->ssConfig->readKey('Global','paginate');
        $result = ['paged'=>1, 'limit'=>$limit, 'paginate' => $paginate];
        $param  =  wp_parse_args($request);
        if( isset($param['page'])){
            $result['page'] = absint($param['page']);
        }
        if( isset($param['limit'])){
            $result['limit'] = absint($param['limit']);
        }
        return $result;
    }
	
    
    public function privilegedPermissionCallback($request){
	    $baseController = new BaseController();
	    $site_id = $baseController->getToken();
	    $status_plugin = $baseController->getStatusPlugin();
	    $token_status = $baseController->getTokenStatus();
	    $token_value = $request->get_header( $baseController->getTokenKey() ,true);
        if( $token_status == 'activated' && $status_plugin == true && $token_value == $site_id )
		    return true;
	    
	    return false;
	
    }


    public function get( $endpoint, $callback ){
        $this->register( $endpoint ,'GET',$callback);
    }

    public function post( $endpoint, $callback ){
        $this->register( $endpoint ,'POST',$callback);
    }

    public function put( $endpoint, $callback ){
        $this->register( $endpoint ,'PUT',$callback);
    }

    public function delete( $endpoint, $callback ){
        $this->register( $endpoint ,'DELETE',$callback);
    }

}