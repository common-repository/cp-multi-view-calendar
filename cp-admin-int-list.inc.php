<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !is_admin() )
{
    echo 'Direct access not allowed.';
    exit;
}

$current_user = wp_get_current_user();

$nonce = wp_create_nonce( 'cpmvc_update_actions_adminlist' );

global $wpdb;
$message = "";
if (isset($_GET['u']) && $_GET['u'] != '' && wp_verify_nonce( sanitize_text_field($_GET['nonce']), 'cpmvc_update_actions_adminlist'))
{
    $wpdb->query( $wpdb->prepare( 'UPDATE `'.$wpdb->prefix.'dc_mv_calendars` SET title=%s,published=%d,owner=%s WHERE id=%d',sanitize_text_field($_GET["name"]), intval($_GET["public"]), sanitize_text_field($_GET["owner"]), intval($_GET['u']) ) );
    $message = "Item updated";
}
else if (isset($_GET['scr']) && $_GET['scr'] != '' && wp_verify_nonce( sanitize_text_field($_GET['nonce']), 'cpmvc_update_actions_adminlist'))
{
    update_option( 'CP_MVC_LOAD_SCRIPTS', ($_GET["scr"]=="1"?"1":"2") );
    update_option( 'CP_MVC_DATEFORMAT', ($_GET["df"]==""?"": ($_GET["df"]=="1"?"1":"2")) );

    $message = "Troubleshoot settings updated";
}


if ($message) echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>".esc_html($message)."</strong></p></div>";

?>
<div class="wrap">
<h1><?php echo esc_html($this->plugin_name); ?></h1>

<script type="text/javascript">
 function cp_updateItem(id)
 {
    var calname = document.getElementById("calname_"+id).value;
    var owner = document.getElementById("calowner_"+id).options[document.getElementById("calowner_"+id).options.selectedIndex].value;
    if (owner == '')
        owner = 0;
    var is_public = (document.getElementById("calpublic_"+id).checked?"1":"0");
    document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>_manage&u='+id+'&nonce=<?php echo esc_js($nonce); ?>&public='+is_public+'&owner='+owner+'&name='+encodeURIComponent(calname);
 }

 function cp_manageSettings(id)
 {
    document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>_manage&cpmvc_id='+id+'&r='+Math.random();
 }

 function cp_deleteItem(id)
 {
    alert('Feature not available / not needed in this version since it supports one calendar.');
 }

 function cp_cloneItem(id)
 {
    alert('Feature not available / not needed in this version since it supports one calendar.');
 }

 function cp_updateConfig()
 {
    if (confirm('Are you sure that you want to update these settings?'))
    {
        var scr = document.getElementById("ccscriptload").value;
        var df = document.getElementById("ccdateformat").value;
        var chs = document.getElementById("cccharsets").value;
        document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>_manage&chs='+chs+'&scr='+scr+'&df='+df+'&nonce=<?php echo esc_js($nonce); ?>';
    }
 }

</script>


<div id="normal-sortables" class="meta-box-sortables">

  <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Instructions</span></h3>
  <div class="inside">

      <p><a href="?page=cp_multiview_publishing"><img src="<?php echo esc_attr(plugins_url('', __FILE__)); ?>/help/publish-calendar-small.png" style="border:1px dotted black;padding:2px;"  alt="Click to enlarge" align="right"></a><span style="font-weight:bold;color:#ff3333">To insert a calendar into a page or post</span>, go to the <strong>edition of the page/post</strong> and use the box named "<strong>CP Multi View Calendar</strong>" below the edition area.</p>

      <p>In that area you can create a new view and when ready sent the shortcode to the editor through the button included for that purpose.</p>

    <p><span style="font-weight:bold;color:#ff3333">To add events to the calendar</span> click the "<strong>Admin Calendar Data</strong>" button below that leads to a page where you can add/edit/delete events on the calendar (just click over the desired dates).</p>

      <p><strong>Want to help to the development of this plugin?</strong> The main features of this plugin are provided free of charge. We need your help to continue developing it and adding new features. If you want to help with the development please <a href="https://wordpress.org/support/view/plugin-reviews/cp-multi-view-calendar?rate=5#postform" style="color:#0000ff;font-weight:bold;">add a review to support it</a>. Thank you!</p>
      <div style="clear:both"></div>
  </div>
 </div>

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Calendar List / Items List</span></h3>
  <div class="inside">


  <table cellspacing="5">
   <tr>
    <th align="left">Calendar Name</th><th align="left">User</th><th></th><th align="left" style="display:none;">&nbsp; &nbsp; Published?</th><th align="left">&nbsp; &nbsp; Options</th>
   </tr>
