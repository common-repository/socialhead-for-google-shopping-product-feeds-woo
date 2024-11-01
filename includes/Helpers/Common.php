<?php
/**
* 
* @since 1.0.0
* @param
* @return
*/

namespace Socialshop\Helpers;
use Socialshop\Base\SsConfig;
use Socialshop\Base\BaseController;

/**
 * Common Class.
 */

class Common{

    /**
     * Get text by key in i18n/messages.php
     * @since 1.0.0
     * @param string $key
     * @return string
     */
    static function message( $key ){
        $file = realpath(SOCIALSHOP_PLUGIN_DIR_PATH.'/i18n/messages.php');
        if( !file_exists($file) ){
            return null;
        }
        $messages = require_once( $file );
        return isset($messages[$key]) ? $messages[$key] : null;
    }

	/**
	* 
	* @since 1.0.0
	* @param
	* @return
	*/
	static function getDateFormat(){
		$ssConfig = new SsConfig();
		return $ssConfig->readKey('Global','date_format');
	}

	/**
	* Format date
	* @since 1.0.0
	* @param  $date 
	* @return string
	*/
	static function formatDate($date,$format = null ){
		if( empty($date) )
			return null;
		
		if( empty($format) ) {
			$format = self::getDateFormat();
		}
		$strtotime = strtotime($date);
		return date($format,$strtotime);
	}

    /**
     * Format paging
     * @since 1.0.0
     * @param  integer $page
     * @param  integer $limit
     * @param  integer $total
     * @return array
     */
	static function formatPaging( $page, $limit, $total ){
	    $baseController = new BaseController();
	    $max_limit = $baseController->getOptionLimit();

	    if( !is_numeric($page) || $page <= 0 ){
	        $page = 1;
        }else if ( empty($page)){
            $page = 0;
        }

	    if ( !is_numeric($limit) || empty($limit) || absint($limit) > absint($max_limit )){
	        $limit = absint($max_limit);
        }

	    $last_page = ceil($total / $limit );
        $offset    = ( $page - 1 ) * $limit;
	    $from      = $offset + 1;
	    $to        = ($offset + $limit) >= $total ? $total : ($offset + $limit);
        return [
            'total' => (int) $total,
            'per_page' => (int) $limit,
            'current_page' => (int) $page,
            'last_page' => (int) $last_page,
            'from' => (int) $from,
            'to' => (int) $to,
        ];
    }


    /**  Format $shopId_$Id
     * @since  1.0.3
     * @param  string $objectId
     * @return string
     */
    public static function parseObjectId( $objectId ){
        $parse_id = explode('_',$objectId);
        if( count($parse_id) == 1 ){
	        return $objectId[0];
        }
	    return array_pop($parse_id);

    }

    /**  Get path from full url
     * @since  1.1
     * @param  string $url
     * @return string or null
     */
    public static function getPathFromUrl($url){
	    $url  = parse_url($url );
	    $path = explode('/',$url['path']);
	    $results =  [];
	    foreach( $path as $item ){
		    if( empty($item) ) continue;
		    $results[] = $item;
	    }
	    return implode('/',$results);
    }

    /**
     * @param integer $length
     * @since 2.0.0
     */
	static function generateRandomString($str,$length = 20) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

        $hash =  hash('sha256',$str);

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString.$hash;
	}
    
}