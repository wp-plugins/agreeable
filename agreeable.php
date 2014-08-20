<?php
/*
Plugin Name: Agreeable
Plugin URI: http://wordpress.org/extend/plugins/agreeable
Description: Add a required "Agree to terms" checkbox to login and/or register forms.  Based on the I-Agree plugin by Michael Stursberg.
Version: 1.3.1
Author: kraftpress
Author URI: http://kraftpress.it
*/


//==================================
//! TODO-
//! Cleanup functions, make it smarter, faster, better.
//==================================



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(session_id() == '') {
	session_start();
}

class Agreeable {
	function __construct() {

		/* Initialize the plugin */
		add_action('init', array($this, 'ag_language_init'));
		add_action('admin_enqueue_scripts', array($this, 'ag_admin'));
		add_action('wp_enqueue_scripts', array($this, 'ag_front'));
		add_action('login_enqueue_scripts', array($this, 'ag_front'));
		add_action('admin_menu', array($this, 'agreeable_options'));

		/* Registration Validation Hooks  */
		add_filter('woocommerce_registration_errors', array($this, 'ag_woocommerce_reg_validation'), 10,3);
		add_filter('registration_errors', array($this, 'ag_authenticate_user_acc'), 10, 2);
		add_filter('bp_signup_validate', array($this, 'ag_authenticate_user_acc'), 10, 2);
		add_filter('wpmu_validate_user_signup', array($this, 'ag_authenticate_user_acc'), 10, 3);


		/* Login Validation Hooks */
		add_filter('wp_authenticate_user', array($this, 'ag_authenticate_user_acc'), 10, 2);


		/* Comment Validation Hooks */
		add_action('pre_comment_on_post', array($this, 'ag_validate_comment'), 10, 2);

		/* Output Hooks */
		add_filter('login_form', array($this, 'ag_login_terms_accept') );
		add_filter('woocommerce_after_customer_login_form', array($this, 'ag_login_terms_accept'));
		add_filter('register_form', array($this, 'ag_register_terms_accept'));
		add_filter('comment_form_after_fields', array($this, 'ag_comment_terms_accept'));
		add_action('bp_before_registration_submit_buttons', array($this, 'ag_register_terms_accept'));
		add_action('tml_register_form', array($this, 'ag_register_terms_accept'), 10, 3);
		add_action('bp_after_login_widget_loggedout', array($this, 'ag_widget_terms_accept'));

		if (is_multisite()) {
			add_action( 'signup_extra_fields', 'ag_register_terms_accept', 10, 3);
			add_action( 'signup_blogform', 'ag_register_terms_accept', 10, 3);
		}

		$this->options = array(
			'login' => get_option('ag_login'),
			'register' => get_option('ag_register'),
			'fail_text' => get_option('ag_fail'),
			'remember_me' => get_option('ag_remember'),
			'message' => get_option('ag_termm'),
			'terms_page' => get_option('ag_url'),
			'comments' => get_option('ag_comments'),
			'lightbox' => get_option('ag_lightbox'),
			'colors' => get_option('ag_colors')
		);

		return true;
	}
	
