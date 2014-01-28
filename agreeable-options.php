<?php 
	    if($_POST['ag_hidden'] == 'Y') {
			//Form data sent
			 
            $dbfail = $_POST['ag_fail'];
          $dbtermm = stripslashes($dbfail);
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
form {
width: 725px;
}
textarea {
clear: right;
float: right;
}
input[type="submit"] {
margin-top: 2em;
}
</style>
<div class="wrap">

			
			<?php echo "<h2>" . __( 'Agreeable', 'ag_trdom' ) . "</h2>"; ?>
			
			<form name="ag_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="ag_hidden" value="Y">
				
				<?php    echo "<h4>" . __( 'Settings', 'ag_trdom' ) . "</h4>"; ?>
				
				<p><label for="ag_fail"><?php _e("Failed to agree error message: " ); ?></label><input type="text" name="ag_fail" value="<?php echo $dbfail; ?>" size="20"></br><?php _e("<span class='mes'>This is what shows up if they don't check the box</span>" ); ?></p>
				
				<p><label for="ag_url"><?php _e("URL for Terms: " ); ?></label><input type="text" name="ag_url" value="<?php echo $dburl; ?>" size="20"></br><?php _e("<span class='mes'>This is the URL where the user can read your terms</span>" ); ?></p>
				
				<p><label for="ag_termm"><?php _e("Message: " ); ?></label><input type="text"  name="ag_termm" size="40" value="<?php echo $dbtermm; ?>"><br><?php _e("<span class='mes'>This is the text that goes right after the checkbox</span>" ); ?></p>
				<br>
				<p>
				<h4><?php _e("Where should it be displayed? " ); ?></h4>

				<input type="checkbox" id="ag_login" name="ag_login" value="1" <?php if($dblogin == 1) {echo 'checked';} ?> /> <label for="ag_login">Login form</label><br>
				<input type="checkbox" id="ag_register" name="ag_register" value="1" <?php if($dbregister == 1) {echo 'checked';} ?> /> <label for="ag_register">Registration form</label>
				</p>

			
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Update Options', 'ag_trdom' ) ?>" />
				</p>
			</form>

		</div>
	