try {
var initializedMV = 0;
var pathCalendar;
function initMVCalCP() {
    for (var i=0; i<100;i++)
    {
    	try {
        var tt = eval("cpmvc_configmultiview"+i);        
        if (tt)
        {
            (function($) {    
                mvcconfig = $.parseJSON(tt.obj);                
                if (mvcconfig.params.otherparams)
                {
                    //console.log("var others={"+mvcconfig.params.otherparams+"};");
                    mvcconfig.params.otherparams = mvcconfig.params.otherparams.replace(/#/g,'"');                    
                    eval("var others={"+mvcconfig.params.otherparams+"};");                    
                    mvcconfig.params = $.extend(mvcconfig.params, others);
                }
            })(jQuery);         
            pathCalendar = cpmvc_ajax_object.url+"?cpmvc_do_action=mvparse&security="+cpmvc_ajax_object.nonce; //mvcconfig.ajax_url;            
            if ( document.getElementById("cal"+mvcconfig.calendar+"_"+i) !== null)
            {
                initMultiViewCal("cal"+mvcconfig.calendar+"_"+i, mvcconfig.calendar,(mvcconfig.params));
                initializedMV++;
            }
        }
       }catch (e) { }  
    }
    if (!initializedMV)
        setTimeout('initMVCalCP()',1000);
}
initMVCalCP();
}catch (e) {} 