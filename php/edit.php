<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

check_ajax_referer( 'cp_multiviewmain', 'security' );

$intervalByHour = 12;

function cpmv_get_sanitized_tf_param($key)
{
    if (isset($_POST[$key]))
        return sanitize_text_field($_POST[$key]);
    elseif (isset($_GET[$key]))
        return sanitize_text_field($_GET[$key]);
    else
        return "";
}

$myGET["id"] =  intval(cpmv_get_sanitized_tf_param("id"));
$myGET["calid"] = intval(cpmv_get_sanitized_tf_param("calid"));
$myGET["calendarId"] = intval(cpmv_get_sanitized_tf_param("calendarId"));

$hoursStart = cpmv_get_sanitized_tf_param("hoursStart");
$myGET["hoursEnd"] = cpmv_get_sanitized_tf_param("hoursEnd");
$myGET["palette"] = cpmv_get_sanitized_tf_param("palette");
$myGET["mt"] = cpmv_get_sanitized_tf_param("mt");
$myGET["css"] = cpmv_get_sanitized_tf_param("css");
$myGET["weekstartday"] = cpmv_get_sanitized_tf_param("weekstartday");
$myGET["paletteDefault"] = cpmv_get_sanitized_tf_param("paletteDefault");
$myGET["title"] = cpmv_get_sanitized_tf_param("title");
$myGET["start"] = cpmv_get_sanitized_tf_param("start");
$myGET["end"] = cpmv_get_sanitized_tf_param("end");
$myGET["month_index"] = cpmv_get_sanitized_tf_param("month_index");
$myGET["isallday"] = cpmv_get_sanitized_tf_param("isallday");
$myGET["delete"] = cpmv_get_sanitized_tf_param("delete");


$hoursStart = (is_numeric($myGET["hoursStart"]))? intval($myGET["hoursStart"]):0;
$hoursEnd = (is_numeric($myGET["hoursEnd"]))? intval($myGET["hoursEnd"]):23;

$handle = $wpdb->get_results( "select palettes from ".$wpdb->prefix."dc_mv_configuration where id=1" , ARRAY_A);
$row = $handle[0];
$palettes = unserialize($row["palettes"]);
if (count($palettes) > $myGET["palette"])
    $palette = $palettes[sanitize_key($myGET["palette"])];
else
    $palette = $palettes[0];

function rquote_field($str)
{
    return str_replace('"','&quot;',$str);
}
function getCalendarByRange($id){
  global $wpdb;
  try{        
    $handle = $wpdb->get_results( $wpdb->prepare("select * from `".$wpdb->prefix."dc_mv_events` where `id` = %d", $id) , ARRAY_A);
    $row = $handle[0];
  }catch(Exception $e){
  }
  return $row;
}
function fomartTimeAMPM($h,$m) {
    if ($myGET["mt"]!="false")
        $tmp = (($h < 10)  ? "0" : "") . $h . ":" . (($m < 10)?"0":"") . $m  ;
    else
    {
            $tmp = (($h%12) < 10) && $h!=12 ? "0" . ($h%12)  : ($h==12?"12":($h%12))  ;
            $tmp .= ":" . (($m < 10)?"0":"") . $m . (($h>=12)?"pm":"am");
    }
    return $tmp ;
}
if($myGET["id"]){
  $event = getCalendarByRange(intval($myGET["id"]));
}
$path = plugins_url('/', __FILE__)."../DC_MultiViewCal/";

if (file_exists(dirname( __FILE__ )."/../DC_MultiViewCal/css/".str_replace('.','',sanitize_key($myGET["css"]))."/calendar.css"))
    wp_enqueue_style ( "cpmvc-calendareditcss", plugins_url('../DC_MultiViewCal/css/'.sanitize_key($myGET["css"]).'/calendar.css', __FILE__) );
