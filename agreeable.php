<?php
/*
Plugin Name: Agreeable
Plugin URI: http://wordpress.org/extend/plugins/agreeable
Description: Add a required "Agree to terms" checkbox to login and/or register forms.  Based on the I-Agree plugin by Michael Stursberg.
Version: 0.4
Author: kraftpress
Author URI: http://kraftpress.it
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

session_start();

function ag_language_init() {
	// Localization
	load_plugin_textdomain('agreeable', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function ag_admin() {
	/* Plugin Stylesheet */
	wp_enqueue_style( 'agreeable-css', plugins_url('css/admin.css', __FILE__), '', '0.3.4', 'screen');
}

function ag_front() {
	/* Only load lightbox code on the frontend, where we need it */
		if ( is_login_page() ) {
			wp_enqueue_script('jquery');
		}
		wp_enqueue_script( 'magnific', plugins_url('js/magnific.js', __FILE__),'', '', true);
		wp_enqueue_script( 'agreeable-js', plugins_url('js/agreeable.js', __FILE__), '', '', true);
		wp_enqueue_style( 'magnific', plugins_url('css/magnific.css', __FILE__));
		wp_enqueue_style( 'agreeable-css', plugins_url('css/front.css', __FILE__));	
}

add_action('init', 'ag_language_init');
add_action('admin_enqueue_scripts', 'ag_admin');
add_action('wp_enqueue_scripts', 'ag_front');
add_action('login_enqueue_scripts', 'ag_front');

function ag_authenticate_user_acc($user) {
	
	$dblogin = get_option('ag_login');
	$dbregister = get_option('ag_register');
	$dbfail = get_option('ag_fail');
	$dbremember = get_option('ag_remember');

	global $bp;
	
	if(isset($_REQUEST['ag_type']) && $_REQUEST['ag_type'] == "login" && $dblogin == 1 || isset($_REQUEST['ag_type']) && $_REQUEST['ag_type'] == 'register' && $dbregister == 1) {
		  
		  // See if the checkbox #login_accept was checked
	    if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {
	        // Checkbox on, allow login
	        
				if ( !isset( $_COOKIE['agreeable_terms'] ) && $dbremember == 1 ) {
					setcookie( 'agreeable_terms', 'yes', strtotime('+30 days'), COOKIEPATH, COOKIE_DOMAIN, false );
				}
	        
	        return $user;
	    } else {
	        // Did NOT check the box, lets see if the cookie is already set
	        
	        if ( !isset($_COOKIE['agreeable_terms'] ) && $dbremember == 1 || $dbremember == 0) {
	        	        	        
		        $errors = new WP_Error();
		        $errors->add('ag_did_not_accept', $dbfail);
		        
		        $_SESSION['ag_errors'] = $dbfail;
		        
				  
				  if(isset($bp)) {
		        		$bp->signup->errors['login_accept'] = '<div class="error">'.$dbfail.'</div>';
		        }
		        
		        
		        if(is_multisite()) {
					  
					  $result['errors'] = $errors;
					  $result['errors']->add('ag_did_not_accept', $dbfail);
					  
					  return $result;
					  
					} else {
		        
		        		return $errors;
				  	
				  	}
		        
	        } else {
	        unset($_SESSION['ag_errors']);
	        	return $user;
	        }
	    }
	} else {
		return $user;
	}

}



add_filter('woocommerce_registration_errors', 'ag_woocommerce_reg_validation', 10,3);

function ag_woocommerce_reg_validation($reg_errors, $sanitized_user_login, $user_email) {
    global $woocommerce;
    $dbfail = get_option('ag_fail');
    
    if(!isset($_REQUEST['login_accept'])) {
    
	    if ( !isset($_COOKIE['agreeable_terms'] ) && $dbremember == 1 || $dbremember == 0) {
	    		  return new WP_Error('registration-error', __($dbfail, 'woocommerce'));
		  		  $woocommerce->add_error( __( $dbfail, 'woocommerce' ) );
	  		  } else {

			 }
    }
    
	if ( !isset( $_COOKIE['agreeable_terms'] ) && $dbremember == 1 ) {
		setcookie( 'agreeable_terms', 'yes', strtotime('+30 days'), COOKIEPATH, COOKIE_DOMAIN, false );
	}
   
   return $reg_errors;
}

function ag_validate_comment($comment) {
	
	$dbcomments = get_option('ag_comments');
	$dbfail = get_option('ag_fail');

	global $bp;
	
	if($dbcomments == 1) {
		  
		  // See if the checkbox #login_accept was checked
	    if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {
	        // Checkbox on, allow login
	        return $comment;
	    } else {
	        // Did NOT check the box, do not allow login
	        
	        $error = new WP_Error();
	        $error->add('did_not_accept', $dbfail);
	        
	        wp_die( __($dbfail) );
	        return $error;
	    }
	} else {
		return $comment;
	}

}

// Add it to the appropriate hooks
add_filter('wp_authenticate_user', 'ag_authenticate_user_acc', 99999, 2);
add_filter('registration_errors', 'ag_authenticate_user_acc', 99999, 2);
add_filter('bp_signup_validate', 'ag_authenticate_user_acc', 99999, 2);
add_action('pre_comment_on_post', 'ag_validate_comment', 99999, 2);

add_filter('wpmu_validate_user_signup','ag_authenticate_user_acc',10,3);

