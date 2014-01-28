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
#ag-form label {display: block; margin-bottom: .25em;}
#ag-form .checkboxes label {display: inline;}

#ag-form h3 {border-bottom: 1px solid #ccc; margin-bottom: .5em; padding-bottom: .5em; color: #369; text-shadow: 0 1px 1px #fff;}
#ag-form input[type="text"] {min-width: 400px;}
#ag-form input[type="submit"] {
	-moz-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	-webkit-box-shadow:inset 0px 1px 0px 0px #dcecfb;
	box-shadow:inset 0px 1px 0px 0px #dcecfb;
background: #7f9ddb; /* Old browsers */
/* IE9 SVG, needs conditional override of 'filter' to 'none' */
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzdmOWRkYiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiM3NDdlYmMiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  #7f9ddb 0%, #747ebc 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#7f9ddb), color-stop(100%,#747ebc)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #7f9ddb 0%,#747ebc 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #7f9ddb 0%,#747ebc 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #7f9ddb 0%,#747ebc 100%); /* IE10+ */
background: linear-gradient(to bottom,  #7f9ddb 0%,#747ebc 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#7f9ddb', endColorstr='#747ebc',GradientType=0 ); /* IE6-8 */
	border-radius: 3px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border:1px solid #84bbf3;
	display:inline-block;
	color:#ffffff;
	font-size:1.2em;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #528ecc;
	font-weight: 100;
	padding: 1em 2em;
}
</style>
<div class="wrap">

			
			<?php echo "<h2>" . __( 'Agreeable Settings', 'ag_trdom' ) . "</h2>"; ?>
			
			<form id="ag-form" name="ag_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="ag_hidden" value="Y">
				
				<?php    echo "<h3>" . __( 'Settings', 'ag_trdom' ) . "</h3>"; ?>
				
				<p><label for="ag_fail"><?php _e("Failed to agree error message: " ); ?></label><input type="text" name="ag_fail" value="<?php echo $dbfail; ?>" size="20"></br><?php _e("<span class='mes'>This is what shows up if they don't check the box</span>" ); ?></p>
				
				<p><label for="ag_url"><?php _e("URL for Terms: " ); ?></label><input type="text" name="ag_url" value="<?php echo $dburl; ?>" size="20"></br><?php _e("<span class='mes'>This is the URL where the user can read your terms</span>" ); ?></p>
				
				<p><label for="ag_termm"><?php _e("Message: " ); ?></label><input type="text"  name="ag_termm" size="40" value="<?php echo $dbtermm; ?>"><br><?php _e("<span class='mes'>This is the text that goes right after the checkbox</span>" ); ?></p>
				<br>

				<div class="checkboxes">
								<p>
				<h3><?php _e("Where should it be displayed? " ); ?></h3>
					<input type="checkbox" id="ag_login" name="ag_login" value="1" <?php if($dblogin == 1) {echo 'checked';} ?> /> <label for="ag_login">Login form</label><br>
					<input type="checkbox" id="ag_register" name="ag_register" value="1" <?php if($dbregister == 1) {echo 'checked';} ?> /> <label for="ag_register">Registration form</label>
					</p>
				</div>
			
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Update Options', 'ag_trdom' ) ?>" />
				</p>
			</form>

		</div>
	