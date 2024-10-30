<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

if (!$this->current_user_has_access())
{
    echo 'Access not granted for this user.';
    exit;
}


$this->ajax_nonce = wp_create_nonce( $this->prefix );


$results = $wpdb->get_results( 'SHOW columns FROM `'.$wpdb->prefix.'dc_mv_calendars'.'` where field=\'oldmonths\'');
if (!count($results))
{
    $wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'dc_mv_calendars'.'` ADD `oldmonths` int(11) NOT NULL default \'0\'');
    $wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'dc_mv_calendars'.'` ADD `olddays` int(11) NOT NULL default \'0\'');
    $wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'dc_mv_calendars'.'` ADD `olddatadel` int(11) NOT NULL default \'0\'');
}

$mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'dc_mv_calendars WHERE id=%d', $this->calendar ) ); 


?>
<div class="wrap">
<h1><?php echo esc_html($this->plugin_name); ?> - Manage Data and Settings</h1>

<input type="button" name="backbtn" value="Back to items list..." onclick="document.location='admin.php?page=<?php echo esc_attr($this->menu_parameter); ?>_manage';">

<form method="post" action="" name="cpformconf"> 
<input name="cpmvc_do_action" type="hidden" id="save_settings" />
<input name="cpmvc_id" type="hidden" value="<?php echo esc_attr($this->calendar); ?>" />


   
<div id="normal-sortables" class="meta-box-sortables">