else
    wp_enqueue_style ( "cpmvc-calendareditcss", plugins_url('../DC_MultiViewCal/css/cupertino/calendar.css', __FILE__) );



    
    wp_enqueue_style ( "cpmvc-calendaredit1css", plugins_url('../DC_MultiViewCal/css/main.css', __FILE__) );
    wp_enqueue_style ( "cpmvc-calendaredit3css", plugins_url('../DC_MultiViewCal/css/colorselect.css', __FILE__) );
    wp_enqueue_style ( "cpmvc-calendaredit4css", plugins_url('../DC_MultiViewCal/src/Plugins/jquery.cleditor.css', __FILE__) );
    wp_enqueue_style ( "cpmvc-calendaredit5css", plugins_url('../DC_MultiViewCal/css/bootstrap.min.css', __FILE__) );
    
    


        wp_register_script('cpmvc-common', plugins_url('../DC_MultiViewCal/src/Plugins/Common.js', __FILE__));
        wp_register_script('cpmvc-underscore', plugins_url('../DC_MultiViewCal/src/Plugins/underscore.js', __FILE__));
        wp_register_script('cpmvc-rrule', plugins_url('../DC_MultiViewCal/src/Plugins/rrule.js', __FILE__));

        wp_register_script('cpmvc-lang', $langscript);

        wp_register_script('cpmvc-jqcalendar', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.calendar.js', __FILE__));
        wp_register_script('cpmvc-jqalert', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.alert.js', __FILE__));
        wp_register_script('cpmvc-multiview', plugins_url('../DC_MultiViewCal/src/Plugins/multiview.js', __FILE__));
        
        wp_register_script('cpmvc-tinymcecodep', plugins_url('../DC_MultiViewCal/src/Plugins/tinymce/code/plugin.min.js', __FILE__));
        wp_register_script('cpmvc-jqueryform', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.form.js', __FILE__));   
        
        wp_register_script('cpmvc-validate', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.validate.js', __FILE__));   
        wp_register_script('cpmvc-colorselect', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.colorselect.js', __FILE__)); 
        wp_register_script('cpmvc-bootstrap', plugins_url('../DC_MultiViewCal/src/Plugins/bootstrap.min.js', __FILE__));   
        wp_register_script('cpmvc-repeat', plugins_url('../DC_MultiViewCal/src/Plugins/repeat.js', __FILE__));          
    
       if (file_exists(dirname( __FILE__ ).'/../DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js'))
           wp_register_script('cpmvc-langedit', plugins_url('../DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js', __FILE__)); 
       else
           wp_register_script('cpmvc-langedit', plugins_url('../DC_MultiViewCal/language/multiview_lang_en_GB.js', __FILE__)); 
    
       // $baseurl = includes_url( 'js/tinymce' );
        
        $dependencies = array('cpmvc-langedit',"jquery",
                           "jquery-ui-core",
                           "jquery-ui-dialog", 
                           "jquery-ui-datepicker",
                           "wp-tinymce",
                           'cpmvc-common',
                           'cpmvc-rrule',
                           'cpmvc-lang',
                           'cpmvc-jqcalendar',
                           'cpmvc-jqalert',
                           'cpmvc-tinymcecodep',
                           'cpmvc-jqueryform',
                           'cpmvc-validate',
                           'cpmvc-colorselect',
                           'cpmvc-bootstrap',
                           'cpmvc-repeat',                           
                           
                           'cpmvc-multiview'
                           );
        if (!isset($_GET["fl_builder"]))
            $dependencies[] = 'cpmvc-underscore'; 
        
        wp_enqueue_script( 'cpmvc-publicjsedit', plugins_url('../DC_MultiViewCal/src/Plugins/jquery.cleditor.js', __FILE__),
                           $dependencies,
                           false, true);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" >
  <head>
    <?php 
     // wp_head(); 
    ?>  
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Calendar Details</title>
<?php

 $prefix_ui = '';
 if (file_exists(dirname( __FILE__ ).'/../../../../wp-includes/js/jquery/ui/jquery.ui.core.min.js'))
     $prefix_ui = 'jquery.ui.';
     
 ?>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/jquery.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/ui/'.$prefix_ui.'core.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/ui/'.$prefix_ui.'tooltip.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/ui/'.$prefix_ui.'button.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/ui/'.$prefix_ui.'dialog.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo $path.'../../../../wp-includes/js/jquery/ui/'.$prefix_ui.'datepicker.min.js'; ?>'></script>

    
<script id='cpmvc_ajax-js-extra'>
var cpmvc_ajax_object = {"url":"<?php echo esc_js($this->get_site_url())."/"; ?>","nonce":"<?php echo esc_js(wp_create_nonce("cp_multiviewmain")); ?>"};
</script>
    

<style type="text/css">
#multicalendar .cleditorMain iframe body{min-height:100px;max-height:200px}
#multicalendar .cleditorMain iframe body{width:100%}
#repeatsave a,#repeatdelete a{width:150px;text-align:center;display:block;float:left;margin:3px 10px 20px 0px}
.ui-dialog{ position: absolute;top:10px  }
.ui-widget-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.ui-widget-overlay { background: #eeeeee ; opacity: .80;filter:Alpha(Opacity=80); }

.ui-datepicker-trigger     {
        width:23px;
        height:23px;
        border:none;
        cursor:pointer;
        background:url("<?php echo esc_attr($path); ?>css/images/cal.gif") no-repeat center center;
        margin-left:5px;
}
#repeat,#repeatsave,#repeatdelete{display:none;font-family: "Lucida Grande","Lucida Sans Unicode",Arial,Verdana,sans-serif;font-size: 12px;}

#repeat div{padding:2px;}
#repeat label{width:100px;float:left}
#repeat .fl{float:left}
#repeat .clear{clear:both}

#repeat.ui-dialog-content{display:block}
</style>
</head>
<body class="multicalendar calendaredition" id="multicalendar">
    
    <?php @include dirname( __FILE__ ) . '/list.inc.php'; ?>
    <script type="text/javascript">//<!--
    <?php print_sanitized_arrayJSList() ?>
    
    //-->
    </script>
    <div class="modal fade" id="cpmvcmodalResponse" role="dialog" style="display:none">
        <div class="modal-dialog">
          <div class="modal-content"> 
                <div id="cpmvcmodalResponsecontent" style="padding:25px;">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="infocontainer ui-widget-content" >    
                        <form action="<?php $url = $this->get_site_url()."/"; echo esc_attr(str_replace('//','/',$url)); ?>?cpmvc_id=<?php echo intval($this->calendar); ?>&cpmvc_do_action=mvparse&security=<?php echo esc_attr(wp_create_nonce("cp_multiviewmain")); ?>&f=datafeed&calid=<?php echo intval($myGET["calid"]);?>&month_index=<?php echo intval($myGET["month_index"]);?>&method=adddetails<?php echo isset($event)?"&id=".intval($event["id"]):""; ?>" class="fform" id="fmEdit" method="post">
                             <label>
                                <span id="s_subject">*Subject:</span>
                                <div id="calendarcolor">
                                </div>
                                <?php
                                if (isset($dc_subjects) && is_array($dc_subjects))
                                {
                                    echo '<select id="Subject" name="Subject" class="required safe inputtext" >';
                                    for ($i=0;$i<count($dc_subjects);$i++)
                                    {
                                        echo '<option value="'.esc_attr($dc_subjects[$i]).'" '.((isset($event) && ($event["title"]==$dc_subjects[$i]))?"selected":"").'>'.esc_html($dc_subjects[$i]).'</option>';
                                    }
                                    echo '</select>';
                                }
                                else
                                {
                                    echo '<input MaxLength="200" class="required safe inputtext" id="Subject" name="Subject" type="text" value="'.(isset($event)?esc_attr($event["title"]):"").'" />';
                                    if (!isset($event))
                                    {
                                    ?>
                                    <script>
                                    jQuery("#Subject").val('<?php echo esc_js($myGET["title"]);?>');
                                    </script>
                                    <?php
                                    }
                                }
                                ?>
                                <input id="colorvalue" name="colorvalue" type="hidden" value="<?php echo isset($event)?esc_attr($event["color"]):"" ?>" />
                            </label>
                            <input type="hidden" id="rrule" name="rrule" value="<?php echo esc_attr($event["rrule"]); ?>" size=55 />
                            <input type="hidden" id="rruleType" name="rruleType" value="" size=55 />
                            <label>
                                <span id="s_time">*Time:</span>
                                <div>
                                    <?php if(isset($event) && ($event["rrule"]=="")){  //no recurrent events
                                        $sarr = explode(" ", php2JsTime(mySql2PhpTime($event["starttime"])));
                                        $earr = explode(" ", php2JsTime(mySql2PhpTime($event["endtime"])));
                                        $shm = explode(":", $sarr[1]);
                                        $ehm = explode(":", $earr[1]);
                                        $stpartdate = $sarr[0];
                                        $stparttime = fomartTimeAMPM(intval($shm[0]),intval($shm[1]));
                                        $etpartdate = $earr[0];
                                        $etparttime = fomartTimeAMPM(intval($ehm[0]),intval($ehm[1]));
                                    }
                                    else if ($myGET["start"]!="" && $myGET["end"]!="")
                                    {
                                        $sarr = explode(" ", sanitize_text_field($myGET["start"]));
                                        $earr = explode(" ", sanitize_text_field($myGET["end"]));
                                        $shm = explode(":", $sarr[1]);
                                        $ehm = explode(":", $earr[1]);
                                        $stpartdate = $sarr[0];
                                        $stparttime = fomartTimeAMPM(intval($shm[0]),intval($shm[1]));
                                        $etpartdate = $earr[0];
                                        $etparttime = fomartTimeAMPM(intval($ehm[0]),intval($ehm[1]));
                                    }
                                    else
                                    {
                                         $stpartdate = date("n/j/Y");
                                         $stparttime = fomartTimeAMPM(8,0);
                                         $etpartdate = $stpartdate;
                                         $etparttime = fomartTimeAMPM(9,0);
                                    }
                                    if ($myGET["month_index"]=="1" && $stpartdate!="" && $etpartdate!="")
                                    {
                                        $sarr = explode("/", $stpartdate);
                                        $stpartdate = $sarr[1]."/".$sarr[0]."/".$sarr[2];
                                        $earr = explode("/", $etpartdate);
                                        $etpartdate = $earr[1]."/".$earr[0]."/".$earr[2];
                                    }
                                    ?>
                                    <input MaxLength="10" class="required date" id="stpartdate" name="stpartdate" type="text" value="<?php echo esc_attr($stpartdate); ?>" />
                                    <select class="time" id="stparttime" name="stparttime" style="width:52px;"></select><span id="s_to" class="inl">To</span>
                                    <input MaxLength="10" class="required date" id="etpartdate" name="etpartdate" type="text" value="<?php echo esc_attr($etpartdate); ?>" />
                                    <select class="time" id="etparttime" name="etparttime" style="width:52px;"></select>
                                    <input MaxLength="10" id="stpartdatelast" name="stpartdatelast" type="hidden" value="" />
                                    <input MaxLength="10" id="etpartdatelast" name="etpartdatelast" type="hidden" value="" />
                                    <input MaxLength="10" id="stparttimelast" name="stparttimelast" type="hidden" value="" />
                                    <input MaxLength="10" id="etparttimelast" name="etparttimelast" type="hidden" value="" />
                                    <label class="checkp">
                                      <input id="IsAllDayEvent" name="IsAllDayEvent" type="checkbox" value="1" <?php if(isset($event)&&$event["isalldayevent"]!=0 || $myGET["isallday"]=="1") {echo "checked";} ?>/><span id="s_all_day_event" class="inl">All Day Event</span>
                                    </label>
                                    <div>
                                    <label class="checkp">
                                        <input id="repeatcheckbox" name="repeatcheckbox" type="checkbox" value="1" <?php if(isset($event)&&$event["rrule"]!="") {echo "checked";} ?>/><span class="inl"><span id="repeat1" class="inl">Repeat</span>: <span id="repeatspan" class="inl"></span> <a href="#" id="repeatanchor">Edit</a></span>
                                    </label>
                                    </div>
                                </div>
                            </label>
                            <label>
                                <span id="s_location">Location:</span>
                                <?php
                                if (isset($dc_locations) && is_array($dc_locations))
                                {
                                    echo '<select id="Location" name="Location" class="required safe inputtext" >';
                                    for ($i=0;$i<count($dc_locations);$i++)
                                    {
                                        echo '<option value="'.esc_attr($dc_locations[$i]).'" '.((isset($event) && ($event["location"] ==$dc_locations[$i]))?"selected":"").'>'.esc_html($dc_locations[$i]).'</option>';
                                    }
                                    echo '</select>';
                                }
                                else
                                    echo '<input MaxLength="200" id="Location" name="Location" class="inputtext"  type="text" value="'.((isset($event))?esc_attr($event["location"]):"").'" />';
                                ?>                            
                            </label>
                            <label>
                                <span id="s_remark">Remark:</span>
                                <textarea cols="20" id="Description" name="Description" rows="2" style="width:100%"><?php echo isset($event)?esc_attr($event["description"]):""; ?></textarea>
                            </label>
                            <input id="timezone" name="timezone" type="hidden" value="" />
                            <div class="editbtns">
                            <a href="#" class="btn btn-primary" id="savebtn">Save</a>
                            <?php if(isset($event) && ($myGET["delete"]=="1")){ ?>
                            <a href="#" class="btn btn-primary" id="deletebtn">Delete</a>
                            <?php } ?>
                            <a href="#" class="btn btn-primary" id="closebtn">Close</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="repeatsave">
        <h2 id="rsh2">Edit recurring event</h2>
        <p id="rsp1">Would you like to change only this event, all events in the series, or this and all following events in the series?</p>
        <div style="clear:both"><a href="#" id="r_save_one" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Only this event</a> <span id="rss1">All other events in the series will remain the same.</span></div>
        <div style="clear:both"><a href="#" id="r_save_following" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Following events</a> <span id="rss2">This and all the following events will be changed.</span><br />
        <span id="rss3">Any changes to future events will be lost.</span></div>
        <div style="clear:both"><a href="#" id="r_save_all" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">All events</a> <span id="rss4">All events in the series will be changed.</span><br />
        <span id="rss5">Any changes made to other events will be kept.</span></div>
        <div style="clear:both;float:right"><a href="#" id="r_save_cancel" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Cancel this change</a></div>
        <div style="clear:both"></div>
    </div>
    <div id="repeatdelete">
        <h2 id="rdh2">Delete recurring event</h2>
        <p id="rdp1">Would you like to delete only this event, all events in the series, or this and all future events in the series?</p>
        <div style="clear:both"><a href="#" id="r_delete_one" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Only this instance</a> <span id="rds1">All other events in the series will remain.</span></div>
        <div style="clear:both"><a href="#" id="r_delete_following" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">All following</a> <span id="rds2">This and all the following events will be deleted.</span></div>
        <div style="clear:both"><a href="#" id="r_delete_all" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">All events in the series</a> <span id="rds3">All events in the series will be deleted.</span></div>
        <div style="clear:both;float:right"><a href="#" id="r_delete_cancel" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Cancel this change</a></div>
        <div style="clear:both"></div>
    </div>
    <div id="repeat">
        <div>
            <label id="rl1">Repeats</label>
            <select id="freq" style="width:100%">
                <option id="opt0" value="0">Daily</option>
                <option id="opt1" value="1">Every weekday (Monday to Friday)</option>
                <option id="opt2" value="2">Every Monday, Wednesday, and Friday</option>
                <option id="opt3" value="3">Every Tuesday, and Thursday</option>
                <option id="opt4" value="4">Weekly</option>
                <option id="opt5" value="5">Monthly</option>
                <option id="opt6" value="6">Yearly</option>
            </select>
        </div>
        <div id="intervaldiv">
            <label id="rl2">Repeat every:</label>
            <select id="interval"></select> <span id="interval_label">weeks</span>
        </div>
        <div id="bydayweek">
            <label id="rl3">Repeat on:</label>
            <input id="bydaySU" class="bydayw" name="SU" type="checkbox"><span id="chk0">SU</span>
            <input id="bydayMO" class="bydayw" name="MO" type="checkbox"><span id="chk1">MO</span>
            <input id="bydayTU" class="bydayw" name="TU" type="checkbox"><span id="chk2">TU</span>
            <input id="bydayWE" class="bydayw" name="WE" type="checkbox"><span id="chk3">WE</span>
            <input id="bydayTH" class="bydayw" name="TH" type="checkbox"><span id="chk4">TH</span>
            <input id="bydayFR" class="bydayw" name="FR" type="checkbox"><span id="chk5">FR</span>
            <input id="bydaySA" class="bydayw" name="SA" type="checkbox"><span id="chk6">SA</span>
        </div>
        <div id="bydaymonth">
            <label id="rl4">Repeat by:</label>
            <input id="byday_m" class="bydaym" name="bydaym" type="radio" value="1" checked="checked"> <span id="bydaymonth1">day of the month</span>
            <input id="byday_w" class="bydaym" name="bydaym" type="radio" value="2"> <span id="bydaymonth2">day of the week</span>
        </div>
        <div>
            <label id="rl5">Starts on:</label>
            <label id="starts"><?php echo esc_attr($stpartdate); ?></label>
        </div>
        <div class="clear"></div>
        <div>
            <label id="rl6">Ends:</label>
            <div class="fl">
                <div><input id="end_never" name="end" checked="" title="Ends never" type="radio"> <span id="end1">Never</span></div>
                <div><input id="end_count" name="end" title="Ends after a number of occurrences" type="radio"> <span id="end21">After</span> <select id="end_after"></select> <span id="end22">occurrences</span></div>
                <div><input id="end_until" name="end" title="Ends on a specified date" type="radio"> <span id="end3">On</span> <input size="10" id="end_until_input" value="5/14/2013"></div>
            </div>
        </div>
        <div class="clear"></div>
        <div>
            <label id="rl7">Summary:</label>
            <span id="summary"></span>
        </div>
        <input type="hidden" id="format" value="" size=55 />
        <a href="#" class="btn btn-primary" id="savebtnRepeat">Save</a>
        <a href="#" class="btn btn-primary" id="closebtnRepeat">Close</a>
        <br />
        <br />
    </div>



  </body id="nomoresc">
<?php wp_footer(); ?>  
    <script type="text/javascript">
        var __WDAY = new Array(i18n.dcmvcal.dateformat.sun, i18n.dcmvcal.dateformat.mon, i18n.dcmvcal.dateformat.tue, i18n.dcmvcal.dateformat.wed, i18n.dcmvcal.dateformat.thu, i18n.dcmvcal.dateformat.fri, i18n.dcmvcal.dateformat.sat);
        var __WDAY2 = new Array(i18n.dcmvcal.dateformat.sun2, i18n.dcmvcal.dateformat.mon2, i18n.dcmvcal.dateformat.tue2, i18n.dcmvcal.dateformat.wed2, i18n.dcmvcal.dateformat.thu2, i18n.dcmvcal.dateformat.fri2, i18n.dcmvcal.dateformat.sat2);
        var __MonthName = new Array(i18n.dcmvcal.dateformat.jan, i18n.dcmvcal.dateformat.feb, i18n.dcmvcal.dateformat.mar, i18n.dcmvcal.dateformat.apr, i18n.dcmvcal.dateformat.may, i18n.dcmvcal.dateformat.jun, i18n.dcmvcal.dateformat.jul, i18n.dcmvcal.dateformat.aug, i18n.dcmvcal.dateformat.sep, i18n.dcmvcal.dateformat.oct, i18n.dcmvcal.dateformat.nov, i18n.dcmvcal.dateformat.dec);
        var __MonthNameLarge = new Array(i18n.dcmvcal.dateformat.l_jan, i18n.dcmvcal.dateformat.l_feb, i18n.dcmvcal.dateformat.l_mar, i18n.dcmvcal.dateformat.l_apr, i18n.dcmvcal.dateformat.l_may, i18n.dcmvcal.dateformat.l_jun, i18n.dcmvcal.dateformat.l_jul, i18n.dcmvcal.dateformat.l_aug, i18n.dcmvcal.dateformat.l_sep, i18n.dcmvcal.dateformat.l_oct, i18n.dcmvcal.dateformat.l_nov, i18n.dcmvcal.dateformat.l_dec);
        var __MilitaryTime = <?php echo  ($myGET["mt"]!="false")?"true":"false";?>

        if (!DateAdd || typeof (DateDiff) != "function") {
            var DateAdd = function(interval, number, idate) {
                number = parseInt(number);
                var date;
                if (typeof (idate) == "string") {
                    date = idate.split(/\D/);
                    eval("var date = new Date(" + date.join(",") + ")");
                }
                if (typeof (idate) == "object") {
                    date = new Date(idate.toString());
                }
                switch (interval) {
                    case "y": date.setFullYear(date.getFullYear() + number); break;
                    case "m": date.setMonth(date.getMonth() + number); break;
                    case "d": date.setDate(date.getDate() + number); break;
                    case "w": date.setDate(date.getDate() + 7 * number); break;
                    case "h": date.setHours(date.getHours() + number); break;
                    case "n": date.setMinutes(date.getMinutes() + number); break;
                    case "s": date.setSeconds(date.getSeconds() + number); break;
                    case "l": date.setMilliseconds(date.getMilliseconds() + number); break;
                }
                return date;
            }
        }
        function formatDateFromTo(value,y1_index,m1_index,d1_index,separator1,y2_index,m2_index,d2_index,separator2)
        {
            var arrs = value.split(separator1);
            var year = arrs[y1_index];
            var month = arrs[m1_index];
            var day = arrs[d1_index];

            var newArray = new Array();
            newArray[y2_index] = year;
            newArray[m2_index] = month;
            newArray[d2_index] = day;
            value = newArray.join(separator2);
            return value;
        }
        function getHM(date)
        {
             var hour =date.getHours();
             var minute= date.getMinutes();
             var ret= (hour>9?hour:"0"+hour)+":"+(minute>9?minute:"0"+minute) ;
             return ret;
        }
        $(document).ready(function() {
            //debugger;
            var intervalByHour = <?php echo intval($intervalByHour);?>;
            var DATA_FEED_URL = cpmvc_ajax_object.url+"?cpmvc_id=<?php echo intval($this->calendar); ?>&security="+cpmvc_ajax_object.nonce+"&cpmvc_do_action=mvparse&f=datafeed&calid=<?php echo intval($myGET["calid"]); ?>";
            var optt = "";
            for (var i = <?php echo $hoursStart?>; i <= <?php echo $hoursEnd?>; i++) {
                for (var j=0;j<intervalByHour;j++)
                {
                    optt = fomartTimeAMPM(i,j*60/intervalByHour,__MilitaryTime);
                    $("#stparttime").append('<option  value="'+optt+'">'+optt+'</option>');
                    $("#etparttime").append('<option>'+fomartTimeAMPM(i,j*60/intervalByHour,__MilitaryTime)+'</option>');
                }    
            }

            $("#timezone").val(new Date().getTimezoneOffset()/60 * -1);
            
            $("#repeatcheckbox").click(function(e) {
                if (!this.checked)
                {
                    $("#rrule").val("");
                    $("#repeatspan").html("");
                }
                else
                {
                    $("#rrule").val($("#format").val());
                    $("#repeatspan").html($("#summary").html());
                    openRepeatWin();
                }
            });
            $("#repeatanchor").click(function(e) {
                openRepeatWin();

            });

            var check = $("#IsAllDayEvent").click(function(e) {
                if (this.checked) {
                    $("#stparttime").val(fomartTimeAMPM(0,0,__MilitaryTime)).hide();
                    $("#etparttime").val(fomartTimeAMPM(0,0,__MilitaryTime)).hide();
                }
                else {
                    var d = new Date();
                    var p = 60 - d.getMinutes();
                    if (p > 30) p = p - 30;
                    d = DateAdd("n", p, d);
                    $("#stparttime").val(fomartTimeAMPM(d.getHours(),d.getMinutes(),__MilitaryTime)).show();
                    d = DateAdd("h", 1, d);
                    $("#etparttime").val(fomartTimeAMPM(d.getHours(),d.getMinutes(),__MilitaryTime)).show();
                }
            });
            if (check[0].checked) {
                $("#stparttime").val(fomartTimeAMPM(0,0,__MilitaryTime)).hide();
                $("#etparttime").val(fomartTimeAMPM(0,0,__MilitaryTime)).hide();
            }
            $("#repeat1").html(i18n.dcmvcal.repeat);
            $("#repeatanchor").html(i18n.dcmvcal.edit);
            $( "#s_subject" ).html(i18n.dcmvcal.subject);
            $( "#s_time" ).html(i18n.dcmvcal.time);
            $( "#s_to" ).html(i18n.dcmvcal.to);
            $( "#s_all_day_event" ).html(i18n.dcmvcal.all_day_event);
            $( "#s_location" ).html(i18n.dcmvcal.location);
            $( "#s_remark" ).html(i18n.dcmvcal.remark);
            $("#savebtn,#closebtn,#deletebtn" ).button();
            $( "#savebtn" ).button( "option", "label", i18n.dcmvcal.i_save );
            $( "#closebtn" ).button( "option", "label", i18n.dcmvcal.i_close );
            $( "#deletebtn" ).button( "option", "label", i18n.dcmvcal.i_delete );
            $("#savebtn").click(function() {
                $("#fmEdit").submit();
            });
            $("#closebtn").click(function() { $('#cpmvcmodalResponse').modal('hide'); });
            deleteEvent = function(){
                var param = [{ "name": "calendarId", value: <?php echo isset($event)?intval($event["id"]):0; ?>},{ "name": "rruleType", value:$( "#rruleType" ).val() }];
                    $.ajaxSetup({
                       jsonp: null,
                       jsonpCallback: null
                    });
                    $.post(DATA_FEED_URL + "&method=remove",
                        param,
                        function(data){
                              if (data.IsSuccess) {
                                    $('#cpmvcmodalResponse').modal('hide');
                                }
                                else
                                    alert(i18n.dcmvcal.error_occurs+ ".\r\n" + ((data.Msg=='OVERLAPPING')?i18n.dcmvcal.error_overlapping:data.Msg));
                        }
                    ,"json");
            }
            $("#deletebtn").click(function() {
<?php if (isset($event) && ($event["rrule"]!="")) { ?>
                $("#repeatdelete").dialog({modal: true,resizable: false,maxWidth: 420,fluid: true,open: function(event, ui){fluidDialog();},width:420}).parent().addClass("mv_dlg").addClass("mv_dlg_editevent").addClass("infocontainer") ;
<?php } else { ?>
                 if (confirm(i18n.dcmvcal.are_you_sure_delete)) {
                    deleteEvent();
                }
<?php } ?>
            });

           //$("#stpartdate,#etpartdate").datepicker({ picker: "<button class='calpick'></button>",});
              var arrs = new Array
              arrs[i18n.dcmvcal.dateformat.year_index] = "yy";
              arrs[i18n.dcmvcal.dateformat.month_index] = "mm";
              arrs[i18n.dcmvcal.dateformat.day_index] = "dd";
              var dateFormat = arrs.join(i18n.dcmvcal.dateformat.separator);
              var dates = $( "#stpartdate, #etpartdate" ).datepicker({numberOfMonths: 1,
              dateFormat: dateFormat,
              monthNamesShort:__MonthName,
              monthNames:__MonthNameLarge,
              dayNamesShort:__WDAY,
              dayNamesMin:__WDAY2,
              firstDay: <?php echo (isset($myGET["weekstartday"]))?intval($myGET["weekstartday"]):1;?>,
			  changeMonth: true,
			  showOn: "button",
			  onSelect: function( selectedDate ) {
			        $(".ui-datepicker").css("display","block");
			  	 var option = this.id == "stpartdate" ? "minDate" : "maxDate",
			  	 	instance = $( this ).data( "datepicker" ),
			  	 	date = $.datepicker.parseDate(
			  	 		instance.settings.dateFormat ||
			  	 		$.datepicker._defaults.dateFormat,
			  	 		selectedDate, instance.settings );
			  	 dates.not( this ).datepicker( "option", option, date );
			  }
		      });
            var cv =$("#colorvalue").val() ;
            if(cv=="")
            {
                cv="#<?php echo esc_attr(sanitize_key($myGET["paletteDefault"]));?>";
            }
            $("#calendarcolor").colorselect({ title: i18n.dcmvcal.color, index: cv, hiddenid: "colorvalue",colors:<?php echo json_encode($palette);?>,paletteDefault:"<?php echo esc_attr(sanitize_key($myGET["paletteDefault"]));?>" });
            //to define parameters of ajaxform

            var options = {
                beforeSubmit: function() {
                    return true;
                },
                jsonp: null,
                jsonpCallback: null,

                dataType: "json",
                success: function(data) {
                    //alert(data.Msg);
                    if (data.IsSuccess) {
                        $('#cpmvcmodalResponse').modal('hide');
                    }
                    else
                        alert(i18n.dcmvcal.error_occurs+ ".\r\n" + ((data.Msg=='OVERLAPPING')?i18n.dcmvcal.error_overlapping:data.Msg));
                }
            };
            $("#r_save_one","#r_save_following","#r_save_all","#r_save_cancel","#r_delete_one","#r_delete_following","#r_delete_all","#r_delete_cancel" ).button();
            $("#r_save_one").click(function() {
                $("#rruleType").val("only");
                $("#repeatsave").dialog('close');
                $("#fmEdit").ajaxSubmit(options);
            });
            $("#r_save_following").click(function() {
                value = $("#stpartdatelast").val();
                var arrs = value.split("/");
                var endDate = new Date(arrs[2], arrs[0]-1, arrs[1]);
                var endDate = DateAdd("d", -1, endDate);
                $("#rruleType").val("UNTIL="+timeToUntilString(endDate));
                $("#repeatsave").dialog('close');
                $("#fmEdit").ajaxSubmit(options);
            });
            $("#r_save_all").click(function() {
                $("#rruleType").val("all");
                $("#repeatsave").dialog('close');
                $("#fmEdit").ajaxSubmit(options);
            });
            $("#r_save_cancel").click(function() {
                $("#repeatsave").dialog('close');
            });
            $("#r_delete_one").click(function() {
                var arrs = $("#stpartdate").val().split(i18n.dcmvcal.dateformat.separator);
                var year = arrs[i18n.dcmvcal.dateformat.year_index];
                var month = arrs[i18n.dcmvcal.dateformat.month_index];
                var day = arrs[i18n.dcmvcal.dateformat.day_index];
                $("#stpartdatelast").val([month,day,year].join("/"));

                $("#rruleType").val("del_only,"+$("#stpartdatelast").val());
                $("#repeatdelete").dialog('close');
                deleteEvent();
            });
            $("#r_delete_following").click(function() {

                var arrs = $("#stpartdate").val().split(i18n.dcmvcal.dateformat.separator);
                var year = arrs[i18n.dcmvcal.dateformat.year_index];
                var month = arrs[i18n.dcmvcal.dateformat.month_index];
                var day = arrs[i18n.dcmvcal.dateformat.day_index];
                $("#stpartdatelast").val([month,day,year].join("/"));

                value = $("#stpartdatelast").val();
                var arrs = value.split("/");
                var endDate = new Date(arrs[2], arrs[0]-1, arrs[1]);
                var endDate = DateAdd("d", -1, endDate);
                $("#rruleType").val("del_UNTIL="+timeToUntilString(endDate));
                $("#repeatdelete").dialog('close');
                deleteEvent();
            });
            $("#r_delete_all").click(function() {
                $("#rruleType").val("del_all");
                $("#repeatdelete").dialog('close');
                deleteEvent();
            });
            $("#r_delete_cancel").click(function() {
                $("#repeatdelete").dialog('close');
            });
            $.validator.addMethod("date", function(value, element) {
                var arrs = value.split(i18n.dcmvcal.dateformat.separator);
                var year = arrs[i18n.dcmvcal.dateformat.year_index];
                var month = arrs[i18n.dcmvcal.dateformat.month_index];
                var day = arrs[i18n.dcmvcal.dateformat.day_index];
                var standvalue = [year,month,day].join("-");

                var r = this.optional(element) || /^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1,3-9]|1[0-2])[\/\-\.](?:29|30))(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1,3,5,7,8]|1[02])[\/\-\.]31)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])[\/\-\.]0?2[\/\-\.]29)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:16|[2468][048]|[3579][26])00[\/\-\.]0?2[\/\-\.]29)(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?: \d{1,3})?)?$|^(?:(?:1[6-9]|[2-9]\d)?\d{2}[\/\-\.](?:0?[1-9]|1[0-2])[\/\-\.](?:0?[1-9]|1\d|2[0-8]))(?: (?:0?\d|1\d|2[0-3])\:(?:0?\d|[1-5]\d)\:(?:0?\d|[1-5]\d)(?:\d{1,3})?)?$/.test(standvalue);
                if (r)
                {
                    $("#"+element.id+"last").val([month,day,year].join("/"));
                }
                return r;
            }, i18n.dcmvcal.invalid_date_format);
            $.validator.addMethod("time", function(value, element) {
                if (__MilitaryTime)
                    var r =  this.optional(element) || /^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/.test(value);
                else
                    var r =  this.optional(element) || /^(0[0-9]|1[0-2]):([0-5][0-9](am|pm))$/.test(value);
                if (r)
                {
                    if (__MilitaryTime)
                        $("#"+element.id+"last").val($("#"+element.id).val());
                    else
                    {

                        var v = $("#"+element.id).val();
                        if (v.indexOf("am")!=-1)
                            v = v.replace("am","");
                        else
                        {
                            v = v.replace("pm","");
                            var d = v.split(":");
                            v = ((parseInt(d[0]*1)==12)?12:(parseInt(d[0]*1)+12))+":"+d[1];
                        }
                        $("#"+element.id+"last").val(v);
                    }
                }
                return r;
            }, i18n.dcmvcal.invalid_time_format);
            $.validator.addMethod("safe", function(value, element) {
                return this.optional(element) || /^[^$\<\>]+$/.test(value);
            }, i18n.dcmvcal._simbol_not_allowed);
            $("#fmEdit").validate({
                submitHandler: function(form) {
                if ( typeof tinymce !== 'undefined' )                              
                    $("#Description").val(tinymce.get("Description").getContent());
//
<?php if (isset($event) && ($event["rrule"]!="")) { ?>
$("#repeatsave").dialog({modal: true,resizable: false,maxWidth: 420,fluid: true,open: function(event, ui){fluidDialog();},width:420}).parent().addClass("mv_dlg").addClass("mv_dlg_editevent").addClass("infocontainer") ;
<?php } else { ?>
                

                $("#fmEdit").ajaxSubmit(options);
<?php } ?>

                },
                errorElement: "div",
                errorClass: "cusErrorPanel",
                errorPlacement: function(error, element) {
                    showerror(error, element);
                }
            });
            function showerror(error, target) {
                var pos = target.position();
                var height = target.height();
                var newpos = { left: pos.left, top: pos.top + height + 2 }
                var form = $("#fmEdit");
                error.appendTo(form).css(newpos);
            }


        });
    </script>
