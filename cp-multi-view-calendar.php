<?php
/*
Plugin Name: Calendar Event Multi View
Plugin URI: https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar
Description: This plugin allows you to insert event calendars into your WP website.
Version: 1.4.29
Author: CodePeople
Author URI: https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar
License: GPL
Text Domain: cp-multi-view-calendar
*/


/* initialization / install */

//define('CP_MVC_DEFER_SCRIPTS_LOADING', (get_option('CP_MVC_LOAD_SCRIPTS',"1") == "1"?true:false));
define('CP_MVC_DEFER_SCRIPTS_LOADING',true);

include_once dirname( __FILE__ ) . '/classes/cp-base-class.inc.php';
include_once dirname( __FILE__ ) . '/cp-main-class.inc.php';

$cp_mvc_plugin = new CP_MultiViewCalendar;
register_activation_hook(__FILE__, array($cp_mvc_plugin,'install') ); 
add_action( 'init', array($cp_mvc_plugin, 'data_management'));
add_action( 'wp_loaded', array($cp_mvc_plugin, 'data_management_loaded'));


function cpmvc_codepeople_localize_ajax(){
    global $cp_mvc_plugin;
    wp_localize_script('jquery-ui-core', 'cpmvc_ajax_object', array(
        'url' => $cp_mvc_plugin->get_site_url()."/",
        'nonce' => wp_create_nonce("cp_multiviewmain"),
    ));
}  

function cpmvc_codepeople_localize_ajax_admin(){
    wp_localize_script('jquery', 'cpmvc_ajax_object', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce("cp_multiviewmain"),
    ));
}      

//START: activation redirection 
function cpmv_activation_redirect( $plugin ) {
    if(
        $plugin == plugin_basename( __FILE__ ) &&
        (!isset($_POST["action"]) || $_POST["action"] != 'activate-selected') &&
        (!isset($_POST["action2"]) || $_POST["action2"] != 'activate-selected') 
      )
    {
        exit( esc_url(wp_redirect( admin_url( 'admin.php?page=cp_multiview_publishwizard' ) )) );
    }
}
//add_action( 'activated_plugin', 'cpmv_activation_redirect' );
//END: activation redirection 


if ( is_admin() ) {    
    add_action('admin_enqueue_scripts', array($cp_mvc_plugin,'insert_adminScripts'), 1);    
    add_action('admin_enqueue_scripts', 'cpmvc_codepeople_localize_ajax_admin', 1);   
    add_filter("plugin_action_links_".plugin_basename(__FILE__), array($cp_mvc_plugin,'plugin_page_links'));   
    add_action('admin_menu', array($cp_mvc_plugin,'admin_menu') );
}  
else
    add_action('wp_enqueue_scripts', 'cpmvc_codepeople_localize_ajax');

add_shortcode( $cp_mvc_plugin->shorttag, array($cp_mvc_plugin, 'filter_content') );    


$codepeople_promote_banner_plugins[ 'cp-multi-view-event-calendar' ] = array( 
                      'plugin_name' => 'CP Multi View Calendar', 
                      'plugin_url'  => 'https://wordpress.org/support/plugin/cp-multi-view-calendar/reviews/?filter=5#new-post'
);
require_once 'banner.php';

// optional opt-in deactivation feedback
require_once 'cp-feedback.php';

?>