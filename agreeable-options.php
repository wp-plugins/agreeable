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
		}
	?>
	
<style>

.mes {
color: gray;
font-style: italic;
font-size:. 9em;
}
#ag-form {
width: 50%;
}
textarea {
clear: right;
float: right;
}
input[type="submit"] {
margin-top: 2em;
}
#ag-form label {display: block; margin-bottom: .25em;}
#ag-form .checkboxes label {display: inline;}

#ag-form h3 {border-bottom: 1px solid #ccc; margin-bottom: .5em; padding-bottom: .5em; color: #369; text-shadow: 0 1px 1px #fff;}
#ag-form input[type="text"] {min-width: 400px;}
#feedback-form input[type="email"], #feedback-form textarea {width: 100%; display: block;}
#feedback-form textarea {min-height: 100px;}
.checkboxes {padding-bottom: 1em;}
</style>

<?php $pages = get_pages('status=publish&numberposts=-1&posts_per_page=-1'); ?>

<div class="wrap">
			
			<div style="position: fixed; right: 10%; top: 13%; width: 25%; min-height: 200px; padding: 1.5em; background: #fafafa; border: 1px solid #ccc;">
				<?php feedback_form(); ?>
			</div>
			
			<?php echo "<h2>" . __( 'Agreeable Settings', 'ag_trdom' ) . "</h2>"; ?>
			
			<form id="ag-form" name="ag_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="ag_hidden" value="Y">
				
				<?php    echo "<h3>" . __( 'Settings', 'ag_trdom' ) . "</h3>"; ?>
				
				<p><label for="ag_fail"><?php _e("Failed to agree error message: " ); ?></label><input type="text" name="ag_fail" value="<?php echo $dbfail; ?>" size="20"></br><?php _e("<span class='mes'>This is what shows up if they don't check the box</span>" ); ?></p>
				
				<p>
					<?php _e("<span class='mes'>This is the URL where the user can read your terms</span>" ); ?>
					<label for="ag_url">Select your terms page</label>
					<select name="ag_url">
						<?php foreach ($pages as $p) { ?>
							<option value="<?php echo $p->ID; ?>" <?php echo $dburl == $p->ID ? 'selected="selected"' : ''; ?>><?php echo $p->post_title; ?></option>
						<?php } ?>
					</select>
				</p>
				
				<p><label for="ag_termm"><?php _e("Message: " ); ?></label><input type="text"  name="ag_termm" size="40" value="<?php echo $dbtermm; ?>"><br><?php _e("<span class='mes'>This is the text that goes right after the checkbox</span>" ); ?></p>
				<br>

				<div class="checkboxes">
								<p>
				<h3><?php _e("Where should it be displayed? " ); ?></h3>
					<input type="checkbox" id="ag_login" name="ag_login" value="1" <?php if($dblogin == 1) {echo 'checked';} ?> /> <label for="ag_login"> Login form</label><br>
					<input type="checkbox" id="ag_register" name="ag_register" value="1" <?php if($dbregister == 1) {echo 'checked';} ?> /> <label for="ag_register">Registration form</label>
					</p>
				</div>				
			
				<p class="submit">
				<input type="submit" class="button button-large button-primary" name="Submit" value="<?php _e('Update Options', 'ag_trdom' ) ?>" />
				</p>
			</form>

		</div>
	