	function update_options() {
		$this->options = array(
			'login' => get_option('ag_login'),
			'register' => get_option('ag_register'),
			'fail_text' => get_option('ag_fail'),
			'remember_me' => get_option('ag_remember'),
			'message' => get_option('ag_termm'),
			'terms_page' => get_option('ag_url'),
			'comments' => get_option('ag_comments'),
			'lightbox' => get_option('ag_lightbox'),
			'colors' => get_option('ag_colors')
		);

	}

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
		if ( $this->is_login_page() ) {
			wp_enqueue_script('jquery');
		}
		wp_enqueue_script( 'magnific', plugins_url('js/magnific.js', __FILE__),'', '', true);
		wp_enqueue_script( 'agreeable-js', plugins_url('js/agreeable.js', __FILE__), '', '', true);
		wp_enqueue_style( 'magnific', plugins_url('css/magnific.css', __FILE__));
		wp_enqueue_style( 'agreeable-css', plugins_url('css/front.css', __FILE__));
	}




	function ag_authenticate_user_acc($user) {


		if(isset($_REQUEST['ag_type']) && $_REQUEST['ag_type'] == "login" && $this->options['login'] == 1 || isset($_REQUEST['ag_type']) && $_REQUEST['ag_type'] == 'register' && $this->options['register'] == 1) {

			// See if the checkbox #login_accept was checked
			if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {

				// Checkbox on, allow login, set the cookie if necessary

				if ( !isset( $_COOKIE['agreeable_terms'] ) && $this->options['remember_me'] == 1 ) {
					setcookie( 'agreeable_terms', 'yes', strtotime('+30 days'), COOKIEPATH, COOKIE_DOMAIN, false );
				}

				return $user;
				
			} else {

				if($this->is_buddypress_registration()) {
				
						global $bp; 
											
						$bp->signup->errors['login_accept'] = $this->options['fail_text'];
						
						return;
						
					}
					
				
				
				$errors = new WP_Error();
				$errors->add('ag_did_not_accept', $this->options['fail_text']);

				/* Incase it's a form that doesn't respect WordPress' error system */

				$_SESSION['ag_errors'] = $this->options['fail_text'];


				if(is_multisite()) {

					$result['errors'] = $errors;
					$result['errors']->add('ag_did_not_accept', $this->options['fail_text']);

					return $result;

				} else {

					return $errors;

				}

			}

		} else {

			return $user;

		}

	}

	function is_buddypress_registration() {

		if(function_exists('bp_current_component')) {
				
			/* Lets make sure we're on the right page- Ie the buddypress register page */
			$bp_pages = get_option('bp-pages');
			$bp_page = get_post($bp_pages['register']);
			
			global $wp_query;
			$current_page = $wp_query->query_vars['name'];
						
			return $bp_page->post_name == $current_page ?  true : false;

		}
	}



	function ag_woocommerce_reg_validation($reg_errors, $sanitized_user_login, $user_email) {
		global $woocommerce;

		if(!isset($_REQUEST['login_accept'])) {

			if ( !isset($_COOKIE['agreeable_terms'] ) && $this->options['remember_me'] == 1 || $this->options['remember_me'] == 0) {
				return new WP_Error('registration-error', __($this->options['fail_text'], 'woocommerce'));
				$woocommerce->add_error( __( $this->options['fail_text'], 'woocommerce' ) );
			} else {

			}
		}

		if ( !isset( $_COOKIE['agreeable_terms'] ) && $this->options['remember_me'] == 1 ) {
			setcookie( 'agreeable_terms', 'yes', strtotime('+30 days'), COOKIEPATH, COOKIE_DOMAIN, false );
		}

		return $reg_errors;
	}

	function ag_validate_comment($comment) {

		if($this->options['comments'] == 1) {

			// See if the checkbox #login_accept was checked
			if ( isset( $_REQUEST['login_accept'] ) && $_REQUEST['login_accept'] == 'on' ) {
				// Checkbox on, allow comment
				return $comment;
			} else {
				// Did NOT check the box, do not allow login

				$error = new WP_Error();
				$error->add('did_not_accept', $this->options['fail_text']);

				wp_die( __($this->options['fail_text']) );
				return $error;
			}
		} else {
			return $comment;
		}

	}


	function ag_display_terms_form($type, $errors = '') {

		if(isset($this->options['terms_page'])) {
			$terms = get_post($this->options['terms_page']);
			$terms_content = '<h3>'.$terms->post_title.'</h3>'.apply_filters('the_content', $terms->post_content);
		}

		/* Add an element to the login form, which must be checked */

		$term_link = get_post_permalink($terms);

		if($this->options['lightbox'] == 1) {

			$term_link = '#terms';

			if($this->options['colors']) {
				echo '<style>#terms {background: '.$this->options['colors']['bg-color'].' !important; color: '.$this->options['colors']['text-color'].';}</style>';
			}
		}

		/*  Get our errors incase we need to display */

		$errors = new WP_Error();

		if(isset($_SESSION['ag_errors']) && $errors->get_error_message( 'ag_did_not_accept' ) != '' ) {

			$error = $_SESSION['ag_errors'];
			unset($_SESSION['ag_errors']);

		} elseif (is_wp_error($errors)) {

			unset($error);

		}

		if ( !empty($error) ) {

			echo "<br><p class='error'>$error</p>";

		}

		/* Are we remembering logins?  Lets check. */

		$remember = '';

		if ( isset($_COOKIE['agreeable_terms'] ) && $this->options['remember_me'] == 1 ) {
			$remember = ' checked ';
		}

		echo '<div style="clear: both; padding: .25em 0;" id="terms-accept" class="terms-form">';

		if($this->is_buddypress_registration()){do_action( 'bp_login_accept_errors' );}

		echo '<label style="text-align: left;"><input type="checkbox" name="login_accept" id="login_accept" '.$remember.' />&nbsp;<a title="'.get_post($this->options['terms_page'])->post_title.'" class="open-popup-link" target="_BLANK" href="'.$term_link.'">'.$this->options['message'].'</a></label>';
		echo '<input type="hidden" value="'.$type.'" name="ag_type" /></div>';
		echo '<div id="terms" class="mfp-hide">'.$terms_content.'</div>';
		echo $type == 'comments' ? '<br>':'';
	}


	function ag_login_terms_accept($errors){

		if($this->options['login'] == 1) {
			$this->ag_display_terms_form('login', $errors);
		}
	}

	function ag_comment_terms_accept(){

		if($this->options['comments'] == 1) {
			$this->ag_display_terms_form('comments');
		}
	}

	function ag_register_terms_accept($errors) {


		if($this->options['register'] == 1) {
			$this->ag_display_terms_form('register', $errors);
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

	function ag_widget_terms_accept() {

		if($this->options['login'] == 1) {
			$this->ag_display_terms_form('login');
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



	function agreeable_options() {
		add_options_page('agreeable', 'Agreeable', 'manage_options', 'terms-options', array($this, 'agoptions'));
	}

	function agoptions() {
		include('agreeable-options.php');
	}

	/* Plugin cross promotion area */

	function cross_promotions($plugin) {
		include('kp_cross_promote.php');
	}

	function is_login_page() {
		return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}

}

$agreeable = new Agreeable();