<script>
$( document ).ready(function() {   
        $('#cpmvcmodalResponse').modal('show');
        $('#cpmvcmodalResponse').bind('hidden.bs.modal', function () {
            var div = $('#editEvent', window.parent.document);            
            $('#gridcontainer'+div.attr("op"), window.parent.document).reload();
            div.remove();
        });
        $("#stparttime").val("<?php echo $stparttime;?>");
        $("#etparttime").val("<?php echo $etparttime;?>");
        function createcleditor()
        { 
            function setf()
            {
                if ($(".cleditorMain iframe").length)
                {
                    $(".cleditorMain iframe").css("width","100%").css("height","150px");
                    $('#Subject').focus();
                }
                else
                    setf(); 
            }
            $("#Description").cleditor({width:"100%",
                controls:     // controls to add to the toolbar
                    "bold italic underline strikethrough | removeformat | bullets numbering | outdent " +
                    "indent | alignleft center alignright justify |  " +
                    "image link unlink | source",
                     height:150, useCSS:true})[0].focus();
            setTimeout(setf,100)
        }
        try {
        if ( typeof tinymce !== 'undefined' ) {           
           tinymce.remove();
           tinymce.init( {
               mode : "exact",
               elements : 'Description',
               theme: "modern",
               skin: "lightgray",
               menubar : false,
               statusbar : false,
               convert_urls:false,
               toolbar: [
                   "bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image fullscreen code"
               ],
               plugins : "paste link image fullscreen code",
               paste_auto_cleanup_on_paste : true,
               paste_postprocess : function( pl, o ) {
                   o.node.innerHTML = o.node.innerHTML.replace( /&nbsp;+/ig, " " );
               }
           } );
		}
		else
		   setTimeout(createcleditor,100);
		} catch (e) { }   
		  
});
    
</script>
</html>
<?php
exit();
?>