<br />
  <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Instructions</span></h3>
  <div class="inside"> 
   
      <p>Note: Use the section below only to manage the calendar's data. To add an event <b>click a date</b> or use the <b>"New Event" button</b> and when ready you can <a href="#tabs-6" class="open-tab">publish the calendar</a>.</p>      

  </div>    
 </div>

 
 <hr />
 <h3>These settings apply only to: <?php echo esc_html ($mycalendarrows[0]->title); ?></h3>

   <div id="tabs">
			<ul>
				<li class="ui-state-active"><a href="#tabs-1"><?php echo esc_html(_( 'ADD/EDIT CALENDAR EVENTS' )); ?></a></li>
                <li><a href="#tabs-4"><?php echo esc_html(_( 'OLD DATA CLEANING' ))?></a></li>
				<li><a href="#tabs-2"><?php echo esc_html(_( 'ICAL FEATURES' ))?></a></li>
				<li><a href="#tabs-3"><?php echo esc_html(_( 'EVENT CATEGORIES' ))?></a></li>
                <li><a href="#tabs-5"><?php echo esc_html(_( 'EMAIL NOTIFICATIONS' ))?></a></li>
                <li><a href="#tabs-6"><?php echo esc_html(_( 'PUBLISH' ))?></a></li>
			</ul>
			<div id="tabs-1">
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Calendar Configuration / Administration</span></h3>
  <div class="inside">

 
  <script type="text/javascript">
   var pathCalendar = cpmvc_ajax_object.url+"?cpmvc_id=<?php echo intval($this->calendar); ?>&cpmvc_do_action=mvparse&security="+cpmvc_ajax_object.nonce;
   dc_subjects = "";dc_locations = "";
   initMultiViewCal("cal<?php echo intval($this->calendar); ?>", <?php echo intval($this->calendar); ?>,
            {viewDay:true,
            viewWeek:true,
            viewMonth:true,
            viewNMonth:true,
            viewList:true,
            viewdefault:"nMonth",
            numberOfMonths:12,
            showtooltip:false,
            tooltipon:1,
            shownavigate:false,
            url:"",
            target:0,
            start_weekday:0,
            language:"en-GB",
            cssStyle:"cupertino",
            edition:true,
            btoday:true,
            bnavigation:true,
            brefresh:true,
            bnew:true,
            path:pathCalendar,
            userAdd:true,
            userEdit:true,
            userDel:true,
            userEditOwner:true,
            userDelOwner:true,
            userOwner:-1 , palette:0, paletteDefault:"F00"});
  </script>

  <div id="multicalendar"><div id="cal<?php echo intval($this->calendar); ?>" class="multicalendar"></div></div>
  
   <div style="clear:both;height:20px" ></div>      
   
  </div>    
 </div> 
 
 
        </div>
        
			<div id="tabs-2">
			    <br />
                
            <div style="border:1px dotted black;background-color:#ffffaa;padding-left:15px;padding-right:15px;padding-top:5px;width:740px;font-size:12px;color:#000000;"> 
            <p>The <strong>iCal features</strong> are available in the <a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">commercial version</a> of the plugin. </p>
   <p><button type="button" onclick="window.open('https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download');" style="cursor:pointer;height:35px;color:#20A020;font-weight:bold;">Activate iCal features</button>
            </div>            
          <br />
          <fieldset style=" border:1px solid;padding:10px; ">
           <legend style="padding: 0.2em 0.5em;border:1px solid;">iCal Export</legend>          
			    iCal Link:
          <input type="text" readonly disabled onClick="this.select();" value="<?php echo esc_attr($this->get_FULL_site_url()); ?>/wp-admin/admin.php?page=cp_multiview_upgrade&ical=1" style="width:60%"></nobr>
          
        
          <input type="button" name="calmanage_<?php echo intval($this->calendar); ?>" value="Download iCal File" disabled onclick="document.location='admin.php?page=<?php echo esc_attr($this->menu_parameter); ?>&cpmvc_id=<?php echo intval($this->calendar); ?>&cpmvc_do_action=eical&r='+Math.random();">
          <br /><br />
          *Note: For automatically exporting the events to third party calendars use the iCal Link above, it should be added
          used in the import options of the third party calendar. For manual exporting of the events just download the iCal file and upload it to the third party calendar.
          </fieldset>
          
          <br /><br />
          
          <fieldset style=" border:1px solid;padding:10px; ">
           <legend style="padding: 0.2em 0.5em;border:1px solid;">iCal Manual Import</legend>
           <form method="post" action="" name="cpformconf" enctype="multipart/form-data">          
            <input name="cpmvc_do_action" type="hidden" value="import_ical" />
            <input name="cpmvc_id" type="hidden" value="<?php echo intval($this->calendar); ?>" />
            
            Select iCal file to import:
            <input type="file" name="filenameical" disabled><input type="submit" name="import" disabled value="Import from ICal" >
           </form>   
          </fieldset>           
          
          <br /><br />
          
          <fieldset style=" border:1px solid;padding:10px; ">
           <legend style="padding: 0.2em 0.5em;border:1px solid;">iCal Automatic Import</legend>          
           <form method="post" action="" name="cpformconfauto">          
            <input name="cpmvc_do_action" type="hidden" value="autoimport_ical" />
            <input name="cpmvc_id" type="hidden" value="<?php echo intval($this->calendar); ?>" />
 
            iCal URL to import automatically(should start with http:// or https://):<br />
            <input type="text" style="width:60%" name="autofilenameical" readonly value=""><input type="submit" name="import" value="Update URL" disabled>
            <br />
            <em>* If some URL is specified into this field it will be periodically imported into the plugin events.</em>
           </form>  
          </fieldset>
			</div>
            
			<div id="tabs-3">
            
            <div style="border:1px dotted black;background-color:#ffffaa;padding-left:15px;padding-right:15px;padding-top:5px;width:740px;font-size:12px;color:#000000;"> 
            <p>The <strong>Event Categories features</strong> are available in the <a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">commercial version</a> of the plugin. </p>
   <p><button type="button" onclick="window.open('https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download');" style="cursor:pointer;height:35px;color:#20A020;font-weight:bold;">Activate Event Categories features</button>
            </div>            
          <br />
          
			    <form action="index.php" method="post" name="tabs-3Form" id="tabs-3Form">    
				  <b>Subject list</b><br />
				  New item
				  <div>
				  <input type="text" readonly placeholder="name of the new item" class="itemvalue"><input disabled type="button" value="Add" class="additem">			  
				  <?php 
				  $subjectlist = array();
				  echo '<ul id="subject_sortable" class="sortable">';
				  for ($i=0;$i<count($subjectlist);$i++)
				  {
				    echo '<li id="'.esc_attr($subjectlist[$i]).'" class="ui-state-default">'.esc_html($subjectlist[$i]).'<a href="" class="ui-icon  ui-icon-circle-minus r"></a></li>';
				  }
				  echo '</ul>';
				  echo ((count($subjectlist)>0)?"*Click on and drag an element to a new spot within the list<br />":"");
          
				  ?>
				  </div>
				  <br />
				  <b>Location list</b><br />
				  New item
				  <div>
				  <input type="text" readonly placeholder="name of the new item" class="itemvalue"><input disabled type="button" value="Add" class="additem">			  
				  <?php 
				  $locationslist = array();
				  echo '<ul id="location_sortable" class="sortable">';
				  for ($i=0;$i<count($locationslist);$i++)
				  {
				    echo '<li id="'.esc_attr($locationslist[$i]).'" class="ui-state-default">'.esc_html($locationslist[$i]).'<a href="" class="ui-icon  ui-icon-circle-minus r"></a></li>';
				  }
				  echo '</ul>';
				  echo ((count($locationslist)>0)?"*Click on and drag an element to a new spot within the list<br />":"");
          
				  ?>
				  </div>
				  <input type="button" disabled class="sbtn submitno" id="btnSavelist" value="<?php echo esc_attr(_( 'SAVE' )); ?>"/>
			</div>
            

            <div id="tabs-4">
               <br />
                Enable automatic deletion of old events?
                :
                <select name="olddatadel" id="olddatadel">
                 <option value="0"<?php if (!$mycalendarrows[0]->olddatadel) echo ' selected'; ?>>No</option>
                 <option value="1"<?php if ($mycalendarrows[0]->olddatadel ==1) echo ' selected'; ?>>Yes</option>
                </select>
                <br /><br />
                An event is considered "old" if it is
                <select name="oldmonths" id="oldmonths">
                 <?php 
                     for ($it=0; $it<36; $it++) 
                         echo '<option value="'.esc_attr($it).'"'.($it==$mycalendarrows[0]->oldmonths?' selected':'').'>'.esc_html($it).'</option>';
                 ?>
                </select> months and
                <select name="olddays" id="olddays">
                 <?php 
                     for ($it=0; $it<30; $it++) 
                         echo '<option value="'.intval($it).'"'.($it==$mycalendarrows[0]->olddays?' selected':'').'>'.intval($it).'</option>';
                 ?>
                </select> days old
                
                <p><b>Note:</b> 0 months and 0 days old means that all events older than the current date will be deleted.</p>
               
                <input type="button" class="sbtn submitno" id="btnSaveDataDeletion" value="<?php echo esc_attr(_( 'UPDATE SETTINGS' )); ?>"/>
            
			</div>
            
            <div id="tabs-5">
               <div style="border:1px dotted black;background-color:#ffffaa;padding-left:15px;padding-right:15px;padding-top:5px;width:740px;font-size:12px;color:#000000;"> 
                 <p>With the email notifications feature you can <strong>receive an email notification every-time an event is added, modified or deleted</strong>.</p>
                <p>The <strong>Email Notifications Categories features</strong> are available in the <a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">commercial version</a> of the plugin. </p>
                <p><button type="button" onclick="window.open('https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download');" style="cursor:pointer;height:35px;color:#20A020;font-weight:bold;">Activate Email Notification features</button>
               </div>    
            </div> 
            
            <div id="tabs-6">
                <br />
                For a fast publishing option use the:<br /><br />
                <input class="button-primary button" type="button" name="calpublish1_<?php echo intval($this->calendar); ?>" value="Access the Easy Publising Wizard" onclick="document.location='?page=cp_multiview_publishwizard';" />  
                <br /><br /> ...or for a full set of pulbishing options use the:<br /><br />
                <input class="button-primary button" type="button" name="calpublish2_<?php echo intval($this->calendar); ?>" value="Full Featured Publishing" onclick="document.location='?page=cp_multiview_publishing';" />
                <br /><br />
			</div>            
            
</div>            




[<a href="https://wordpress.org/support/plugin/cp-multi-view-calendar#new-post" target="_blank">Support</a>] | [<a href="<?php echo esc_attr($this->plugin_URL); ?>" target="_blank">Documentation</a>]
</form>
</div>
</div>

<script type="text/javascript">
jQuery(function(){(function($){
			$('#tabs').tabs(); 
			$("#subject_sortable,#location_sortable" ).sortable();			
			$("#subject_sortable,#location_sortable").on("click", ".r", function(){
			  $(this).parent().remove();
			  return false;
			});
			$(".additem").click(function(){
			    var v = $(this).parent().find(".itemvalue").val();
			    if (v!="")
			    {
			        $(this).parent().find( ".sortable" ).append('<li id="'+v+'" class="ui-state-default">'+v+'<a href="" class="ui-icon  ui-icon-circle-minus r"></a></li>');
			    }    
			});
			$(".itemvalue").keypress(function(e) {
        if(e.which == 13) {
            $(this).blur();
            $(this).parent().find(".additem").focus().click();
        }
      });
      
     $('.open-tab').click(function (event) {
        $( "#tabs" ).tabs({ active: 5 })
    });
      
			$("#btnSaveDataDeletion").click(function(){
                var data = {
                        action: '<?php echo esc_js($this->prefix); ?>saveautodel',
                        security: '<?php echo esc_js($this->ajax_nonce); ?>',
                        olddatadel: $( "#olddatadel" ).val(),
	  	                oldmonths: $( "#oldmonths" ).val(),
	  	                olddays: $( "#olddays" ).val(),
                        calid: <?php echo intval($this->calendar); ?>
     	        };
			    $.post(ajaxurl, data).done(function( data ) {
                  alert('Automatic deletion options sucessfully updated');
                });                
			    return false;
			});      
      
			$("#btnSavelist").click(function(){
                var data = {
                        action: '<?php echo esc_js($this->prefix); ?>savelist',
                        security: '<?php echo esc_js($this->ajax_nonce); ?>',
	  	                subjectlist: $( "#subject_sortable" ).sortable( "toArray" ),
	  	                locationslist: $( "#location_sortable" ).sortable( "toArray" ),
                        calid: <?php echo intval($this->calendar); ?>
     	        };
			    $.post(ajaxurl, data).done(function( data ) {
                  alert('List sucessfully updated');
                });                
			    return false;
			});
            
})(jQuery);
});
</script>	
<style type="text/css">
.sortable li{padding:10px}  
.ui-icon.r{float:right}
</style>		














