<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !is_admin() ) {echo 'Direct access not allowed.';exit;} 

$nonce = wp_create_nonce( 'cpmvc_update_actions_pwizard' );

?>

<h1>Quick Start: Publish Calendar Wizard</h1>

<style type="text/css">
/* Style the form */
#regForm {
  background-color: #ffffff;
  margin: 50px auto;
  padding: 40px;
  width: 70%;
  min-width: 300px;
}

/* Style the input fields */
input, select, textarea {

  width: 100%;
  font-size: 20px;
  font-family: Raleway;
  border: 1px solid #aaaaaa;
}

/* Mark input boxes that gets an error on validation: */
input.invalid {
  background-color: #ffdddd;
}

/* Hide all steps by default: */
.tabcpwizzard {
  display: none;
}

/* Make circles that indicate the steps of the form: */
.step {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbbbbb;
  border: none; 
  border-radius: 50%;
  display: inline-block;
  opacity: 0.5;
}

/* Mark the active step: */
.step.active {
  opacity: 1;
}

/* Mark the steps that are finished and valid: */
.step.finish {
  background-color: #4CAF50;
}

button {
  background-color: #4CAF50;
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  font-size: 20px;
  font-family: Raleway;
  cursor: pointer;
}

button:hover {
  opacity: 0.8;
}

#prevBtn {
  background-color: #bbbbbb;
}

/* Customize the label (the container) */
.container {
  display: block;
  float: left;
  position: relative;
  padding-left: 35px;
  margin-right: 40px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 20px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 25px;
  width: 25px;
  background-color: #eee;
  border: 1px solid #aaaaaa;
}

