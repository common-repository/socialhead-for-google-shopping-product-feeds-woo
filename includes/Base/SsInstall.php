<?php

namespace Socialshop\Base;

class SsInstall extends BaseController {
    public function register(){
        add_action( 'admin_init', function(){
            if( is_plugin_active(SOCIALSHOP_PLUGIN_BASENAME ) ){
                add_action( 'plugin_action_links_'.SOCIALSHOP_PLUGIN_BASENAME, array( $this , 'adminPluginSettingLinks' ) );
            }
        } );
    }


    /**  Attach links to admin plugins page
     * @hooked plugin_action_links_{SOCIALSHOP_PLUGIN_BASENAME}
     * @since  1.0.0
     * @param  array $links
     * @return array
     */
    public function adminPluginSettingLinks($links ) {
        $links[] = '<a href="'.esc_url( $this->getDefaultPage() ).'">'.__('Setting', $this->app_id).'</a>';
        $links[] = '<a href="'.esc_url( $this->getAppUrl() ).'" target="_blank">'.__('Open Application', $this->app_id).'</a>';
        return array_reverse($links);
    }

}