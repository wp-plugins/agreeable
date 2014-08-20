<?php 
	    if(isset($_POST['ag_hidden']) && $_POST['ag_hidden'] == 'Y') {
			//Form data sent
			 
          $dbfail = $_POST['ag_fail'];
          
          $dbfail = stripslashes($dbfail);
            update_option('ag_fail', $dbfail);
          
          $dbtermm = $_POST['ag_termm'];
          
          $dbtermm = stripslashes($dbtermm);
          update_option('ag_termm', $dbtermm);
            
          $dburl = $_POST['ag_url'];
          update_option('ag_url', $dburl);
            
          $dblogin = $_POST['ag_login'];      
          update_option('ag_login', $dblogin);

          $dbregister = $_POST['ag_register'];
          update_option('ag_register', $dbregister);

          $dbcomments = $_POST['ag_comments'];
          update_option('ag_comments', $dbcomments);
          
          $dblightbox = $_POST['ag_lightbox'];
          update_option('ag_lightbox', $dblightbox);
          
          $dbcolors = array('text-color' => $_POST['ag_text_color'], 'bg-color' => $_POST['ag_bg_color']);
          update_option('ag_colors', $dbcolors);
          
          $dbremember = $_POST['ag_remember'];
          update_option('ag_remember', $dbremember);
          
?> 
		
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
			<?php
		} else {
		//Normal page display
		  $dbfail = get_option('ag_fail');
		  $dbtermm = get_option('ag_termm');
		  $dburl = get_option('ag_url');
		  $dblogin = get_option('ag_login');
		  $dbregister = get_option('ag_register');
		  $dbcomments = get_option('ag_comments');
		  $dblightbox = get_option('ag_lightbox');
		  $dbcolors = get_option('ag_colors');
		  $dbremember = get_option('ag_remember');
		  
		  if(empty($dbcolors)) {
			  $dbcolors['text-color'] = '#333333';
			  $dbcolors['bg-color'] = '#FFFFFF';
		  }
 		  		  
		}
	?>

<?php $pages = get_pages('status=publish&numberposts=-1&posts_per_page=-1'); ?>

<div class="wrap agreeable-settings">
			<div class="ag-plugin-banner">
				<img src="<?php echo plugins_url('/images/banner.png', __FILE__); ?>" alt="Agreeable" />
			</div>
			<div class="ag_feedback_form">
				<?php ag_feedback_form(); ?>
			</div>
			
			
			<form id="ag-form" name="ag_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="ag_hidden" value="Y">
				
				<?php    echo "<h3>" . __( 'Settings', 'agreeable' ) . "</h3>"; ?>
				
				<p><label for="ag_fail"><?php _e("Failed to agree error message: ", 'agreeable' ); ?></label><input type="text" name="ag_fail" value="<?php echo $dbfail; ?>" size="20"></br><span class='mes'><?php _e("This is what shows up if they don't check the box", 'agreeable' ); ?></span></p>
				
				<p>
					<label for="ag_url"><?php _e("Select your terms page", 'agreeable'); ?></label>
					<select name="ag_url">
						<?php foreach ($pages as $p) { ?>
							<option value="<?php echo $p->ID; ?>" <?php echo $dburl == $p->ID ? 'selected="selected"' : ''; ?>><?php echo $p->post_title; ?></option>
						<?php } ?>
					</select>
					<br><span class='mes'><?php _e("Create a page for your terms and conditions and select it here.", 'agreeable' ); ?></span>
				</p>
				
				<p><label for="ag_termm"><?php _e("Message: ", 'agreeable' ); ?></label><input type="text"  name="ag_termm" size="40" value="<?php echo $dbtermm; ?>"><br><span class='mes'><?php _e("This is the text that goes right after the checkbox", 'agreeable' ); ?></span></p>
				
				
				<p class="ag-checkboxes">
					<input type="checkbox" id="ag_remember" name="ag_remember" value="1" <?php if($dbremember == 1) {echo 'checked';} ?> />
					<label for="ag_remember"><?php _e("Remember agreement for 30 days", 'agreeable'); ?></label>
				</p>
				
				<div class="ag-color-options ag-checkboxes">
					<h3><?php _e("Lightbox Options", 'agreeable'); ?></h3>
					<p class="ag-checkboxes">
						<input type="checkbox" id="ag_lightbox" name="ag_lightbox" value="1" <?php if($dblightbox == 1) {echo 'checked';} ?> />
						<label for="ag_lightbox"><?php _e("Active?", 'agreeable'); ?></label>
						
						<br><span class='mes'><?php _e("If checked, the terms will pop up in a responsive lightbox.  If unchecked the message will link to your terms page.", 'agreeable' ); ?></span></p>
					</p>
					
					<input type="color" name="ag_text_color" id="ag_text_color" value="<?php echo $dbcolors['text-color']; ?>"/>
					<label for="ag_text_color"><?php _e("Text color", 'agreeable'); ?></label>
					<br><br>
					
					<input type="color" name="ag_bg_color" id="ag_bg_color" value="<?php echo $dbcolors['bg-color']; ?>" />
					<label for="ag_bg_color"><?php _e("Background color", 'agreeable'); ?></label>
				</div>	

				<div class="ag-checkboxes">
								
				<h3><?php _e("Where should it be displayed? ", 'agreeable' ); ?></h3>
					<p>
						<input type="checkbox" id="ag_login" name="ag_login" value="1" <?php if($dblogin == 1) {echo 'checked';} ?> /> <label for="ag_login"> <?php _e("Login form", 'agreeable'); ?></label><br>
						<input type="checkbox" id="ag_register" name="ag_register" value="1" <?php if($dbregister == 1) {echo 'checked';} ?> /> <label for="ag_register"><?php _e("Registration form", 'agreeable'); ?></label>
						<br>
						<input type="checkbox" id="ag_comments" name="ag_comments" value="1" <?php if($dbcomments == 1) {echo 'checked';} ?> /> <label for="ag_comments"><?php _e("Comment form", 'agreeable'); ?></label>
					</p>
				</div>				
			
				<p class="submit">
				<input type="submit" class="button button-large button-primary" name="Submit" value="<?php _e('Update Options', 'agreeable' ) ?>" />
				</p>
			</form>

		</div>
	