<?php
/*
Plugin Name: Agreeable
Plugin URI: http://wordpress.org/extend/plugins/agreeable
Description: Add a required "Agree to terms" checkbox to login and/or register forms.  Based on the I-Agree plugin by Michael Stursberg.
Version: 0.1.1
Author: buildcreate
Author URI: http://buildcreate.com
*/

function wp_authenticate_user_acc($user, $password) {
	
	$dblogin = get_option('ag_login');
	$dbregister = get_option('ag_register');
	
	if(in_array($GLOBALS['pagenow'], array('wp-login.php')) && $dblogin == 1 || in_array($GLOBALS['pagenow'], array('wp-register.php', 'index.php')) && $dbregister == 1) {
	
		 $dbfail = get_option('ag_fail');
		  
		  // See if the checkbox #login_accept was checked
	    if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {
	        // Checkbox on, allow login
	        return $user;
	    } else {
	        // Did NOT check the box, do not allow login
	        $error = new WP_Error();
	        $error->add('did_not_accept', $dbfail);
	        return $error;
	    }
    } else {
	    return $user;
    }
}

// As part of WP login form construction, call our function
add_filter ( 'login_form', 'terms_accept' );
add_filter ( 'register_form', 'terms_accept' );
add_action('bp_before_registration_submit_buttons', 'terms_accept');

function terms_accept(){
	$dbtermm = get_option('ag_termm');
	$dburl = get_option('ag_url');
	$dblogin = get_option('ag_login');
	$dbregister = get_option('ag_register');
	global $post;
	
    if(in_array($GLOBALS['pagenow'], array('wp-login.php')) && $dblogin == 1 || in_array($GLOBALS['pagenow'], array('wp-register.php', 'index.php')) && $dbregister == 1) {
    
    	// Add an element to the login form, which must be checked
    	echo '<div id="terms-accept"><label><input type="checkbox" name="login_accept" id="login_accept" />&nbsp;<a target="_BLANK" href="'.$dburl.'">'.$dbtermm.'</a></label></div>';
    }
}

function i_agree_options() {  
  add_options_page('agreeable', 'Agreeable', 'manage_options', 'terms-options', 'agoptions');
}

function agoptions() {		  
	include('agreeable-options.php');
}

// Add to the admin menu
add_action('admin_menu', 'i_agree_options');

// Add it to the appropriate hooks
add_filter('wp_authenticate_user', 'wp_authenticate_user_acc', 99999, 2);
add_filter('bp_signup_validate', 'wp_authenticate_user_acc', 9999, 2);