function ag_display_terms_form($type, $errors = '') {
	$dbtermm = get_option('ag_termm');
	$dburl = get_option('ag_url');
	$dblightbox = get_option('ag_lightbox');
	$dbcolors = get_option('ag_colors');
	$dbremember = get_option('ag_remember');
	$dbfail = get_option('ag_fail');
	
	if ( !isset($_COOKIE['agreeable_terms'] ) && $dbremember == 1 || $dbremember == 0 ) {
	   if(isset($dburl)) {$terms = get_post($dburl); $terms_content = '<h3>'.$terms->post_title.'</h3>'.apply_filters('the_content', $terms->post_content);}    
	   
	 	// Add an element to the login form, which must be checked
	 	
	 	$term_link = get_post_permalink($terms);
	 	
	 	if($dblightbox == 1) {
	 	
	 		$term_link = '#terms';
	 		
	 		if($dbcolors) {
		 		echo '<style>#terms {background: '.$dbcolors['bg-color'].' !important; color: '.$dbcolors['text-color'].';}</style>';
	 		}		
	 	}
	 	
/* 	 Get our errors incase we need to display */
		
		if(is_wp_error($errors)) {
			
				$error = $errors->get_error_message( 'ag_did_not_accept' );
			
			}
		
		if(isset($_SESSION['ag_errors'])) {
			
				$error = $_SESSION['ag_errors'];
				unset($_SESSION['ag_errors']);
			}
			
		if ( !empty($error) ) {
			
				echo "<br><p class='error'>$error</p>";		
				
			}
	
	 	echo '<div style="clear: both; padding: .25em 0;" id="terms-accept" class="terms-form">';
	 		if(isset($bp)){do_action( 'bp_login_accept_errors' );}
	 	echo '<label style="text-align: left;"><input type="checkbox" name="login_accept" id="login_accept" />&nbsp;<a title="'.get_post($dburl)->post_title.'" class="open-popup-link" target="_BLANK" href="'.$term_link.'">'.$dbtermm.'</a></label>';
	 	echo '<input type="hidden" value="'.$type.'" name="ag_type" /></div>';
	 	echo '<div id="terms" class="mfp-hide">'.$terms_content.'</div>';
	 	echo $type == 'comments' ? '<br>':'';
 	}
}

function ag_login_terms_accept($errors){
	$dblogin = get_option('ag_login');
	
	if($dblogin == 1) {
		ag_display_terms_form('login', $errors);
	}
}

function ag_comment_terms_accept(){
	$dbcomments = get_option('ag_comments');
	
	if($dbcomments == 1) {
		ag_display_terms_form('comments');
	}
}

function ag_register_terms_accept($errors) {
	
	$dbregister = get_option('ag_register');
	
	if($dbregister == 1) {
		ag_display_terms_form('register', $errors);
	}
	
	echo '<script>';
		echo '
			jQuery(document).ready(function($){
				if($("#theme-my-login")) {
					$("#theme-my-login #terms-accept").insertBefore("#theme-my-login .submit");
				}
			});
		';
	echo '</script>';
	
}


// As part of WP login form construction, call our function
add_filter('login_form', 'ag_login_terms_accept' );
add_filter('woocommerce_after_customer_login_form', 'ag_login_terms_accept');
add_filter('register_form', 'ag_register_terms_accept');
add_filter('comment_form_after_fields', 'ag_comment_terms_accept');

add_action('bp_before_registration_submit_buttons', 'ag_register_terms_accept');

add_action( 'tml_register_form', 'ag_register_terms_accept', 9999, 3);

if (is_multisite()) {
	add_action( 'signup_extra_fields', 'ag_register_terms_accept', 9999, 3);
	add_action( 'signup_blogform', 'ag_register_terms_accept', 9999, 3);
}


function ag_widget_terms_accept() {

	$dblogin = get_option('ag_login');
	
	if($dblogin == 1) {
		ag_display_terms_form('login');
	}
	
	echo '<script>';
		echo '
			jQuery(document).ready(function($){
				$(".widget_bp_core_login_widget #terms-accept").insertBefore("#bp-login-widget-form .forgetmenot");
				$(".widget_bp_core_login_widget #bp-login-widget-form").nextAll(".terms-form").remove();
			});
		';
	echo '</script>';
	
}

add_action('bp_after_login_widget_loggedout', 'ag_widget_terms_accept');

function agreeable_options() {  
  add_options_page('agreeable', 'Agreeable', 'manage_options', 'terms-options', 'agoptions');
}

function agoptions() {		  
	include('agreeable-options.php');
}

// Add to the admin menu
add_action('admin_menu', 'agreeable_options');


/* Plugin feedback form */

function ag_feedback_form() {
	
	
	$output .= '<div style="padding: 1em; background: #eee; color: #333;">
					<h3 style="color: #369;">Buy me a cup of joe?</h3>
					<p>
						<em>Feeling generous?  Because I sure wouldn\'t turn down a hot cup of coffee...</em>
					</p>
					<p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="LCNWR8KVE3UVL">
							<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</p></div>';
	
	echo $output;
}

function ag_send_feedback() {
	if(isset($_POST['feedback_email']) && isset($_POST['feedback_content'])) {
		
		$to = 'ian@buildcreate.com';
		$subject = 'New plugin feedback';
		$message = $_POST['feedback_content'];
		$headers = 'From: <'.$_POST['feedback_email'].'>' . "\r\n";
		
		
		wp_mail( $to, $subject, $message, $headers, '' );
		
	}
}

add_action( 'plugins_loaded', 'ag_send_feedback');

function is_login_page() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}