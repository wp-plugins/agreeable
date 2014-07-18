<?php
/*
Plugin Name: Agreeable
Plugin URI: http://wordpress.org/extend/plugins/agreeable
Description: Add a required "Agree to terms" checkbox to login and/or register forms.  Based on the I-Agree plugin by Michael Stursberg.
Version: 0.3.3
Author: buildcreate
Author URI: http://buildcreate.com
*/

function ag_action_init() {
	// Localization
	load_plugin_textdomain('agreeable', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action('init', 'ag_action_init');


wp_enqueue_style( 'agreeable-css', plugins_url('css/agreeable.css', __FILE__));	


function agreeable_lightbox() {
	if (!is_admin()) {
		wp_enqueue_script( 'magnific', plugins_url('js/magnific.js', __FILE__),'', '', true);
		wp_enqueue_script( 'agreeable-js', plugins_url('js/agreeable.js', __FILE__), '', '', true);
		wp_enqueue_style( 'magnific', plugins_url('css/magnific.css', __FILE__));	
	}
}

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
	        	        	        
		        $error = new WP_Error();
		        $error->add('did_not_accept', $dbfail);
				  
				  if(isset($bp->signup)) {
		        		$bp->signup->errors['login_accept'] = '<div class="error">'.$dbfail.'</div>';
		        }
		        
		        return $error;
		        
	        } else {
	        	return $user;
	        }
	    }
	} else {
		return $user;
	}

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

function ag_display_terms_form($type) {
	$dbtermm = get_option('ag_termm');
	$dburl = get_option('ag_url');
	$dblightbox = get_option('ag_lightbox');
	$dbcolors = get_option('ag_colors');
	$dbremember = get_option('ag_remember');
	
	if ( !isset($_COOKIE['agreeable_terms'] ) && $dbremember == 1 || $dbremember == 0 ) {
	   if(isset($dburl)) {$terms = get_post($dburl); $terms_content = '<h3>'.$terms->post_title.'</h3>'.apply_filters('the_content', $terms->post_content);}    
	   
	 	// Add an element to the login form, which must be checked
	 	
	 	$term_link = get_post_permalink($terms);
	 	
	 	if($dblightbox == 1) {
	 	
	 		agreeable_lightbox();
	 		$term_link = '#terms';
	 		
	 		if($dbcolors) {
		 		echo '<style>#terms {background: '.$dbcolors['bg-color'].' !important; color: '.$dbcolors['text-color'].';}</style>';
	 		}		
	 	}
	 	
	 	echo '<div style="clear: both; padding: .25em 0;" id="terms-accept" class="terms-form">';
	 		if(isset($bp)){do_action( 'bp_login_accept_errors' );}
	 	echo '<label style="text-align: left;"><input type="checkbox" name="login_accept" id="login_accept" />&nbsp;<a title="'.get_post($dburl)->post_title.'" class="open-popup-link" target="_BLANK" href="'.$term_link.'">'.$dbtermm.'</a></label>';
	 	echo '<input type="hidden" value="'.$type.'" name="ag_type" /></div>';
	 	echo '<div id="terms" class="mfp-hide">'.$terms_content.'</div>';
	 	echo $type == 'comments' ? '<br>':'';
 	}
}

function ag_login_terms_accept(){
	$dblogin = get_option('ag_login');
	
	if($dblogin == 1) {
		ag_display_terms_form('login');
	}
}

function ag_comment_terms_accept(){
	$dbcomments = get_option('ag_comments');
	
	if($dbcomments == 1) {
		ag_display_terms_form('comments');
	}
}

function ag_register_terms_accept() {
	
	$dbregister = get_option('ag_register');
	
	if($dbregister == 1) {
		ag_display_terms_form('register');
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
add_filter('register_form', 'ag_register_terms_accept');
add_filter('comment_form_after_fields', 'ag_comment_terms_accept');

add_action('bp_before_registration_submit_buttons', 'ag_register_terms_accept');


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
	
	if(!isset($_POST['feedback_email']) && !isset($_POST['feedback_content'])) {
	
/*
	$output = '<h3>We want your feedback.</h3>
				<p><em>Have a feature idea, feedback, or question about the plugin?<br>We want to know- send it on over!</em></p>
				<form id="ag-feedback-form" name="feedback_form" method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'">
				<label for="feedback_email">Your email</label>
					<input type="email" name="feedback_email" placeholder="your@email.com" /><br>
				<label for="feedback_content">Message</label>
					<textarea name="feedback_content" placeholder="Type your feedback / feature request here!"></textarea><br>
					<input type="submit" class="button-primary button-large button" style="margin-top: 1em;" value="Send it!" />			
				</form>';
*/
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
	} else {
		$output = '<h3>Thank you for your feedback!</h3>';
	}
	
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