<?php
  $users = $wpdb->get_results( "SELECT user_login,ID FROM ".$wpdb->users." ORDER BY ID DESC" );
  $myrows = $wpdb->get_results( "SELECT * FROM ". $wpdb->prefix."dc_mv_calendars");
  foreach ($myrows as $item)
      if ($this->is_administrator() || ($current_user->ID == $item->owner))
      {
?>
   <tr>
      <td nowrap><input <?php if (!$this->is_administrator()) echo ' readonly '; ?> type="text" name="calname_<?php echo intval($item->id); ?>" id="calname_<?php echo intval($item->id); ?>" value="<?php echo esc_attr($item->title); ?>" />
      </td>
    <?php if ($this->is_administrator()) { ?>
      <td nowrap>
      <select name="calowner_<?php echo intval($item->id); ?>" id="calowner_<?php echo intval($item->id); ?>">
       <option value="0"<?php if (!$item->owner) echo ' selected'; ?>></option>
       <?php foreach ($users as $user) {
       ?>
          <option value="<?php echo esc_attr($user->ID); ?>"<?php if ($user->ID."" == $item->owner) echo ' selected'; ?>><?php echo esc_html($user->user_login); ?></option>
       <?php  } ?>
      </select>
    </td>
    <td>
      <input class="button" type="button" name="calupdate_<?php echo intval($item->id); ?>" value="Update Name &amp; User" onclick="cp_updateItem(<?php echo intval($item->id); ?>);" />
    </td>
    <?php } else { ?>
        <td nowrap>
        <?php echo esc_html($current_user->user_login); ?>
        </td>
    <?php } ?>
    <?php if ($this->is_administrator()) { ?>
      <td nowrap style="display:none">&nbsp; &nbsp; <input type="checkbox" name="calpublic_<?php echo intval($item->id); ?>" id="calpublic_<?php echo intval($item->id); ?>" value="1" <?php if ($item->published) echo " checked "; ?> /></td>
    <?php } ?>
      <td style="padding-left: 20px;">
                             <input class="button-primary button" type="button" name="calmanage_<?php echo intval($item->id); ?>" value="Admin Calendar Data" onclick="cp_manageSettings(<?php echo intval($item->id); ?>);" />
                              <input class="button-primary button" type="button" name="calpublish_<?php echo intval($item->id); ?>" value="Publish Calendar " onclick="document.location='?page=cp_multiview_publishing';" />
                  <?php if ($this->is_administrator()) { ?>
                             <input class="button" type="button" name="calclone_<?php echo intval($item->id); ?>" value="Clone" onclick="cp_cloneItem(<?php echo intval($item->id); ?>);" />
                             <input class="button" type="button" name="caldelete_<?php echo intval($item->id); ?>" value="Delete" onclick="cp_deleteItem(<?php echo intval($item->id); ?>);" /></td>
                  <?php } ?>
   </tr>
<?php
  }
?>

  </table>



  </div>
 </div>


