<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !$this ) 
{
    echo 'Direct access not allowed.';
    exit;
}

 if (!is_admin())
    wp_localize_script('jquery', 'cpmvc_ajax_object', array(
        'url' => $this->get_site_url()."/",
        'nonce' => wp_create_nonce("cp_multiviewmain"),
    ));


global $wpdb;                                                           
$this->calendar = intval($base_params["id"]);
require_once dirname( __FILE__ ) . '/php/list.inc.php';
?>
<script type="text/javascript">//<!--
<?php print_sanitized_arrayJSList(); ?>
//-->
</script>
<noscript>The CP Multi View Event Calendar requires JavaScript enabled</noscript>
<div style="z-index:1000;" id="multicalendar">
    <div id="cal1_<?php echo intval($this->print_counter); ?>" class="multicalendar"></div>
</div>        
<div style="clear:both;"></div> 

