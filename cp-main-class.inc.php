<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CP_MultiViewCalendar extends CP_BaseClass {

    private $calendar = 1;
    private $menu_parameter = 'cp_multiview';
    private $plugin_name = 'CP Multi View Calendar';
    public $prefix = 'cp_multiview';
    private $plugin_URL = 'https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar';
    private $print_counter = 0;
    private $ajax_nonce;
    private $postURL;

    public $shorttag = 'CPMV_CALENDAR';

    function __construct()
    {
        if ( is_admin() ) {
            add_action('wp_ajax_'.$this->prefix.'add_calendar', array($this,'ajax_add_calendar'));
            add_action('wp_ajax_'.$this->prefix.'delete_calendar', array($this,'ajax_delete_calendar'));
            add_action('wp_ajax_'.$this->prefix.'get_views', array($this,'ajax_get_views'));
            
            add_action('wp_ajax_'.$this->prefix.'saveautodel', array($this,'save_autodel'));
        }
        
        if ( ! wp_next_scheduled( 'cpmvc_data_hook' ) ) {
                wp_schedule_event( time(), 'hourly', 'cpmvc_data_hook' );
        }
        add_action( 'cpmvc_data_hook', array( &$this, 'run_data_cleaning' ) );
    }

    function _install() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."dc_mv_calendars (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          title text,
          permissions text,
          owner int(11) default '0',
          ordering int(11) NOT NULL default '0',
          published tinyint(1) NOT NULL default '0',
          checked_out int(11) NOT NULL default '0',
          checked_out_time datetime NOT NULL default '0000-00-00 00:00:00',
          olddatadel int(11) NOT NULL default '0',
          oldmonths int(11) NOT NULL default '0',
          olddays int(11) NOT NULL default '0',
          PRIMARY KEY  (id)
        ) ".$wpdb->get_charset_collate().";");

        
        $wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."dc_mv_events (
          id int(10) unsigned NOT NULL AUTO_INCREMENT,
          calid int(10) unsigned DEFAULT NULL,
          starttime datetime DEFAULT NULL,
          endtime datetime DEFAULT NULL,
          title varchar(255) DEFAULT NULL,
          location varchar(250) DEFAULT NULL,
          rrule varchar(255) DEFAULT NULL,
          exdate text,
          uid int(11),
          description mediumtext,
          isalldayevent tinyint(3) unsigned DEFAULT NULL,
          color varchar(10) DEFAULT NULL,
          owner int(11),
          published tinyint(1),
          PRIMARY KEY  (id)
        ) ".$wpdb->get_charset_collate().";
        ");

        $wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."dc_mv_configuration (
          id int(10) unsigned NOT NULL AUTO_INCREMENT,
          palettes text,
          administration text,
          PRIMARY KEY  (id)
        ) ".$wpdb->get_charset_collate().";
        ");

        $wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."dc_mv_views (
          id int(10) unsigned NOT NULL AUTO_INCREMENT,
          calid int(10) unsigned DEFAULT NULL,
          viewDay text,
          viewWeek text,
          viewMonth text,
          viewNMonth text,
          viewdefault text,
          start_weekday text,
          cssStyle text,
          palette text,
          edition text,
          btoday text,
          bnavigation text,
          brefresh text,
          numberOfMonths text,
          showtooltip text,
          tooltipon text,
          shownavigate text,
          url text,
          target text,
          otherparams text,
          PRIMARY KEY  (id)
        ) ".$wpdb->get_charset_collate().";
        ");

        // insert initial data
        $count = $wpdb->get_var( "SELECT COUNT(id) FROM ".$wpdb->prefix."dc_mv_calendars" );
        if (!$count)
            $wpdb->query("INSERT INTO ".$wpdb->prefix."dc_mv_calendars (id,title,published) VALUES (1,'Calendar 1',1);");
        $count = $wpdb->get_var( "SELECT COUNT(id) FROM ".$wpdb->prefix."dc_mv_configuration" );
        if (!$count)
            $wpdb->query("INSERT INTO ".$wpdb->prefix."dc_mv_configuration (id,palettes,administration) VALUES (1,'a:2:{i:0;a:3:{s:4:\"name\";s:7:\"Default\";s:6:\"colors\";a:70:{i:0;s:3:\"FFF\";i:1;s:3:\"FCC\";i:2;s:3:\"FC9\";i:3;s:3:\"FF9\";i:4;s:3:\"FFC\";i:5;s:3:\"9F9\";i:6;s:3:\"9FF\";i:7;s:3:\"CFF\";i:8;s:3:\"CCF\";i:9;s:3:\"FCF\";i:10;s:3:\"CCC\";i:11;s:3:\"F66\";i:12;s:3:\"F96\";i:13;s:3:\"FF6\";i:14;s:3:\"FF3\";i:15;s:3:\"6F9\";i:16;s:3:\"3FF\";i:17;s:3:\"6FF\";i:18;s:3:\"99F\";i:19;s:3:\"F9F\";i:20;s:3:\"BBB\";i:21;s:3:\"F00\";i:22;s:3:\"F90\";i:23;s:3:\"FC6\";i:24;s:3:\"FF0\";i:25;s:3:\"3F3\";i:26;s:3:\"6CC\";i:27;s:3:\"3CF\";i:28;s:3:\"66C\";i:29;s:3:\"C6C\";i:30;s:3:\"999\";i:31;s:3:\"C00\";i:32;s:3:\"F60\";i:33;s:3:\"FC3\";i:34;s:3:\"FC0\";i:35;s:3:\"3C0\";i:36;s:3:\"0CC\";i:37;s:3:\"36F\";i:38;s:3:\"63F\";i:39;s:3:\"C3C\";i:40;s:3:\"666\";i:41;s:3:\"900\";i:42;s:3:\"C60\";i:43;s:3:\"C93\";i:44;s:3:\"990\";i:45;s:3:\"090\";i:46;s:3:\"399\";i:47;s:3:\"33F\";i:48;s:3:\"60C\";i:49;s:3:\"939\";i:50;s:3:\"333\";i:51;s:3:\"600\";i:52;s:3:\"930\";i:53;s:3:\"963\";i:54;s:3:\"660\";i:55;s:3:\"060\";i:56;s:3:\"366\";i:57;s:3:\"009\";i:58;s:3:\"339\";i:59;s:3:\"636\";i:60;s:3:\"000\";i:61;s:3:\"300\";i:62;s:3:\"630\";i:63;s:3:\"633\";i:64;s:3:\"330\";i:65;s:3:\"030\";i:66;s:3:\"033\";i:67;s:3:\"006\";i:68;s:3:\"309\";i:69;s:3:\"303\";}s:7:\"default\";s:3:\"F00\";}i:1;a:3:{s:4:\"name\";s:9:\"Semaphore\";s:6:\"colors\";a:3:{i:0;s:3:\"F00\";i:1;s:3:\"FF3\";i:2;s:3:\"3C0\";}s:7:\"default\";s:3:\"3C0\";}}','a:15:{s:5:\"views\";a:4:{i:0;s:7:\"viewDay\";i:1;s:8:\"viewWeek\";i:2;s:9:\"viewMonth\";i:3;s:10:\"viewNMonth\";}s:11:\"viewdefault\";s:5:\"month\";s:8:\"language\";s:5:\"en-GB\";s:13:\"start_weekday\";s:1:\"0\";s:8:\"cssStyle\";s:9:\"cupertino\";s:12:\"paletteColor\";s:1:\"0\";s:6:\"btoday\";s:1:\"1\";s:11:\"bnavigation\";s:1:\"1\";s:8:\"brefresh\";s:1:\"1\";s:14:\"numberOfMonths\";s:2:\"12\";s:7:\"sample0\";N;s:7:\"sample1\";s:5:\"click\";s:7:\"sample2\";N;s:7:\"sample3\";s:0:\"\";s:7:\"sample4\";s:10:\"new_window\";}');");

        $count = $wpdb->get_var( "SELECT COUNT(id) FROM ".$wpdb->prefix."dc_mv_views" );
        if (!$count)        
            $wpdb->query("INSERT INTO `".$wpdb->prefix."dc_mv_views` (`id`, `calid`, `viewDay`, `viewWeek`, `viewMonth`, `viewNMonth`, `viewdefault`, `start_weekday`, `cssStyle`, `palette`, `edition`, `btoday`, `bnavigation`, `brefresh`, `numberOfMonths`, `showtooltip`, `tooltipon`, `shownavigate`, `url`, `target`, `otherparams`) VALUES (1, '1', 'true', 'true', 'true', 'true', 'month', '0', 'cupertino', '0', '', '', 'true', '', '6', 'true', '0', '', '', '0', 'militaryTime:false')");
    }
    

    // autodetect language   
    function _autodetect_language()
    {
        $basename = '/DC_MultiViewCal/language/multiview_lang_';
        
        $binfo = str_replace('-','_',get_bloginfo('language'));
        
        $options = array ($binfo,
                          strtolower($binfo),
                          substr(strtolower($binfo),0,2)."_".substr(strtoupper($binfo),strlen(strtoupper($binfo))-2,2),
                          substr(strtolower($binfo),0,2),
                          substr(strtolower($binfo),0,2)."_".strtoupper(substr(strtolower($binfo),0,2)),
                          substr(strtolower($binfo),strlen(strtolower($binfo))-2,2)                      
                          );                     
        foreach ($options as $option)
        {
            if (file_exists(dirname( __FILE__ ).$basename.$option.'.js'))
                return $option;
            $option = str_replace ("-","_", $option);    
            if (file_exists(dirname( __FILE__ ).$basename.$option.'.js'))
                return $option;
        }  
        return '';
    }    


    /* Filter for placing the maps into the contents */
    public function filter_content($atts) {
        global $wpdb;
        extract( shortcode_atts( array(
    		                           'calendar' => '',
    	                        ), $atts ) );
        if ($calendar != '')
            $this->calendar = 1;
        ob_start();
        if ((isset($_GET["action"]) && $_GET["action"] == 'elementor') ||
            (isset($_POST["action"]) && $_POST["action"] == 'elementor_ajax'))
        
            return '<div style="padding: 5px;border: 1px solid #349534; margin: 5px 0;      background: #C9FFCA;color: #008000;font-weight: bold;text-align: center;">Multi View Calendar will appear in this area. Real calendar rendering disabled while editing with Elementor.</div>';
        else
            $this->insert_public_item($atts);
        $buffered_contents = ob_get_contents();
        ob_end_clean();
        return $buffered_contents;
    }

    private function show_preview($id) {
        global $wpdb;

        if (file_exists(dirname( __FILE__ ).'/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js'))
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js', __FILE__);
        else
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_en_GB.js', __FILE__);

        $convert_arr = array(
             'viewday'        =>  'viewDay',
             'viewweek'       =>  'viewWeek',
             'viewmonth'      =>  'viewMonth',
             'viewnmonth'     =>  'viewNMonth',
             'numberofmonths' =>  'numberOfMonths',
             'cssstyle'       =>  'cssStyle',
             'militarytime'   =>  'militaryTime',
             'useradd'        =>  'userAdd',
             'useredit'       =>  'userEdit',
             'userdel'        =>  'userDel',
             'usereditowner'  =>  'userEditOwner',
             'userdelowner'   =>  'userDelOwner',
             'userowner'      =>  'userOwner',
             'palettedefault' =>  'paletteDefault'
         );

        $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."dc_mv_views WHERE id=%d", $id ) );

        $base_params = array ();
        $base_params['id'] = $myrows[0]->calid;
        if ($myrows[0]->viewDay == 'true') $base_params['viewDay'] = $myrows[0]->viewDay;
        if ($myrows[0]->viewWeek == 'true') $base_params['viewWeek'] = $myrows[0]->viewWeek;
        if ($myrows[0]->viewMonth == 'true') $base_params['viewMonth'] = $myrows[0]->viewMonth;
        if ($myrows[0]->viewNMonth == 'true') $base_params['viewNMonth'] = $myrows[0]->viewNMonth;
        $base_params['viewdefault'] = $myrows[0]->viewdefault;
        $base_params['start_weekday'] = $myrows[0]->start_weekday;
        $base_params['cssStyle'] = $myrows[0]->cssStyle;
        if ('' == $base_params['cssStyle']) $base_params['cssStyle'] = 'cupertino';
        $base_params['palette'] = $myrows[0]->palette;        
        if ($myrows[0]->edition == 'true') $base_params['edition'] = $myrows[0]->edition;
        if ($myrows[0]->btoday == 'true') $base_params['btoday'] = $myrows[0]->btoday;
        if ($myrows[0]->bnavigation == 'true') $base_params['bnavigation'] = $myrows[0]->bnavigation;
        if ($myrows[0]->brefresh == 'true') $base_params['brefresh'] = $myrows[0]->brefresh;
        $base_params['numberOfMonths'] = $myrows[0]->numberOfMonths;
        if ($myrows[0]->showtooltip == 'true') $base_params['showtooltip'] = $myrows[0]->showtooltip;
        $base_params['tooltipon'] = $myrows[0]->tooltipon;
        if ($myrows[0]->showtooltip == 'true')
                if ($myrows[0]->tooltipon!=1)
                    $base_params['showtooltipdwm_mouseover'] = 'true';
                else
                    $base_params['showtooltipdwm'] = 'true';
        if ($myrows[0]->shownavigate == 'true') $base_params['shownavigate'] = $myrows[0]->shownavigate;
        $base_params['url'] = $myrows[0]->url;
        $base_params['target'] = $myrows[0]->target;
        $base_params['otherparams'] = $myrows[0]->otherparams;


        $params = "";
        foreach ($base_params as $item => $value)
        {
            if (isset($convert_arr[$item]))
                $item = $convert_arr[$item];
            $item = str_replace(array('"', "'"),array('\\"', "\\'"),$item);
            $value = str_replace("\n",'\\\\n',$value);
            if (is_numeric($value) || $value == 'true' || $value == 'false')
                $params .= ',"'.$item.'":'.$value;
            else
                $params .= ',"'.$item.'":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$value).'"';
        }
        $params = '{'.substr($params,1).'}';
       //$params = str_replace(array('"', "'"),array('\\"', "\\'"),$params);
        $this->print_counter = 0;
        $plugin_url = plugins_url('', __FILE__);

        $prefix_ui = '';
        if (@file_exists('./wp-includes/js/jquery/ui/jquery.ui.core.min.js'))
            $prefix_ui = 'jquery.ui.';
        
        if (file_exists(dirname( __FILE__ ).'/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js'))
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js', __FILE__);
        else
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_en_GB.js', __FILE__);


        wp_register_script('cpmvc-common', plugins_url('/DC_MultiViewCal/src/Plugins/Common.js', __FILE__));
        wp_register_script('cpmvc-underscore', plugins_url('/DC_MultiViewCal/src/Plugins/underscore.js', __FILE__));
        wp_register_script('cpmvc-rrule', plugins_url('/DC_MultiViewCal/src/Plugins/rrule.js', __FILE__));

        wp_register_script('cpmvc-lang', $langscript);

        wp_register_script('cpmvc-jqcalendar', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.calendar.js', __FILE__));
        wp_register_script('cpmvc-jqalert', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.alert.js', __FILE__));
        wp_register_script('cpmvc-multiview', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.js', __FILE__));

        $dependencies = array("jquery",
                           "jquery-ui-core",
                           "jquery-ui-dialog", 
                           "jquery-ui-datepicker",
                           'cpmvc-common',
                           'cpmvc-rrule',
                           'cpmvc-lang',
                           'cpmvc-jqcalendar',
                           'cpmvc-jqalert',
                           'cpmvc-multiview'
                           );
        if (!isset($_GET["fl_builder"]))
            $dependencies[] = 'cpmvc-underscore'; 
        
        wp_enqueue_script( 'cpmvc-publicjs', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.public.js', __FILE__),
                           $dependencies,
                           false, true);
                           

        $overwritelang = get_option('CP_MVC_DATEFORMAT',"");            
        if ($overwritelang == '1')
             wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_mmddyyyy.js', __FILE__),array('cpmvc-publicjs'));   
        else if ($overwritelang == '2')
             wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_ddmmyyyy.js', __FILE__),array('cpmvc-publicjs'));             
 
        wp_enqueue_style ( "cpmvc-calendarcustomcss", plugins_url('/DC_MultiViewCal/css/'.$base_params["cssStyle"].'/calendar.css', __FILE__) );
        wp_enqueue_style ( "cpmvc-calendarmainpcss", plugins_url('/DC_MultiViewCal/css/main.css', __FILE__) );


        wp_localize_script('cpmvc-publicjs', 'cpmvc_configmultiview0', array('obj'  	=>
              '{"params":'.$params.',
               "ajax_url":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$this->get_site_url()).'/?cpmvc_do_action=mvparse&v='.$atts["view"].'",
               "calendar":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$base_params["id"]).'"
               }'
           )); 
           
        ?>
