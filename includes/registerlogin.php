<?php
    /* Login and register forms */

    function officials_register()
    {
	$defaults = array(
		"screenname" => "",
		"password"   => "",
		"password2"  => "",
		"fullname"   => "",
		"email"      => "" ); 
	if (isset($_REQUEST['screenname'])) {
	   $defaults['screenname'] = $_REQUEST['screenname'];
	}
	if (isset($_REQUEST['fullname'])) {
	   $defaults['fullname'] = $_REQUEST['fullname'];
	}
	if (isset($_REQUEST['email'])) {
	   $defaults['email'] = $_REQUEST['email'];
	}
	$ret = (isset($_REQUEST['returnto'])?$_REQUEST['returnto']:BASEURL);
	?><div id="register">
	<form action="<?php echo BASEURL . 'register.php'; ?>" method="POST">
	<input type="hidden" name="returnto" value="<?php echo $ret; ?>" />
	<table class="loginformtable">
	   <tr><th><label for="screenname">Screen Name</label></th>
	       <td><input type="text" id="screenname" name="screenname" 
			  value="<?php echo $defaults['screenname']; ?>" /></td></tr>
	   <tr><th></th><td style="font-size: small;">Screen names must be at least 5 characters and only contain letters, digits, - and _ characters.</td></tr>
	   <tr><th><label for="password">Password</label></th>
	       <td><input type="password" id="password" name="password"
			  value="<?php echo $defaults['password']; ?>" /></td></tr>
	   <tr><th></th><td style="font-size: small;">Passwords need to be at least 6 characters and need to include at least one upper case letter, one lower case letter, and one digit.</td></tr>
	   <tr><th><label for="password2">Password Confirm</label></th>
	       <td><input type="password" id="password2" name="password2"
			  value="<?php echo $defaults['password2']; ?>" /></td></tr>
	   <tr><th><label for="fullname">Full Name</label></th>
	       <td><input type="text" id="fullname" name="fullname"
			  value="<?php echo $defaults['fullname']; ?>" /></td></tr>
	   <tr><th><label for="email">E-Mail Address</label></th>
	       <td><input type="text" id="email" name="email"
			  value="<?php echo $defaults['email']; ?>" /></td></tr>
	   <tr><td colspan="2"><input class="loginbutton button" type="submit" 
				      value="Register" name="register" /></td></tr>
	</table>
	</form>
	</div><?php
    }

    function officials_login()
    {
	$defaults = array(
		"screenname" => "",
		"password"   => "" ); 
	if (isset($_REQUEST['screenname'])) {
	   $defaults['screenname'] = $_REQUEST['screenname'];
	}
	$ret = (isset($_REQUEST['returnto'])?$_REQUEST['returnto']:BASEURL);
	?><div id="login">
	<form action="<?php echo BASEURL . 'login.php'; ?>" method="POST">
	<input type="hidden" name="returnto" value="<?php echo $ret; ?>" />
	<table class="loginformtable">
	   <tr><th><label for="screenname">Screen Name</label></th>
	       <td><input type="text" id="screenname" name="screenname" 
			  value="<?php echo $defaults['screenname']; ?>" /></td></tr>
	   <tr><th><label for="password">Password</label></th>
	       <td><input type="password" id="password" name="password"
			  value="<?php echo $defaults['password']; ?>" /></td></tr>
	   <tr><td colspan="2"><input class="loginbutton button" type="submit" 
				      value="Login" name="login" /></td></tr>
	</table>
	</form>
	<a href="<?php echo BASEURL . 'forgotlogin.php'; ?>">Forgot Screen Name or Password?</a>
	</div><?php
    }
