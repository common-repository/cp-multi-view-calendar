<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


define("CPMV_JC_NO_OVERLAPPING_TIME",false);
define("CPMV_JC_NO_OVERLAPPING_SUBJECT",false);
define("CPMV_JC_NO_OVERLAPPING_LOCATION",false);

if (!empty($_GET["id"]))
    $_GET["id"] = intval($_GET["id"]);
if (!empty($_GET["calendarId"]))
    $_GET["calendarId"] = intval(@$_GET["calendarId"]);

$method = sanitize_text_field($_GET["method"]);
if (!empty($_GET["calid"]))
    $calid = intval($_GET["calid"]);

switch ($method) {
    case "add":
        check_ajax_referer( "cp_multiviewmain", 'security' );
        confirmEditionAccess();       
        $ret = addCalendar($calid, $this->get_param("CalendarStartTime"), $this->get_param("CalendarEndTime"), wp_unslash ($this->get_param("CalendarTitle")), $this->get_param("IsAllDayEvent"), wp_unslash(sanitize_text_field($this->get_param("location"))));
        break;
    case "list":
        if ('list' == $_POST["viewtype"])
            $ret = listCalendarByPage($calid, sanitize_text_field($_POST["list_start"]), sanitize_text_field($_POST["list_end"]), sanitize_text_field($_POST["list_order"]), sanitize_text_field($_POST["list_eventsPerPage"]), sanitize_text_field($_POST["lastdate"]));
        else
        {
            $d1 = js2PhpTime($this->get_param("startdate"));
            $d2 = js2PhpTime($this->get_param("enddate"));
            
            $d1 = mktime(0, 0, 0,  date("m", $d1), date("d", $d1), date("Y", $d1));
            $d2 = mktime(0, 0, 0, date("m", $d2), date("d", $d2), date("Y", $d2))+24*60*60-1;
            $ret = listCalendarByRange($calid, ($d1),($d2));
        }
        break;
    case "update":
        check_ajax_referer( "cp_multiviewmain", 'security' );
        cpmvc_confirm_basic();
        $ret = updateCalendar( intval($this->get_param("calendarId")), $this->get_param("CalendarStartTime"), $this->get_param("CalendarEndTime"));
        break;
    case "remove":
        check_ajax_referer( "cp_multiviewmain", 'security' );
        cpmvc_confirm_basic();
        $ret = removeCalendar( intval($this->get_param("calendarId")),$this->get_param("rruleType"));
        break;
    case "adddetails":
        check_ajax_referer( "cp_multiviewmain", 'security' );
        cpmvc_confirm_basic();
        $st = $this->get_param("stpartdatelast") . " " . $this->get_param("stparttimelast");
        $et = $this->get_param("etpartdatelast") . " " . $this->get_param("etparttimelast");
        if($this->get_param("id")!=""){

            $ret = updateDetailedCalendar(intval($this->get_param("id")), $st, $et,
                sanitize_text_field(wp_unslash($this->get_param("Subject"))), ($this->get_param("IsAllDayEvent")==1)?1:0, wp_unslash($this->get_param('Description')) ,
                wp_unslash(sanitize_text_field($this->get_param("Location"))), $this->get_param("colorvalue"), $this->get_param("rrule"),$this->get_param("rruleType"), $this->get_param("timezone"));
        }else{

            $ret = addDetailedCalendar($calid, $st, $et,sanitize_text_field(wp_unslash($this->get_param("Subject"))), ($this->get_param("IsAllDayEvent")==1)?1:0, wp_unslash($this->get_param('Description')) ,
                wp_unslash(sanitize_text_field($this->get_param("Location"))), $this->get_param("colorvalue"), $this->get_param("rrule"),0, $this->get_param("timezone"));
        }
        break;


}
echo json_encode($ret);
function confirmEditionAccess() 
{
    if (!checkEditionAccess())
    {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = 'Access to edition not allowed. In order to be able to edit/change events data from the public page you have to be authenticated (logged in) in the website with some WordPress account. It can be an authentication in the public side of the website, it doesn\'t have to be with access to the back end.';
        echo ''.json_encode($ret);
        exit;
    }
}
function cpmvc_confirm_basic()
{
    if (!current_user_can('read'))
    {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = 'Access to edition not allowed. In order to be able to edit/change events data from the public page you have to be authenticated (logged in) in the website with some WordPress account. It can be an authentication in the public side of the website, it doesn\'t have to be with access to the back end.';
        echo ''.json_encode($ret);
        exit;
    }
}
function checkEditionAccess() 
{    
    if ($viewid = intval(@$_GET["v"]))
        return checkEditionforView($viewid);
    else
        return current_user_can('edit_posts');
}
function checkEditionforView($viewid)
{
    global $wpdb;
    $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."dc_mv_views WHERE id=%d", $viewid ) );
    if (!count($myrows))
        return false;
    else
        return ($myrows[0]->edition == 'true');
}
function checkIfOverlappingThisEvent($id, $st, $et)
{
    global $wpdb;

    $handle = $wpdb->get_results( $wpdb->prepare( "select * from `".$wpdb->prefix."dc_mv_events"."` where id=%d", $id ) );
    if ( $handle )
        return checkIfOverlapping($handle[0]->calid, $st, $et, $handle[0]->title, $handle[0]->isalldayevent, $handle[0]->location,$id);
    else
        return true;
}
function checkIfOverlapping($calid, $st, $et, $sub, $ade, $loc,$id)
{
    global $wpdb;    
    $sd = date("Y-m-d H:i:s",js2PhpTime($st));
    $ed = date("Y-m-d H:i:s",js2PhpTime($et));
    $condition = "";  
    if (CPMV_JC_NO_OVERLAPPING_TIME)
        $condition .= " and ( (`starttime` > '"
      .esc_sql($sd)."' and `starttime` < '". esc_sql($ed)."') or (`endtime` > '"
      .esc_sql($sd)."' and `endtime` < '". esc_sql($ed)."') or (`starttime` <= '"
      .esc_sql($sd)."' and `endtime` >= '". esc_sql($ed)."') or (`starttime` >= '"
      .esc_sql($sd)."' and `endtime` <= '". esc_sql($ed)."') or  (".(($ade==1)?"":"isalldayevent=1 and ")." (SUBSTRING(`starttime`,1,10)= '".esc_sql(substr($sd,0,10))."' or SUBSTRING(`endtime`,1,10)= '".esc_sql(substr($ed,0,10))."' ))   )   ";
    if (CPMV_JC_NO_OVERLAPPING_SUBJECT)
        $condition .= " and ( `title` = '". esc_sql($sub)."' )   ";
    if (CPMV_JC_NO_OVERLAPPING_LOCATION)
        $condition .= " and ( `location` = '". esc_sql($loc)."' )   ";
    if ($condition=="")
        $condition = " and 1=0";   
             
    $handle = $wpdb->get_results( $wpdb->prepare("select * from `".$wpdb->prefix."dc_mv_events"."` where calid=%d ".$condition, $calid) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    if (!$handle || (count($handle)==1 && $handle[0]->id==$id))
        return true;
    else
        return false;

}
function getMessageOverlapping()
{
    $ret = array();
    $ret['IsSuccess'] = false;
    $ret['Msg'] = "OVERLAPPING";
    return $ret;
}
function addCalendar($calid, $st, $et, $sub, $ade, $loc){
  global $wpdb;  
  $ret = array(); 
  $user = wp_get_current_user();
  try{
    if (checkIfOverlapping($calid, $st, $et,$sub, $ade, $loc,0))
    {
    
    if ($wpdb->query($wpdb->prepare("insert into `".$wpdb->prefix."dc_mv_events"."` (`calid`,`title`, `starttime`, `endtime`, `isalldayevent`, `location`, `owner`, `published`) values (%s,%s,%s,%s,%s,%s, %d,1)" ,$calid,$sub,php2MySqlTime(js2PhpTime($st)),php2MySqlTime(js2PhpTime($et)),$ade,$loc,$user->ID))=== FALSE){
      $ret['IsSuccess'] = false;
      $ret['Msg'] = $wpdb->last_error;
    }else{
      $ret['IsSuccess'] = true;
      $ret['Msg'] = 'add success';
      $ret['Data'] = $wpdb->insert_id;
    }
    }
    else
     $ret = getMessageOverlapping();

	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }

  return $ret;
}


function addDetailedCalendar($calid, $st, $et, $sub, $ade, $dscr, $loc, $color, $rrule,$uid,$tz){
  global $wpdb;
  $ret = array();

  $user = wp_get_current_user();
  try{
    if (checkIfOverlapping($calid, $st, $et,$sub, $ade, $loc,0))
    {
      
    if ($wpdb->query( $wpdb->prepare( "insert into `".$wpdb->prefix."dc_mv_events"."` (`calid`,`title`, `starttime`, `endtime`, `isalldayevent`, `description`, `location`, `color`,`rrule`,`uid`,`owner`, `published`) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,1)", $calid,$sub,php2MySqlTime(js2PhpTime($st)),php2MySqlTime(js2PhpTime($et)), $ade,$dscr,$loc,$color,$rrule,$uid,$user->ID  ) ) === FALSE){
      $ret['IsSuccess'] = false;
      $ret['Msg'] = $wpdb->last_error;
    }else{
      $ret['IsSuccess'] = true;
      $ret['Msg'] = 'add success';
      $ret['Data'] = $wpdb->insert_id;
    }
    }
    else
     $ret = getMessageOverlapping();
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}
function listCalendarByPage($calid, $list_start, $list_end, $list_order, $list_eventsPerPage, $lastdate){
  global $wpdb;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret['error'] = null;
  $ret["start"] = "";
  $ret["end"] = "";  
  try{
  $cond = CPMV_DC_MV_CAL_IDCAL."=".intval($calid)." and (rrule='' or rrule is null)";
  if ($list_start!="")
  {
      if ($list_order=="asc")
      {
          $cond .= " and (`starttime`>='".date("Y-m-d H:i:s",strtotime($list_start))."')"; 
          $ret["start"] = strtotime($list_start);
      }
      else
      {
          $cond .= " and (`starttime`<='".date("Y-m-d H:i:s",strtotime($list_start))."')"; 
          $ret["end"] = strtotime($list_start);
      }
  }    
  if ($list_end!="")
  {
      if ($list_order=="asc")
      {
          $cond .= " and (`endtime`<='".date("Y-m-d H:i:s",strtotime($list_end))."')";    
          $ret["end"] = strtotime($list_end);
      }
      else
      {
          $cond .= " and (`endtime`>='".date("Y-m-d H:i:s",strtotime($list_end))."')"; 
          $ret["start"] = strtotime($list_end);
      }
      
  }    
  if ($lastdate!="")
  {
      if ($list_order=="asc")
      {
          $cond .= " and (`starttime`>='".date("Y-m-d H:i:s",strtotime($lastdate))."')";
          $ret["start"] = strtotime($lastdate);
      }    
      else
      {
          $cond .= " and (`starttime`<='".date("Y-m-d H:i:s",strtotime($lastdate))."')";      
          $ret["end"] = strtotime($lastdate);
      }   
  }    
  $sql = "select * from `".$wpdb->prefix."dc_mv_events"."` where ".$cond." order by  starttime ".(strtolower($list_order)=='asc'?'asc':'desc')."";  
  $rows2 = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared  

  $rows1 = $wpdb->get_results($wpdb->prepare( "select * from `".$wpdb->prefix."dc_mv_events"."` where calid=%d and rrule<>''",$calid ));
  $rows = array_merge($rows1,$rows2);
    if (!$rows){
          $ret['IsSuccess'] = false;
          $ret['Msg'] = $wpdb->last_error;
    }


    $str = "";
    for ($i=0;$i<count($rows);$i++)
    {
        $row = $rows[$i];
        //
        if ($list_order=="desc")
        {
            if ($row->rrule=="" && ($ret["start"]=="" || $ret["start"]!="" && strtotime($row->starttime)<$ret["start"]))
                $ret["start"] = strtotime($row->starttime);
            if ($row->rrule=="" && ($ret["end"]=="" || $ret["end"]!="" && strtotime($row->endtime)>$ret["end"]))
                $ret["end"] = strtotime($row->endtime);
        }    
        $row = $rows[$i];
        if (strlen($row->exdate)>0)
            $row->rrule .= ";exdate=".$row->exdate;
        $ev = array(
            $row->id,
            $row->title,
            php2JsTime(mySql2PhpTime($row->starttime)),
            php2JsTime(mySql2PhpTime($row->endtime)),
            $row->isalldayevent,
            0, //more than one day event
            //$row->InstanceType,
            ((is_numeric($row->uid) && $row->uid>0)?$row->uid:$row->rrule),//Recurring event rule,
            $row->color,
            1,//editable
            $row->location,
            '',//$attends
            $row->description,
            $row->owner,
            $row->published
        );
        $ret['events'][] = $ev;
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  if ($ret["start"]!="") $ret["start"] = date("m/d/Y H:i",$ret["start"]);
  if ($ret["end"]!="") $ret["end"] = date("m/d/Y H:i",$ret["end"]);
  if ($list_order=="desc" && $ret["end"]=="") $ret["end"] = date("m/d/Y H:i");
  //if ($list_order=="desc" && $ret["start"]=="") $ret["start"] = date("m/d/Y H:i");
  return $ret;
}
function listCalendarByRange($calid,$sd, $ed){
  global $wpdb;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;  
  try{  

    $rows = $wpdb->get_results($wpdb->prepare(  "select * from `".$wpdb->prefix."dc_mv_events"."` where calid=%d and ( (`starttime` between %s and %s) or (`endtime` between %s and %s) or (`starttime` <= %s and `endtime` >= %s) or rrule<>'') order by uid desc,  starttime  ",$calid,php2MySqlTime($sd),php2MySqlTime($ed),php2MySqlTime($sd),php2MySqlTime($ed),php2MySqlTime($sd),php2MySqlTime($ed) ));
    if (!$rows){
          $ret['IsSuccess'] = false;
          $ret['Msg'] = $wpdb->last_error;
    }


    $str = "";
    for ($i=0;$i<count($rows);$i++)
    {
        $row = $rows[$i];
        if (strlen($row->exdate)>0)
            $row->rrule .= ";exdate=".$row->exdate;
        $ev = array(
            $row->id,
            $row->title,
            php2JsTime(mySql2PhpTime($row->starttime)),
            php2JsTime(mySql2PhpTime($row->endtime)),
            $row->isalldayevent,
            0, //more than one day event
            //$row->InstanceType,
            ((is_numeric($row->uid) && $row->uid>0)?$row->uid:$row->rrule),//Recurring event rule,
            $row->color,
            1,//editable
            $row->location,
            '',//$attends
            $row->description,
            $row->owner,
            $row->published
        );
        $ret['events'][] = $ev;
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  return $ret;
}
function listCalendar($day, $type){
  $phpTime = js2PhpTime($day);

  switch($type){
    case "month":
      $st = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime)+1, 1, date("Y", $phpTime));
      break;
    case "week":
      //suppose first day of a week is monday
      $monday  =  date("d", $phpTime) - date('N', $phpTime) + 1;

      $st = mktime(0,0,0,date("m", $phpTime), $monday, date("Y", $phpTime));
      $et = mktime(0,0,-1,date("m", $phpTime), $monday+7, date("Y", $phpTime));
      break;
    case "day":
      $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime)+1, date("Y", $phpTime));
      break;
  }

  return listCalendarByRange($st, $et);
}

function updateCalendar($id, $st, $et){
  global $wpdb;
  $ret = array();  
  try{  
    if (checkIfOverlappingThisEvent($id, $st, $et))
    {
        if ($wpdb->query($wpdb->prepare("update `".$wpdb->prefix."dc_mv_events"."` set"
          . " `starttime`=%s, "
          . " `endtime`=%s "
          . "where `id`=%d",php2MySqlTime(js2PhpTime($st)),php2MySqlTime(js2PhpTime($et)),$id))=== FALSE){
          $ret['IsSuccess'] = false;
          $ret['Msg'] = $wpdb->last_error;
        }else{
          $ret['IsSuccess'] = true;
          $ret['Msg'] = 'Succefully';
        }
    }
    else
         $ret = getMessageOverlapping();
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}

function updateDetailedCalendar($id, $st, $et, $sub, $ade, $dscr, $loc, $color, $rrule,$rruleType,$tz){
  global $wpdb;
  $ret = array();  
  $calid = intval($_GET['calid']);
  try{ 
    if (checkIfOverlapping($calid, $st, $et,$sub,$ade,$loc,$id))
    { 
        if ($rruleType=="only")
        { 
            return addDetailedCalendar($calid, $st, $et, $sub, $ade, $dscr, $loc, $color, "",$id,$tz);   
        }        
        else if ($rruleType=="all")
        {
            if ($wpdb->query( $wpdb->prepare( "update `".$wpdb->prefix."dc_mv_events"."` set"
              . " `starttime`=concat(substring(starttime,1,11),%s), "
              . " `endtime`=concat(substring(endtime,1,11),%s), "
              . " `title`=%s, "
              . " `isalldayevent`=%s, "
              . " `description`=%s, "
              . " `location`=%s, "
              . " `color`=%s, "
              . " `rrule`=%s "
              . "where `id`=%d" ,substr(php2MySqlTime(js2PhpTime($st)),-8),substr(php2MySqlTime(js2PhpTime($et)),-8),$sub,$ade,$dscr,$loc,$color,$rrule,$id) )=== FALSE){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $wpdb->last_error;
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }        
        else if (substr($rruleType,0,5)=="UNTIL")
        {            
            $rows = $wpdb->get_results($wpdb->prepare( "select * from `".$wpdb->prefix."dc_mv_events"."` where id=%d", $id ));
            $pre_rrule = $rows[0]->rrule;
            //remove until
            $tmp = explode(";UNTIL=",$pre_rrule);
            if (count($tmp)>1)
            {
                $pre_rrule = $tmp[0];
                $tmp2 = explode(";",$tmp[1]); 
                if (count($tmp2)>1)
                    $pre_rrule .= ";".$tmp2[1]; 
            }
            //add
            $pre_rrule .= ";".$rruleType;
              
            $wpdb->query($wpdb->prepare("update `".$wpdb->prefix."dc_mv_events"."` set"
              . " `rrule`=%s "
              . "where `id`=%d", $pre_rrule,$id));
            return addDetailedCalendar($calid, $st, $et, $sub, $ade, $dscr, $loc, $color, $rrule,0,$tz);
        }
        else 
        {
            if ($wpdb->query($wpdb->prepare("update `".$wpdb->prefix."dc_mv_events"."` set"
              . " `starttime`=%s, "
              . " `endtime`=%s, "
              . " `title`=%s, "
              . " `isalldayevent`=%s, "
              . " `description`=%s, "
              . " `location`=%s, "
              . " `color`=%s, "
              . " `rrule`=%s "
              . "where `id`=%d", php2MySqlTime(js2PhpTime($st)),php2MySqlTime(js2PhpTime($et)),$sub,$ade,$dscr,$loc,$color,$rrule,$id))=== FALSE){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $wpdb->last_error;
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }
    }
    else
        $ret = getMessageOverlapping();
	}catch(Exception $e){	    
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}

function removeCalendar($id,$rruleType){
  global $wpdb;
  $ret = array();  
  try{
        if (substr($rruleType,0,8)=="del_only")
        {
            $rows = $wpdb->get_results($wpdb->prepare( "select * from `".$wpdb->prefix."dc_mv_events"."` where id=%d", $id ));
            $exdate = $rows[0]->exdate.substr($rruleType,8);
            
            if ($wpdb->query($wpdb->prepare( "update `".$wpdb->prefix."dc_mv_events"."` set"
              . " `exdate`=%s "
              . "where `id`=%d",  $exdate, $id ) )=== FALSE){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $wpdb->last_error;
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }  
        else if (substr($rruleType,0,9)=="del_UNTIL")
        {
            $rows = $wpdb->get_results($wpdb->prepare( "select * from `".$wpdb->prefix."dc_mv_events"."` where id=%d", $id ));
            $pre_rrule = $rows[0]->rrule;
            //remove until
            $tmp = explode(";UNTIL=",$pre_rrule);
            if (count($tmp)>1)
            {
                $pre_rrule = $tmp[0];
                $tmp2 = explode(";",$tmp[1]); 
                if (count($tmp2)>1)
                    $pre_rrule .= ";".$tmp2[1]; 
            }
            //add
            $pre_rrule .= ";".substr($rruleType,4);              
            
            if ($wpdb->query($wpdb->prepare("update `".$wpdb->prefix."dc_mv_events"."` set"
              . " `rrule`=%s "
              . "where `id`=%d",$pre_rrule,$id))=== FALSE){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $wpdb->last_error;
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
            
        }
        else  // $rruleType = "del_all" or ""
        {
            if ($wpdb->query($wpdb->prepare("delete from `".$wpdb->prefix."dc_mv_events"."` where `id`=%d" , $id))=== FALSE){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $wpdb->last_error;
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}


exit();
?>