<!DOCTYPE html>
<html>
<head><title>CP Multi View Event Calendar - Preview</title></head>
<body>
<?php wp_head(); ?>
<?php require_once dirname( __FILE__ ) . '/php/list.inc.php'; ?>
<script type="text/javascript">//<!--
<?php 
 print_sanitized_arrayJSList(); 
 ?>
//-->
</script>
<?php @include dirname( __FILE__ ) . '/cp-public-int.inc.php'; ?>

</body>
<?php wp_footer(); ?>
</html>
        <?php 
        exit;
    }


    public function insert_public_item($atts) {
        global $wpdb;

        $convert_arr = array(
             'viewday'        =>  'viewDay',
             'viewweek'       =>  'viewWeek',
             'viewmonth'      =>  'viewMonth',
             'viewnmonth'     =>  'viewNMonth',
             'numberofmonths' =>  'numberOfMonths',
             'cssstyle'       =>  'cssStyle',
             'militarytime'   =>  'militaryTime',
             'useradd'        =>  'userAdd',
             'useredit'       =>  'userEdit',
             'userdel'        =>  'userDel',
             'usereditowner'  =>  'userEditOwner',
             'userdelowner'   =>  'userDelOwner',
             'userowner'      =>  'userOwner',
             'palettedefault' =>  'paletteDefault'
         );

        if ($atts["view"] != '') {            
            $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."dc_mv_views WHERE id=%d", $atts["view"] ) );
            if (!count($myrows))
            {
                echo '<strong>Warning:</strong> The VIEW ID #<strong>'.intval($atts["view"]).'</strong> isn\'t avaiable in the database. Please be sure to indicate a valid VIEW ID for the CP Multi View Event Calendar in the shortcode.';
                return;
            }
            $base_params = array ();
            $base_params['id'] = $myrows[0]->calid;
            if ($myrows[0]->viewDay == 'true') $base_params['viewDay'] = $myrows[0]->viewDay;
            if ($myrows[0]->viewWeek == 'true') $base_params['viewWeek'] = $myrows[0]->viewWeek;
            if ($myrows[0]->viewMonth == 'true') $base_params['viewMonth'] = $myrows[0]->viewMonth;
            if ($myrows[0]->viewNMonth == 'true') $base_params['viewNMonth'] = $myrows[0]->viewNMonth;
            $base_params['viewdefault'] = $myrows[0]->viewdefault;
            $base_params['start_weekday'] = $myrows[0]->start_weekday;
            $base_params['cssStyle'] = $myrows[0]->cssStyle;
            $base_params['palette'] = $myrows[0]->palette;
            if ($myrows[0]->edition == 'true') $base_params['edition'] = $myrows[0]->edition;
            if ($myrows[0]->btoday == 'true') $base_params['btoday'] = $myrows[0]->btoday;
            if ($myrows[0]->bnavigation == 'true') $base_params['bnavigation'] = $myrows[0]->bnavigation;
            if ($myrows[0]->brefresh == 'true') $base_params['brefresh'] = $myrows[0]->brefresh;
            $base_params['numberOfMonths'] = $myrows[0]->numberOfMonths;
            if ($myrows[0]->showtooltip == 'true') $base_params['showtooltip'] = $myrows[0]->showtooltip;
            $base_params['tooltipon'] = $myrows[0]->tooltipon;
            if ($myrows[0]->showtooltip == 'true')
                if ($myrows[0]->tooltipon!=1)
                    $base_params['showtooltipdwm_mouseover'] = 'true';
                else
                    $base_params['showtooltipdwm'] = 'true';
            if ($myrows[0]->shownavigate == 'true') $base_params['shownavigate'] = $myrows[0]->shownavigate;
            $base_params['url'] = $myrows[0]->url;
            $base_params['target'] = $myrows[0]->target;
            $base_params['otherparams'] = $myrows[0]->otherparams;
        }
        else
            $base_params = $atts;

        $params = "";
        foreach ($base_params as $item => $value)
        {
            if (isset($convert_arr[$item]))
                $item = $convert_arr[$item];
            $item = str_replace(array('"', "'"),array('\\"', "\\'"),$item);
            if (is_numeric($value) || $value == 'true' || $value == 'false')
                $params .= ',"'.$item.'":'.$value;
            else
                $params .= ',"'.$item.'":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$value).'"';
        }
        $params = '{'.substr($params,1).'}';

        if (file_exists(dirname( __FILE__ ).'/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js'))
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js', __FILE__);
        else
            $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_en_GB.js', __FILE__);


        wp_register_script('cpmvc-common', plugins_url('/DC_MultiViewCal/src/Plugins/Common.js', __FILE__));
        wp_register_script('cpmvc-underscore', plugins_url('/DC_MultiViewCal/src/Plugins/underscore.js', __FILE__));
        wp_register_script('cpmvc-rrule', plugins_url('/DC_MultiViewCal/src/Plugins/rrule.js', __FILE__));

        wp_register_script('cpmvc-lang', $langscript);

        wp_register_script('cpmvc-jqcalendar', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.calendar.js', __FILE__));
        wp_register_script('cpmvc-jqalert', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.alert.js', __FILE__));
        wp_register_script('cpmvc-multiview', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.js', __FILE__));

        $dependencies = array("jquery",
                           "jquery-ui-core",
                           "jquery-ui-dialog", 
                           "jquery-ui-datepicker",
                           'cpmvc-common',
                           'cpmvc-rrule',
                           'cpmvc-lang',
                           'cpmvc-jqcalendar',
                           'cpmvc-jqalert',
                           'cpmvc-multiview'
                           );
        if (!isset($_GET["fl_builder"]))
            $dependencies[] = 'cpmvc-underscore'; 
        
        wp_enqueue_script( 'cpmvc-publicjs', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.public.js', __FILE__),
                           $dependencies,
                           false, true);
                           

        $overwritelang = get_option('CP_MVC_DATEFORMAT',"");            
        if ($overwritelang == '1')
             wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_mmddyyyy.js', __FILE__),array('cpmvc-publicjs'));   
        else if ($overwritelang == '2')
             wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_ddmmyyyy.js', __FILE__),array('cpmvc-publicjs'));             
 
        wp_localize_script('cpmvc-publicjs', 'cpmvc_configmultiview'.($this->print_counter), array('obj'  	=>
              '{"params":'.$params.',
               "ajax_url":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$this->get_site_url()).'/?cpmvc_do_action=mvparse&v='.$atts["view"].'",
               "calendar":"'.str_replace(array('"', "'"),array('\\"', "\\'"),$base_params["id"]).'"
    	         }'
           ));       

        wp_enqueue_style ( "cpmvc-calendarcustomcss", plugins_url('/DC_MultiViewCal/css/'.$base_params["cssStyle"].'/calendar.css', __FILE__) );
        wp_enqueue_style ( "cpmvc-calendarmainpcss", plugins_url('/DC_MultiViewCal/css/main.css', __FILE__) );

        @include dirname( __FILE__ ) . '/cp-public-int.inc.php';

        $this->print_counter++;
    }


    /* Code for the admin area */

    public function plugin_page_links($links) {
        $customAdjustments_link = '<a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">'.__('Upgrade To Premium').'</a>';
    	array_unshift($links, $customAdjustments_link);
        $settings_link = '<a href="admin.php?page='.$this->menu_parameter.'_manage">'.__('Settings').'</a>';
    	array_unshift($links, $settings_link);
    	$help_link = '<a href="'.$this->plugin_URL.'?documentation=open">'.__('Help').'</a>';
    	array_unshift($links, $help_link);
    	return $links;
    }

    public function admin_menu() {
        add_options_page($this->plugin_name.' Options', $this->plugin_name, 'manage_options', $this->menu_parameter.'settings', array($this, 'settings_page') );
        add_menu_page( $this->plugin_name.' Options', $this->plugin_name, '', $this->menu_parameter, array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Manage Calendars', 'Manage Calendars', 'edit_pages', $this->menu_parameter."_manage", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Publish Wizard', 'Publish Wizard', 'edit_pages', $this->menu_parameter."_publishwizard", array($this, 'settings_page') );    
        add_submenu_page( $this->menu_parameter, 'Help: Sample Views', 'Help: Sample Views', 'edit_pages', $this->menu_parameter."_samples", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Help: CSS Styles', 'Help: CSS Styles', 'edit_pages', $this->menu_parameter."_css", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Help: Publishing Views', 'Help: Publishing Views', 'edit_pages', $this->menu_parameter."_publishing", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Help: Online Demo', 'Help: Online Demo', 'edit_pages', $this->menu_parameter."_demo", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Export/Import iCal', 'Export/Import iCal', 'edit_pages', $this->menu_parameter."_ical", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Upgrade', 'Upgrade', 'edit_pages', $this->menu_parameter."_upgrade", array($this, 'settings_page') );

        add_meta_box($this->prefix.'box', $this->plugin_name, array($this, 'insertMetaBox'), 'post', 'normal');
        add_meta_box($this->prefix.'box', $this->plugin_name, array($this, 'insertMetaBox'), 'page', 'normal');
    }

    public function insertMetaBox() {
        global $wpdb;
        wp_enqueue_script( "jquery" );
        wp_enqueue_script( "jquery-ui-core" );
        wp_enqueue_script( "jquery-ui-dialog" );
        wp_enqueue_style ( "wp-jquery-ui-dialog" );
        $this->ajax_nonce = wp_create_nonce( $this->prefix );
        @include_once dirname( __FILE__ ) . '/cp-metabox.inc.php';
    }

    private function printViewsList() {
        global $wpdb;
        // print the list of views
        //....
        $seed = date("His").mt_rand(10,100);
        $sanitized_buffer = "";
        $sanitized_buffer_scripts = "";
        $last_id = 0;
        $myrows = $wpdb->get_results( "SELECT ".$wpdb->prefix."dc_mv_views.*,".$wpdb->prefix."dc_mv_calendars.title FROM ".$wpdb->prefix."dc_mv_views INNER JOIN ".$wpdb->prefix."dc_mv_calendars ON ".$wpdb->prefix."dc_mv_views.calid= ".$wpdb->prefix."dc_mv_calendars.id ORDER BY ".$wpdb->prefix."dc_mv_views.id ASC");
        if (count($myrows))
        {
            ?>
            <table cellpadding="5">
             <tr>
               <th align="left" nowrap style="border-bottom:1px dotted black;">View ID</th>
               <th align="left" style="border-bottom:1px dotted black;">Calendar</th>
               <th align="left" style="border-bottom:1px dotted black;">Shortcode</th>
               <th align="left" style="border-bottom:1px dotted black;">Options</th>
             </tr>
             <?php 
                 foreach ($myrows as $item)
                 {
                     echo '<tr>'.
                          '<td>#'.intval($item->id).'</td>'.
                          '<td>'.esc_html($item->title).'</td>'.
                          '<td nowrap style="color:#338833;font-weight:bold;">[CPMV_CALENDAR view="'.intval($item->id).'"]</td>'.
                          '<td>'.
                          '<a class="button" style="" href="javascript:'.esc_attr($this->prefix).'Admin.sendToEditor('.intval($item->calid).','.intval($item->id).');">'.esc_html(__('Publish')).'</a> &nbsp; '.
                          '<a class="button" href="javascript:'.esc_attr($this->prefix).'previewCalendarId('.intval($item->id).');">'.esc_html(__('Preview')).'</a> &nbsp; '.
                          '<a class="button" href="javascript:'.esc_attr($this->prefix).esc_js($seed).'editCalendar'.intval($item->id).'();">'.esc_html(__('Edit')).'</a> &nbsp; '.
                          '<a class="button" href="javascript:'.esc_attr($this->prefix).'deleteCalendar('.intval($item->id).');">'.esc_html(__('Delete')).'</a>'.
                          '</td>'.
                          '</tr>';
                 }

             ?>
            </table>
            <?php 
               echo '<div id="'.esc_attr($this->prefix).'scriptsarea" style="display:none">';
               foreach ($myrows as $item)
               {
                   // generate edit function
                   echo 'function '.esc_html($this->prefix.$seed).'editCalendar'.intval($item->id).'(){'.
                        esc_html($this->prefix).'_sel_sel("id","'.intval($item->calid).'");'.
                        esc_html($this->prefix).'_sel_chk("viewDay","'.esc_js($item->viewDay).'");'.
                        esc_html($this->prefix).'_sel_chk("viewWeek","'.esc_js($item->viewWeek).'");'.
                        esc_html($this->prefix).'_sel_chk("viewMonth","'.esc_js($item->viewMonth).'");'.
                        esc_html($this->prefix).'_sel_chk("viewNMonth","'.esc_js($item->viewNMonth).'");'.
                        esc_html($this->prefix).'_sel_sel("viewdefault","'.esc_js($item->viewdefault).'");'.
                        esc_html($this->prefix).'_sel_sel("start_weekday","'.esc_js($item->start_weekday).'");'.
                        esc_html($this->prefix).'_sel_sel("cssStyle","'.esc_js($item->cssStyle).'");'.
                        esc_html($this->prefix).'_sel_sel("palette","'.esc_js($item->palette).'");'.
                        esc_html($this->prefix).'_sel_chk("edition","'.esc_js($item->edition).'");'.
                        esc_html($this->prefix).'_sel_chk("btoday","'.esc_js($item->btoday).'");'.
                        esc_html($this->prefix).'_sel_chk("bnavigation","'.esc_js($item->bnavigation).'");'.
                        esc_html($this->prefix).'_sel_chk("brefresh","'.esc_js($item->brefresh).'");'.
                        esc_html($this->prefix).'_sel_sel("numberOfMonths","'.esc_js($item->numberOfMonths).'");'.
                        esc_html($this->prefix).'_sel_chk("showtooltip","'.esc_js($item->showtooltip).'");'.
                        esc_html($this->prefix).'_sel_sel("tooltipon","'.esc_js($item->tooltipon).'");'.
                        esc_html($this->prefix).'_sel_chk("shownavigate","'.esc_js($item->shownavigate).'");'.
                        esc_html($this->prefix).'_sel_fld("url","'.esc_js($item->url).'");'.
                        esc_html($this->prefix).'_sel_sel("target","'.esc_js($item->target).'");'.
                        esc_html($this->prefix).'_sel_fld("otherparams","'.esc_html(str_replace("\r","",str_replace("\n","",str_replace('"','\"',$item->otherparams)))).'");'.
                        esc_html($this->prefix).'createNewCalendar('.intval($item->id).');'.
                        '}';
                   $last_id = $item->id;
               }               
               echo (empty($_POST["viewid"]) || intval($_POST["viewid"]) == 0?'document.getElementById("r'.esc_html($this->prefix).'isediting").value="'.intval($last_id).'";':'').
                    '</div>';
               
            ?>
            <?php
        } else {
            echo '<strong>';
            echo esc_html(__('Start creating a new calendar view with the following button:'));
            echo '</strong>';
        }
    }

    public function ajax_get_views() {
        global $wpdb;
        check_ajax_referer( $this->prefix, 'security' );
        $this->printViewsList();
        die();
    }

    private function ajax_get_posted_params() {            
        $rawparams = explode("||||||", stripslashes (sanitize_text_field($_POST["params"])) );
        $params =  array ( 'viewDay' => '',
                           'viewWeek' => '',
                           'viewMonth' => '',
                           'viewNMonth' => '',
                           'edition' => '',
                           'btoday' => '',
                           'bnavigation' => '',
                           'brefresh' => '',
                           'showtooltip' => '',
                           'shownavigate' => '',
                           'otherparams' => '',
                           'url' => ''
                         );
        foreach ($rawparams as $item)
        {
            $item = trim($item);
            if ($item != '')
            {
                $tmppos = strpos($item, "="); //explode("=",$item);
                $tmp = array( substr($item,0,$tmppos), substr($item,$tmppos+1) );
                $params[$tmp[0]] = substr($tmp[1],1,strlen($tmp[1])-2);
            }
        }
        return $params;
    }

    public function ajax_add_calendar() { // this function is also used for updating
        global $wpdb;
        check_ajax_referer( $this->prefix, 'security' );
        // add calendar view
        $params = $this->ajax_get_posted_params();
        
        $format = array();
        for ($i=0;$i<count($params);$i++)
            $format[] = '%s';
        
        if ($_POST["viewid"] == '0')
            $wpdb->insert($wpdb->prefix."dc_mv_views", $params, $format);
        else
            $wpdb->update( $wpdb->prefix."dc_mv_views", $params, array( 'id' => intval($_POST["viewid"]) ), $format, array('%d') );

        $this->printViewsList();
        die();
    }

    public function ajax_delete_calendar() {
        global $wpdb;
        check_ajax_referer( $this->prefix, 'security' );
        // delete calendar view
        $wpdb->delete($wpdb->prefix."dc_mv_views", array( 'id' => intval($_POST["id"]) ));

        $this->printViewsList();
        die();
    }

    public function settings_page() {
        global $wpdb;
        if ($this->get_param("cpmvc_id") && $this->get_param("page") != 'cp_multiview_publishwizard')
        {
            $this->calendar = 1;
            @include_once dirname( __FILE__ ) . '/cp-admin-int.inc.php';
        }
        else if ($this->get_param("page") == 'cp_multiview_publishing')
        {
            @include_once dirname( __FILE__ ) . '/help/publishing.html';
        }    
        else if ($this->get_param("page") == 'cp_multiview_publishwizard')
        {
            @include_once dirname( __FILE__ ) . '/cp-publish-wizzard.inc.php';
        }          
        else if ($this->get_param("page") == 'cp_multiview_samples')
        {
            @include_once dirname( __FILE__ ) . '/help/samples.html';
        }
        else if ($this->get_param("page") == 'cp_multiview_ical')
        {
?>
<br />
  <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Export to iCal files and Import from iCal files</span></h3>
  <div class="inside">
<p>This option is available only in the <a target="_blank" href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">Platinum version of the CP Multi View Calendar</a>.</p>
<p>This feature allows to export the calendar data to an iCal file and import data into the calendar from a iCal file.</p>
<p>The iCal format used is compatible with Google Calendar, Android Calendars and other calendars supporting the iCal standard.</p>
<p>iCal files can be exported/imported separately for each calendar. When using the <a target="_blank" href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download">Platinum version of the CP Multi View Calendar</a> this feature
is located into the "Admin Calendar Data" for each calendar.</p>
</div>
</div>
[<a href="https://wordpress.org/support/plugin/cp-multi-view-calendar#new-post" target="_blank">Support</a>] | [<a href="https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar" target="_blank">Documentation</a>]






<?php
        }
        else if ($this->get_param("page") == 'cp_multiview_css')
        {
            @include_once dirname( __FILE__ ) . '/help/cssstyles.html';
        }
        else if ($this->get_param("page") == 'cp_multiview_upgrade')
        {
            echo("Redirecting to upgrade page...<script type='text/javascript'>document.location='https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#download';</script>");
            exit;
        }
        else if ($this->get_param("page") == 'cp_multiview_demo')
        {
            echo("Redirecting to demo page...<script type='text/javascript'>document.location='https://wordpress.dwbooster.com/calendars/cp-multi-view-calendar#demo';</script>");
            exit;
        }
        else if (isset($_GET["edit"]) && $_GET["edit"] == '1')
            @include_once dirname( __FILE__ ) . '/cp_admin_int_edition.inc.php';
        else 
            @include_once dirname( __FILE__ ) . '/cp-admin-int-list.inc.php';
    }

    function insert_adminScripts($hook) {
        if ($this->get_param("page") == $this->menu_parameter."_manage")
        {
            wp_enqueue_script( "jquery" );
            wp_enqueue_script( "jquery-ui-core" );
            wp_enqueue_script( "jquery-ui-dialog" );
            wp_enqueue_script( "jquery-ui-datepicker" );
            wp_enqueue_script( "jquery-ui-tabs" );
            wp_enqueue_script( "jquery-ui-sortable" );
            wp_enqueue_script( "jquery-ui-calendar" );
            wp_enqueue_script( "jquery-ui-droppable" );
			wp_enqueue_style ( "cpmvc-calendarcss", plugins_url('DC_MultiViewCal/css/cupertino/calendar.css', __FILE__) );
            wp_enqueue_style ( "cpmvc-auxcss", plugins_url('DC_MultiViewCal/configuration/css/cupertino/jquery-ui-1.8.20.custom.css', __FILE__) );
			wp_enqueue_style ( "cpmvc-maincss", plugins_url('DC_MultiViewCal/css/main.css', __FILE__) );
            
            if (file_exists(dirname( __FILE__ ).'/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js'))
                $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_'.$this->_autodetect_language().'.js', __FILE__);
            else
                $langscript = plugins_url('/DC_MultiViewCal/language/multiview_lang_en_GB.js', __FILE__);
            
            
            wp_register_script('cpmvc-common', plugins_url('/DC_MultiViewCal/src/Plugins/Common.js', __FILE__));
            wp_register_script('cpmvc-underscore', plugins_url('/DC_MultiViewCal/src/Plugins/underscore.js', __FILE__));
            wp_register_script('cpmvc-rrule', plugins_url('/DC_MultiViewCal/src/Plugins/rrule.js', __FILE__));
            
            wp_register_script('cpmvc-lang', $langscript);
            
            wp_register_script('cpmvc-jqcalendar', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.calendar.js', __FILE__));
            wp_register_script('cpmvc-jqalert', plugins_url('/DC_MultiViewCal/src/Plugins/jquery.alert.js', __FILE__));
            wp_register_script('cpmvc-multiview', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.js', __FILE__));
            
            $dependencies = array("jquery",
                               "jquery-ui-core",
                               "jquery-ui-dialog", 
                               "jquery-ui-datepicker",
                               'cpmvc-common',
                               'cpmvc-rrule',
                               'cpmvc-lang',
                               'cpmvc-jqcalendar',
                               'cpmvc-jqalert',
                               'cpmvc-multiview'
                               );
            if (!isset($_GET["fl_builder"]))
                $dependencies[] = 'cpmvc-underscore'; 
            
            wp_enqueue_script( 'cpmvc-publicjs', plugins_url('/DC_MultiViewCal/src/Plugins/multiview.public.js', __FILE__),
                               $dependencies,
                               false, true);
                               
            
            $overwritelang = get_option('CP_MVC_DATEFORMAT',"");            
            if ($overwritelang == '1')
                 wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_mmddyyyy.js', __FILE__),array('cpmvc-publicjs'));   
            else if ($overwritelang == '2')
                 wp_enqueue_script( 'cpmvc-lang-overwrite', plugins_url('/DC_MultiViewCal/language/multiview_lang_overwrite_ddmmyyyy.js', __FILE__),array('cpmvc-publicjs'));             
                 
        }
    }
    
    private function publish_on($whereto, $publishpage = '', $publishpost = '', $content = '')
    {
        global $wpdb;
        $id = '';
        if ($whereto == '0' || $whereto =='1') // new page
        {
            $my_post = array(
              'post_title'    => 'Calendar',
              'post_type' => ($whereto == '0'?'page':'post'),
              'post_content'  => 'This is a <b>preview</b> page, remember to publish it if needed. You can edit the calendar events from the admin area or into the below calendar if the "edition" option was marked in the wizzard.<br /><br /> '.$content,
              'post_status'   => 'draft'
            );
            
            // Insert the post into the database
            $id = wp_insert_post( $my_post );
        }
        else 
        {
            $id = ($whereto == '2'?$publishpage:$publishpost);
            $post = get_post( $id );
            $my_post = array(
                  'ID'           => $id,
                  'post_content' => $content.$post->post_content,
              );
            // Update the post into the database
            wp_update_post( $my_post );
        }
        return get_permalink($id);
    }
    
    
    function current_user_has_access()
    {
        global $wpdb;
        if (current_user_can('manage_options'))
            return true;
        else
        {
            $current_user = wp_get_current_user();
            $mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '. $wpdb->prefix.'dc_mv_calendars WHERE id=%d AND owner=%d', $this->calendar, $current_user->ID) );
            return (is_array($mycalendarrows) && count($mycalendarrows));
        }
    }        

    /* hook for checking posted data for the admin area */
    
    function save_autodel()
    {
        global $wpdb;
        check_ajax_referer( $this->prefix, 'security' );
        $wpdb->update($wpdb->prefix."dc_mv_calendars", array ("olddatadel" => intval($_POST["olddatadel"]), "oldmonths" => intval($_POST["oldmonths"]), "olddays" => intval($_POST["olddays"])) , array( 'id' => intval($_POST["calid"]) ) );
        $this->run_data_cleaning();
        echo 'OK';
        die();
    }
    
    
    function run_data_cleaning()
    {
        global $wpdb;
        $mycalendarrows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'dc_mv_calendars WHERE olddatadel=1', $this->calendar ) );
        foreach($mycalendarrows as $item) 
        {
            $olddate = date("Y-m-d H:i:s", strtotime("today -".$item->oldmonths." months -".$item->olddays." days"));
            $wpdb->query("DELETE FROM ".$wpdb->prefix."dc_mv_events WHERE starttime<'".esc_sql($olddate)."' and endtime<'".esc_sql($olddate)."'");
        }
    }

    
    function data_management() {
        global $wpdb;    
    
        $action = sanitize_text_field($this->get_param('cpmvc_do_action'));

    	if (!$action) return; // go out if the call isn't for this one

        if ($this->get_param('cpmvc_id')) $this->calendar = 1;

        if ($action == "mvparse")
        {
            $feed = sanitize_text_field($this->get_param('f'));
            if ($feed == 'datafeed')
            {
                @include_once dirname( __FILE__ ) . '/php/functions.php';
                @include_once dirname( __FILE__ ) . '/php/datafeed.php';
                exit();
            }
            else if ($feed == 'edit')
            {
                 @include_once dirname( __FILE__ ) . '/php/functions.php';
                 @include_once dirname( __FILE__ ) . '/php/edit.php';
                 exit();
            }
        }
        else if ($action == "preview")
        {
            $this->show_preview( intval($_GET["id"]) );
            exit();
        }

        // ...
        echo 'Some unexpected error happened. If you see this error contact the support service at https://wordpress.org/support/plugin/cp-multi-view-calendar#new-post';

        exit();
    }
    
    
   function data_management_loaded() {
        global $wpdb;
    
        $action = sanitize_text_field($this->get_param('cpmvc_do_action_loaded'));
    	if (!$action) return; // go out if the call isn't for this one

        if ($this->get_param('cpmvc_id')) $this->calendar = 1;

        if ($action == "wizard" && wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'cpmvc_update_actions_pwizard') && current_user_can('edit_posts'))
        {
            $wpdb->insert( 
            	$wpdb->prefix."dc_mv_views", 
            	array( 
            		'calid' => intval($_POST["cpmvc_id"]), 
                    'viewDay'  => (isset($_POST["cpmvc_id"])?'true':'false'),
                    'viewWeek' => (isset($_POST["viewWeek"])?'true':'false'),
                    'viewMonth' => (isset($_POST["viewMonth"])?'true':'false'),
                    'viewNMonth' => (isset($_POST["viewNMonth"])?'true':'false'),
                    'viewdefault' => sanitize_text_field($_POST["viewdefault"]),
                    'start_weekday' => sanitize_text_field($_POST["start_weekday"]),
                    'cssStyle' => 'cupertino',
                    'palette' => '0',
                    'edition' => (!empty($_POST["edition"])?sanitize_text_field($_POST["edition"]):''),
                    'btoday' => '',
                    'bnavigation' => 'true',
                    'brefresh' => '',
                    'numberOfMonths' => sanitize_text_field($_POST["numberOfMonths"]),
                    'showtooltip' => (isset($_POST["showtooltip"])?'true':'false'),
                    'tooltipon' => sanitize_text_field($_POST["tooltipon"]),
                    'shownavigate' => '',
                    'url' => '',
                    'target' => '0',
                    'otherparams' => sanitize_text_field($_POST["otherparams"])
            	), 
            	array( 
            		'%d', 
            		'%s', '%s', '%s', '%s', '%s', 
                    '%s', '%s', '%s', '%s', '%s', 
                    '%s', '%s', '%s', '%s', '%s', 
                    '%s', '%s', '%s', '%s' 
            	)
            );
            $shortcode = '[CPMV_CALENDAR view="'.$wpdb->insert_id.'"]';
            $this->postURL = $this->publish_on(sanitize_text_field($_POST["whereto"]), sanitize_text_field($_POST["publishpage"]), sanitize_text_field($_POST["publishpost"]), $shortcode);            
            return;
        }

        // ...
        echo 'Some unexpected error happened. If you see this error contact the support service at https://wordpress.org/support/plugin/cp-multi-view-calendar#new-post';

        exit();
    }    
    

} // end class

?>