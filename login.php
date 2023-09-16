<?php 
    /* Handle Login */

        require_once(dirname(__FILE__) . '/officials-load.php');

    $message = "";

    global $officials_database;

    if (isset($_POST['login'])) {
	global $officials_database;
	$screenname = ""; $password = "";
	if (isset($_POST['screenname'])) {
	  $screenname = $officials_database->sanitizeString($officials_database->get_post_var('screenname'));
	}
	if (isset($_POST['password'])) {
	  $password = $officials_database->sanitizeString($officials_database->get_post_var('password'));
	}
        if ($screenname == "" || $password == "") {
	  $message = '<p class="error">Please enter a screenname and a password to login!</p><br clear="all" />'."\n";
	} else {
	  $userid = World_User::validate($screenname, $password);
	  switch ($userid) {
	    case -1:
		$message = '<p class="error">No such user!</p><br clear="all" />'."\n";
		break;
	    case -2:
		$message = '<p class="error">Wrong password!</p><br clear="all" />'."\n";
		break;
	    default:
		header('Location: '.$_REQUEST['returnto']);
		die();
	   }
 	}
    }	  

    require_once(INCPATH . 'officials-head.php' );

    ?><body <?php officials_body_class(); ?>>

    <?php officials_page_top(); ?>
    <?php officials_navigation_bar(); ?>

    <?php 
	echo $message;
	officials_register();
	officials_login();
	?>	

    <?php officials_footer(); ?>
    </body></html>

