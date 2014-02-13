<?php
/*
Plugin Name: Agreeable
Plugin URI: http://wordpress.org/extend/plugins/agreeable
Description: Add a required "Agree to terms" checkbox to login and/or register forms.  Based on the I-Agree plugin by Michael Stursberg.
Version: 0.2
Author: buildcreate
Author URI: http://buildcreate.com
*/

function wp_authenticate_user_acc($user) {
	
	$dblogin = get_option('ag_login');
	$dbregister = get_option('ag_register');
	$dbfail = get_option('ag_fail');
	$body_class = get_body_class();
	$login_page = get_option('ag_login_page');
	$register_page = get_option('ag_register_page');
	
	global $bp, $post;
	isset($post) ? $pid = $post->ID : $pid = NULL;
		  
		  // See if the checkbox #login_accept was checked
	    if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {
	        // Checkbox on, allow login
	        return $user;
	    } else {
	        // Did NOT check the box, do not allow login
	        
	        $error = new WP_Error();
	        $error->add('did_not_accept', $dbfail);
			  
			  if(isset($bp->signup)) {
	        		$bp->signup->errors['login_accept'] = '<div class="error">'.$dbfail.'</div>';
	        }
	        
	        return $error;
	    }

}

// Add it to the appropriate hooks
add_filter('wp_authenticate_user', 'wp_authenticate_user_acc', 99999, 2);
add_filter('registration_errors', 'wp_authenticate_user_acc', 99999, 2);
add_filter('bp_signup_validate', 'wp_authenticate_user_acc', 99999, 2);

function display_terms_form() {
	$dbtermm = get_option('ag_termm');
	$dburl = get_option('ag_url');
	
	global $post;
	isset($post) ? $pid = $post->ID : $pid = NULL;
	$body_class = get_body_class();
	
	global $bp;
 
   if(isset($dburl)) {$terms = get_post($dburl); $terms = apply_filters('the_content', $terms->post_content);}    
 
 	// Add an element to the login form, which must be checked
 	
 	agreeable_thickbox();
 	
 	echo '<style>#terms{display: none} #TB_ajaxContent p {font-weight: 200; line-height: 1.5em;}</style>';
 	echo '<div style="clear: both; padding: .25em 0;" id="terms-accept" class="terms-form">';
 		if(isset($bp)){do_action( 'bp_login_accept_errors' );}
 	echo '<label style="text-align: left;"><input type="checkbox" name="login_accept" id="login_accept" />&nbsp;<a title="'.get_post($dburl)->post_title.'" class="thickbox" target="_BLANK" href="#TB_inline?width=600&height=550&inlineId=terms">'.$dbtermm.'</a></label></div>';
 	
 	echo '<div id="terms"><div>'.$terms.'</div></div>';
}

function login_terms_accept(){
	$dblogin = get_option('ag_login');
	
	if($dblogin == 1) {
		display_terms_form();
	}
}

function register_terms_accept() {
	
	$dbregister = get_option('ag_register');
	
	if($dbregister == 1) {
		display_terms_form();
	}
}

// As part of WP login form construction, call our function
add_filter ( 'login_form', 'login_terms_accept' );
add_filter ( 'register_form', 'register_terms_accept' );
add_action('bp_before_registration_submit_buttons', 'register_terms_accept');


function ag_widget_terms_accept() {

	$dblogin = get_option('ag_login');
	
	if($dblogin == 1) {
		display_terms_form();
	}
	
	echo '<script>';
		echo '
			jQuery(document).ready(function($){
				$("#terms-accept").insertBefore("#bp-login-widget-rememberme");
				$("#bp-login-widget-form").nextAll(".terms-form").hide();
			});
		';
	echo '</script>';
	
}

add_action('bp_after_login_widget_loggedout', 'ag_widget_terms_accept');

function agreeable_thickbox() {
	if (! is_admin()) {
		wp_enqueue_script('thickbox', null,  array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	}
}

function agreeable_options() {  
  add_options_page('agreeable', 'Agreeable', 'manage_options', 'terms-options', 'agoptions');
}

function agoptions() {		  
	include('agreeable-options.php');
}

// Add to the admin menu
add_action('admin_menu', 'agreeable_options');


/* Plugin feedback form */

function feedback_form() {
	
	if(!isset($_POST['feedback_email']) && !isset($_POST['feedback_content'])) {
	
	$output = '<h3>We want your feedback.</h3>
				<p><em>Have a feature idea, feedback, or question about the plugin?<br>We want to know- send it on over!</em></p>
				<form id="feedback-form" name="feedback_form" method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'">
				<label for="feedback_email">Your email</label>
					<input type="email" name="feedback_email" placeholder="your@email.com" /><br>
				<label for="feedback_content">Message</label>
					<textarea name="feedback_content" placeholder="Type your feedback / feature request here!"></textarea><br>
					<input type="submit" class="button-primary button-large button" style="margin-top: 1em;" value="Send it!" />			
				</form>';
	$output .= '<div style="padding: 1em; background: #eee; color: #333; margin-top: 2em;">
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
	} else {
		$output = '<h3>Thank you for your feedback!</h3>';
	}
	
	echo $output;
}

function send_feedback() {
	if(isset($_POST['feedback_email']) && isset($_POST['feedback_content'])) {
		
		$to = 'ian@buildcreate.com';
		$subject = 'New plugin feedback';
		$message = $_POST['feedback_content'];
		$headers = 'From: <'.$_POST['feedback_email'].'>' . "\r\n";
		
		
		wp_mail( $to, $subject, $message, $headers, '' );
		
	}
}

add_action( 'plugins_loaded', 'send_feedback');