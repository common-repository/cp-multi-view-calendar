<?php
         
if( !class_exists( 'CP_BaseClass' ) ) {         

class CP_BaseClass {       
    
    /** installation functions */
    public function install($networkwide)  {
    	global $wpdb;
     
    	if (function_exists('is_multisite') && is_multisite()) {
    		// check if it is a network activation - if so, run the activation function for each blog id
    		if ($networkwide) {
    	                $old_blog = $wpdb->blogid;
    			// Get all blog ids
    			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    			foreach ($blogids as $blog_id) {
    				switch_to_blog($blog_id);
    				$this->_install();
    			}
    			switch_to_blog($old_blog);
    			return;
    		}	
    	} 
    	$this->_install();	
    }    
    
    public function get_param($key)
    {
        $allowed_tags = wp_kses_allowed_html( 'post' );
        if (isset($allowed_tags["script"])) unset($allowed_tags["sript"]);
        if (isset($allowed_tags["iframe"])) unset($allowed_tags["iframe"]);            
        if (isset($_GET[$key]) && $_GET[$key] != '')
            return wp_kses($_GET[$key], $allowed_tags);
        elseif (isset($_POST[$key]) && $_POST[$key] != '')
            return wp_kses($_POST[$key], $allowed_tags);
        else 
            return '';
    }
    
    public function is_administrator()
    {
        return current_user_can('manage_options');
    }
    
    public function get_site_url($admin = false)
    {
        $blog = get_current_blog_id();
        if( $admin ) 
            $url = get_admin_url( $blog );	
        else 
            $url = get_home_url( $blog );	
        
        $url = parse_url($url);       
        if ( isset( $url["path"] ) ) {
            return rtrim(@$url["path"],"/");
        } else {
            return "";
        }
    }
    
    
    public function get_FULL_site_url($admin = false)
    {
        $blog = get_current_blog_id();
        if( $admin ) 
            $url = get_admin_url( $blog );	
        else 
            $url = get_home_url( $blog );	
        
        $url = parse_url($url);
        if ( isset( $url["path"] ) ) {
            $url = rtrim($url["path"],"/");
        } else {
            $url = "/";
        }
        $pos = strpos($url, "://");
        if ($pos === false)
            $url = 'http://'.sanitize_text_field($_SERVER["HTTP_HOST"]).$url;
        return $url;
    }
    
    public function esc_sql_table_name($tablename)
    {       
        return str_replace( "`", "``", santize_key($name) );
    }
       
} // end class

} // end if class exists

?>