<?php
/**
 * @package  WooSocialshop
 */


namespace Socialshop\Base;

class Enqueue{

    protected $compress = '';

    public function register(){
        $ssConfig = new SsConfig();
        $debug    = $ssConfig->getGlobal('debug');
        if( $debug == false ){
            $this->compress = '.min';
        }


        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_global_styles' ) );
    	if( $this->currentPage() == true ){
		    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		    add_action( 'admin_enqueue_scripts', array( $this, 'set_script_header' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'add_pusher_script' ) );
	    }
    }

    public function currentPage(){
    	$page_now = $GLOBALS['pagenow'];
	    if( is_admin() && $page_now == 'admin.php' && @$_GET['page'] == 'socialshop' ){
	    	return true;
	    }
	    return false;
    }

    public function add_pusher_script(){
        $ssConfig = new SsConfig();
        $config   = $ssConfig->read('Env');

        $pusher_key = $config['pusher_key'];
        $pusher_event_name = $config['pusher_event_name'];
        $pusher_cluster = $config['pusher_cluster'];

        ?>

        <script>
            var pusher_key          = "<?php echo $pusher_key ?>";
            var pusher_event_name   = "<?php echo $pusher_event_name ?>";
            var pusher_cluster      = "<?php echo $pusher_cluster ?>";
        </script>

        <?php

    }

    /*
    * @description Register styles into admin
    */
    public function enqueue_styles() {
        wp_enqueue_style( 'socialshop',
            SOCIALSHOP_PLUGIN_URL.'assets/css/main'.$this->compress.'.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    public function enqueue_global_styles(){
        wp_enqueue_style( 'root-socialshop',
            SOCIALSHOP_PLUGIN_URL.'assets/css/global'.$this->compress.'.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    /*
    * @description Register script into admin
    */
    public function enqueue_scripts() {
	    wp_enqueue_script(
		    'pusher',
		    'https://js.pusher.com/7.0/pusher.min.js',
		    array('jquery')
	    );
        wp_enqueue_script(
            'socialshop',
            SOCIALSHOP_PLUGIN_URL.'assets/js/main'.$this->compress.'.js',
            array('jquery')
        );
    }

    function set_script_header(){
    	?>
	    <script>
		    var socialshop_admin_ajax = "<?php echo admin_url('admin-ajax.php') ?>";
	    </script>
		<?php
    }
}