/* On mouse-over, add a grey background color */
.container:hover input ~ .checkmark {
  background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark {
  background-color: #2196F3;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.container input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.container .checkmark:after {
  left: 9px;
  top: 5px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}

.cpmvcontainer { font-size:18px; }

</style>


<form method="post" action="?page=cp_multiview_publishwizard" name="regForm" id="regForm">          
 <input name="cpmvc_do_action_loaded" type="hidden" value="wizard" />
 <input name="nonce" type="hidden" value="<?php echo esc_attr($nonce); ?>" />

<?php 

if ($this->get_param('cpmvc_do_action_loaded') == 'wizard') {
?>
<h1>Great!, you have created your first calendar view</h1>
<p class="cpmvcontainer">The calendar was placed into the page <a href="<?php echo esc_attr($this->postURL); ?>"><?php echo esc_html($this->postURL); ?></a>.</p>
<p class="cpmvcontainer">Now you can:</p>
<div style="clear:both"></div>
<button type="button" id="nextBtn" onclick="window.open('<?php echo esc_js($this->postURL); ?>');">View the Published Calendar</button>
<div style="clear:both"></div>
<p class="cpmvcontainer">* Note: If the calendar was published in a new page or post it will be a 'draft', you have to publish the page/post in the future if needed.</p>
<div style="clear:both"></div>
<button type="button" id="nextBtn" onclick="window.open('?page=cp_multiview_manage&cpmvc_id=<?php echo intval($this->get_param("cpmvc_id")); ?>');">Edit the Calendar Data</button>
<div style="clear:both"></div>
<p class="cpmvcontainer">* Note: For editing the generated settings or apply more advanced settings please check the <a href="?page=cp_multiview_publishing">general way to create and edit a calendar view</a>.</p>

<?php
} else {     
?>
<!-- One "tab" for each step in the form: -->
<div class="tabcpwizzard"><h1>Select calendar display settings:</h1>
<table class="form-table">
    <tr valign="top" id="nmonthsnum">
        <th scope="row"><label>Calendar</label></th>
        <td><select id="cpmvc_id" name="cpmvc_id">
<?php
  $myrows = $wpdb->get_results( "SELECT * FROM ". $wpdb->prefix."dc_mv_calendars");
  foreach ($myrows as $item)            
      echo '<option value="'.intval($item->id).'">'.esc_html($item->title).'</option>';
?>                
            </select>
        </td>    
    </tr>  
    <tr valign="top">
        <th scope="row"><label>Calendar Views</label></th>					
        <td>
         <label class="container">Day
           <input type="checkbox" id="<?php echo esc_attr($this->prefix); ?>_viewDay" name="viewDay" value="true" checked="checked">
           <span class="checkmark"></span>
         </label>
         <label class="container">Week
           <input type="checkbox" id="<?php echo esc_attr($this->prefix); ?>_viewWeek" name="viewWeek" value="true" checked="checked">
           <span class="checkmark"></span>
         </label>
         <label class="container">Month
           <input type="checkbox" id="<?php echo esc_attr($this->prefix); ?>_viewMonth" name="viewMonth" value="true" checked="checked">
           <span class="checkmark"></span>
         </label>
         <label class="container">nMonth
           <input type="checkbox" id="<?php echo esc_attr($this->prefix); ?>_viewNMonth" name="viewNMonth" value="true" checked="checked" onclick="cpmv_mvpublish_displayviews(this)">
           <span class="checkmark"></span>
         </label>
        </td>
    </tr>   
    <tr valign="top" id="nmonthsnum">
        <th scope="row"><label>Number of Months for nMonths View</label></th>
        <td><select id="<?php echo esc_attr($this->prefix); ?>_numberOfMonths" name="numberOfMonths">
            	<option value="1">1</option>
            	<option value="2">2</option>
            	<option value="3">3</option>
            	<option value="4">4</option>
            	<option value="5">5</option>
            	<option value="6" selected="selected">6</option>
            	<option value="7">7</option>
            	<option value="8">8</option>
            	<option value="9">9</option>
            	<option value="10">10</option>
            	<option value="11">11</option>
            	<option value="12">12</option>
            	<option value="13">13</option>
            	<option value="14">14</option>
            	<option value="15">15</option>
            	<option value="16">16</option>
            	<option value="17">17</option>
            	<option value="18">18</option>
            	<option value="19">19</option>
            	<option value="20">20</option>
            	<option value="21">21</option>
            	<option value="22">22</option>
            	<option value="23">23</option>
            	<option value="24">24</option>
            </select>
        </td>    
    </tr>        
    <tr valign="top">
        <th scope="row"><label>Default View</label></th>
        <td><select id="<?php echo esc_attr($this->prefix); ?>_viewdefault" name="viewdefault">
            	<option value="day">Day</option>
            	<option value="week">Week</option>
            	<option value="month" selected="selected">Month</option>
            	<option value="nMonth">nMonth</option>
            </select>
        </td>    
    </tr>    
    <tr valign="top">
        <th scope="row"><label>Start day of the week</label></th>
        <td><select id="<?php echo esc_attr($this->prefix); ?>_start_weekday" name="start_weekday">
            	<option value="0" selected="selected">Sunday</option>
            	<option value="1">Monday</option>
            	<option value="2">Tuesday</option>
            	<option value="3">Wednesday</option>
            	<option value="4">Thursday</option>
            	<option value="5">Friday</option>
            	<option value="6">Saturday</option>
            </select>
        </td>
    </tr>      
</table>
</div>

<div class="tabcpwizzard"><h1>Options for data display on calendar:</h1>
<table class="form-table">    
    <tr valign="top">
        <th scope="row"><label>Allow edition in frontend?</label></th>
        <td>
         <label class="container">
           <input type="checkbox" id="<?php echo esc_attr($this->prefix); ?>_edition"  name="edition" value="true">
           <span class="checkmark"></span>
         </label>        
        </td>
    </tr>      
    <tr valign="top">
        <th scope="row"><label>Tooltip Settings</label></th>
        <td>          
          <div>
         <label class="container">Show tooltip on:
           <input type="checkbox" checked="checked" id="<?php echo esc_attr($this->prefix); ?>_showtooltip" name="showtooltip" value="true"  onclick="javascript:showhide('mvparams')">
           <span class="checkmark"></span>
         </label>        
            <select id="<?php echo esc_attr($this->prefix); ?>_tooltipon" name="tooltipon" ><option value="0"  >mouse over</option><option value="1" >click</option></select>
          </div>
        </td>
    </tr>    
   <tr valign="top">
        <th scope="row"><label>Other parameters</label></th>
        <td>		
          <input name="otherparams" id="<?php echo esc_attr($this->prefix); ?>_otherparams" value="militaryTime:false" />		
        </td>  
    </tr>
</table>
</div>

<div class="tabcpwizzard"><h1>Where to publish it:</h1>
  
  <p>
  <select name="whereto" onchange="mvpublish_displayoption(this);">
    <option value="0">Into a new page</option>
    <option value="1">Into a new post</option>
    <option value="2">Into an existent page</option>
    <option value="3">Into an existent post</option>
  </select></p>
  
  <p id="ppage" style="display:none">
  Select page:<br />
  <select name="publishpage">
   <?php 
       $pages = get_pages();
       foreach ( $pages as $page ) {
         echo '<option value="' .  intval($page->ID) . '">' . esc_html($page->post_title) . '</option>';
       }
   ?>
  </select></p>

  <p id="ppost" style="display:none">
  Select post:<br />
  <select name="publishpost">
   <?php 
       $pages = get_posts();
       foreach ( $pages as $page ) {
         echo '<option value="' .  intval($page->ID)  . '">' . esc_html($page->post_title) . '</option>';
       }
   ?>
  </select></p> 
  
</div>

<div style="overflow:auto;">
  <div style="float:right;">
    <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
    <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
  </div>
</div>
 
<!-- Circles which indicates the steps of the form: -->
<div style="text-align:center;margin-top:40px;">
  <span class="step"></span>
  <span class="step"></span>
  <span class="step"></span>
</div>

<?php } ?>

</form>

<script type="text/javascript">

function cpmv_mvpublish_displayviews(sel) {
    if (sel.checked)
        document.getElementById("nmonthsnum").style.display = '';
    else
        document.getElementById("nmonthsnum").style.display = 'none';        
}

function mvpublish_displayoption(sel) {
    document.getElementById("ppost").style.display = 'none';
    document.getElementById("ppage").style.display = 'none';
    if (sel.selectedIndex == 2)
        document.getElementById("ppage").style.display = '';
    else if (sel.selectedIndex == 3)
        document.getElementById("ppost").style.display = '';
}

var currentTab = 0; // Current tab is set to be the first tab (0)
cpmv_showTab(currentTab); // Display the current tab

function cpmv_showTab(n) {
  // This function will display the specified tab of the form ...
  var x = document.getElementsByClassName("tabcpwizzard");
  x[n].style.display = "block";
  // ... and fix the Previous/Next buttons:
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Submit";
  } else {
    document.getElementById("nextBtn").innerHTML = "Next";
  }
  // ... and run a function that displays the correct step indicator:
  fixStepIndicator(n)
}

function nextPrev(n) {
  // This function will figure out which tab to display
  var x = document.getElementsByClassName("tabcpwizzard");
  // Exit the function if any field in the current tab is invalid:
  if (n == 1 && !validateForm()) return false;
  // Hide the current tab:
  x[currentTab].style.display = "none";
  // Increase or decrease the current tab by 1:
  currentTab = currentTab + n;
  // if you have reached the end of the form... :
  if (currentTab >= x.length) {
    //...the form gets submitted:
    document.getElementById("regForm").submit();
    return false;
  }
  // Otherwise, display the correct tab:
  cpmv_showTab(currentTab);
}

function validateForm() {
  // This function deals with validation of the form fields
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tabcpwizzard");
  y = x[currentTab].getElementsByTagName("input");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (y[i].value == "") {
      // add an "invalid" class to the field:
      y[i].className += " invalid";
      // and set the current valid status to false:
      valid = false;
    }
  }
  // If the valid status is true, mark the step as finished and valid:
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid; // return the valid status
}

function fixStepIndicator(n) {
  // This function removes the "active" class of all steps...
  var i, x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  //... and adds the "active" class to the current step:
  x[n].className += " active";
}
</script>   

<hr size="1" />