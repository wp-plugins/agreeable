jQuery(document).ready(function($) {
	$('.open-popup-link').magnificPopup({
	  type:'inline',
	  midClick: true
	});	
	
	if($('.woocommerce .login')) {
		$(".woocommerce>#terms-accept").insertBefore(".woocommerce .login #rememberme");

	}
	
});