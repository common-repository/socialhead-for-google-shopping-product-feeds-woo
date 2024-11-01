<?php


namespace Socialshop\Base;

/**
 * Define and read configs from 'Configs/*'
 */
class SsConfig
{

	protected $config_path;
	/**
	* Define config path
	*/
	function __construct (){
		$this->config_path = SOCIALSHOP_INCLUDE_PATH.'Configs/';
	}

	/**
	* Get config path
	* @param $filename without extension
	* @return string
	*/
	function getPath ( $filename ){
		$file = $this->config_path.$filename.'.php';
		if( !file_exists($file) ){
			return null;
		}
		return $file;
	}

	/**
	* Read config file by key
	* @since 1.0.0
	* @param string $filename File name.
	* @param string $key 	  Key in data return.
	* @param string $default  Default value if not found.
	* @return array|string
	*/
	public function readKey( $filename, $key, $default = null ){
		$data = $this->read($filename);
		if( isset($data[ $key ] ) ){
			return $data[ $key];
		}
		return $default;
	}

	/**
	* Read config file
	* @since 1.0.0
	* @param string $filename File name.
	* @return array|string
	*/
	public function read( $filename ){
		$filePath = $this->getPath($filename);
		if( !file_exists($filePath) ){
			return null;
		}
		return require($filePath);
	}

	/**
	* Get default value in Product config
	* @since   1.0.0
	* @param string $key 	  Key in data return.
	* @return  
	*/
	function getDefaultProduct( $key ){
		$data = $this->readKey('Product','default');
		if( isset($data[ $key ] ) ){
			return $data[ $key];
		}
		return null;
	}
	/**
	* Get default value in Global config
	* @since   1.0.0
	* @param string $key 	  Key in data return.
	* @return
	*/
	function getGlobal( $key ){
		return $this->readKey('Global', $key );
	}
	/**
	* Get default value in Global config
	* @since   1.0.0
	* @param string $key 	  Key in data return.
	* @return
	*/
	function getProduct( $key ){
		return $this->readKey('Product', $key );
	}
	/**
	* Get default value in Global config
	* @since   1.0.0
	* @param string $key 	  Key in data return.
	* @return
	*/
	function getEnv( $key ){
		return $this->readKey('Env', $key );
	}

}