<?php if ($this->is_administrator()) { ?>
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>New Calendar / Item</span></h3>
  <div class="inside">

        * Pro version supports multiple calendars. <a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">Click here for details</a>.

  </div>
 </div>
<?php } ?>


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Need an Event Booking Calendar?</span></h3>
  <div class="inside">

    <p>With the following plugins you can also have a form for accepting bookings: </p>
    <div style="clear:both"></div>

    <div class="plugin-card plugin-card-appointment-hour-booking" <?php if (is_plugin_active('appointment-hour-booking/app-booking-plugin.php')) echo 'style="display:none"'; ?> >
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-hour-booking&amp;" class="thickbox open-plugin-details-modal">
						Appointment Hour Booking						<img src="<?php echo esc_attr(plugins_url('/images/ahb-icon-256x256.png',__FILE__)); ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons"><li><a class="install-now button" data-slug="appointment-hour-booking" href="<?php echo esc_attr(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=appointment-hour-booking'), 'install-plugin_appointment-hour-booking')); ?>" aria-label="Install Appointment Hour Booking" data-name="Appointment Hour Booking">Install Now</a></li><li><a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-hour-booking" class="thickbox open-plugin-details-modal" aria-label="More information about Appointment Hour Booking" data-title="Appointment Hour Booking">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Booking forms for appointments/services with a start time and a defined duration over a schedule. The start time is visually selected by the end user from a set of start times (based in "open" hours and service duration).</p>

				</div>
			</div>
    </div>

   <div class="plugin-card plugin-card-appointment-booking-calendar" <?php if (is_plugin_active('appointment-booking-calendar/cpabc_appointments.php')) echo 'style="display:none"'; ?>>
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-booking-calendar&amp;" class="thickbox open-plugin-details-modal">
						Appointment Booking Calendar						<img src="<?php echo esc_attr(plugins_url('/images/abc-icon-256x256.png',__FILE__)); ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons"><li><a class="install-now button" data-slug="appointment-booking-calendar" href="<?php echo esc_attr(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=appointment-booking-calendar'), 'install-plugin_appointment-booking-calendar')); ?>" aria-label="Install  Appointment Booking Calendar" data-name="Appointment Booking Calendar">Install Now</a></li><li><a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-booking-calendar" class="thickbox open-plugin-details-modal" aria-label="More information about Appointment Booking Calendar" data-title="Appointment Booking Calendar">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Appointment booking calendar for booking time-slots into dates from a set of available time-slots in a calendar. Includes PayPal payments integration for processing the bookings.</p>

				</div>
			</div>
    </div>

    <div style="clear:both"></div>

    <div class="plugin-card plugin-card-booking-calendar-contact-form" <?php if (is_plugin_active('booking-calendar-contact-form/dex_bccf.php')) echo 'style="display:none"'; ?>>
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=booking-calendar-contact-form&amp;" class="thickbox open-plugin-details-modal">
						Booking Calendar Contact Form						<img src="<?php echo esc_attr(plugins_url('/images/bccf-icon-256x256.png',__FILE__)); ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons"><li><a class="install-now button" data-slug="booking-calendar-contact-form" href="<?php echo esc_attr(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=booking-calendar-contact-form'), 'install-plugin_booking-calendar-contact-form')); ?>" aria-label="Install  Booking Calendar Contact Form" data-name="Booking Calendar Contact Form">Install Now</a></li><li><a href="plugin-install.php?tab=plugin-information&amp;plugin=booking-calendar-contact-form" class="thickbox open-plugin-details-modal" aria-label="More information about Booking Calendar Contact Form" data-title="Booking Calendar Contact Form">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Booking form for booking range of dates (selecting start date and end date). Example  hotel booking, car rental, room booking, etc... PayPal integration included.</p>
				</div>
			</div>
    </div>

    <div style="clear:both"></div>

  </div>
 </div>


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Troubleshoot Area</span></h3>
  <div class="inside">
    <p><strong>Important!</strong>: Use this area <strong>only</strong> if you are experiencing conflicts with third party plugins or if the calendar doesn't appear in the public page.</p>
    <form name="updatesettings">

      Script load method:<br />
       <select id="ccscriptload" name="ccscriptload">
        <option value="1" <?php if (get_option('CP_MVC_LOAD_SCRIPTS',"1") == "1") echo 'selected'; ?>>Classic (Recommended)</option>
        <option value="2" <?php if (get_option('CP_MVC_LOAD_SCRIPTS',"1") != "1") echo 'selected'; ?>>Direct</option>
       </select><br />
       <em>* Change the script load method if the calendar doesn't appear in the public website or if there are conflicts
             with other plugins.</em>

      <div style="display:none">  <br /><br />
      Character encoding:<br />
       <select id="cccharsets" name="cccharsets">
        <option value="">Keep current charset (Recommended)</option>
        <option value="utf8_general_ci">UTF-8 (try this first)</option>
        <option value="latin1_swedish_ci">latin1_swedish_ci</option>
       </select><br />
       <em>* Update the charset if you are getting problems displaying special/non-latin characters. After updated you need to edit the special characters again.</em>
      </div>
        <br /><br />

    Date format: <br />
       <select id="ccdateformat" name="ccdateformat">
        <option value="" <?php if (get_option('CP_MVC_DATEFORMAT',"") == "") echo 'selected'; ?>>Automatic: Use default format each language</option>
        <option value="1" <?php if (get_option('CP_MVC_DATEFORMAT',"") == "1") echo 'selected'; ?>>Overwrite to Month/Day/Year</option>
        <option value="2" <?php if (get_option('CP_MVC_DATEFORMAT',"") == "2") echo 'selected'; ?>>Overwrite to Day/Month/Year</option>
       </select><br />
       <em>* Use this settings field to overwrite the auto-detected date format if needed.</em>

        <br /><br />

       <input type="button" onclick="cp_updateConfig();" name="gobtn" value="UPDATE" />
      <br /><br />
    </form>

  </div>
 </div>


   <script type="text/javascript">
   function cp_editArea(id)
   {
          document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>_manage&edit=1&cal=1&item='+id+'&r='+Math.random();
   }
  </script>


</div>


[<a href="https://wordpress.org/support/plugin/cp-multi-view-calendar#new-post" target="_blank">Support</a>] | [<a href="<?php echo esc_attr($this->plugin_URL); ?>" target="_blank">Documentation</a>]
</form>
</div>














