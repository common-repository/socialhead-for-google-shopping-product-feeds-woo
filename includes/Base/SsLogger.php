<?php
/**
 * @package  WooSocialshop
 */

namespace Socialshop\Base;

class SsLogger extends BaseController{
    public static function alert( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->alert( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
        	return;
        }
    }
    public static function critical( $message ){
	    try{
		    $logger = wc_get_logger();
		    $logger->critical( print_r($message,true), array(
			    'source' => 'socialshop'
		    ) );
	    }catch ( \Exception $ex){
		    return;
	    }
    }
    public static function debug( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->debug( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }
    public static function emergency( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->emergency( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }
    public static function error( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->error( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }

    public static function info( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->info( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }

    public static function notice( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->notice( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }

    public static function warning( $message ){
        try{
	        $logger = wc_get_logger();
	        $logger->notice( print_r($message,true), array(
		        'source' => 'socialshop'
	        ) );
        }catch ( \Exception $ex){
	        return;
        }
    }
}