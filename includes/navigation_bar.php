<?php
  /* Navigation Bar */

  

  function officials_navigation_bar()
  {
    global $officials_database;
    $officials_profile = new Officials_Profile($officials_database);
     ?><!-- Navigation bar begin --><div id="navigation_bar">
     <ul id="navigation">
        <li><a href="<?php echo BASEURL; ?>" title="Home">Home</a></li>
	<li><a href="<?php echo BASEURL . 'officials.php'; ?>" title="Officials">Officials</a></li>
	<li><a href="<?php echo BASEURL . 'newofficial.php'; ?>" title="New/Edit Official">New/Edit Official</a></li>
	<li><a href="<?php echo BASEURL . 'offices.php'; ?>" title="Offices">Offices</a></li>
	<li><a href="<?php echo BASEURL . 'newoffice.php'; ?>" title="New/Edit Office">New/Edit Office</a></li>
        <li><a href="<?php echo BASEURL . 'about.php'; ?>" title="About">About</a></li>
        <li><a href="<?php echo BASEURL . 'USAGE.php'; ?>" title="Usage">Usage</a></li>
        <?php if (file_exists(THEMEPATH . "extra_navigation.php")) {
            @require_once(THEMEPATH . "extra_navigation.php");
        } ?>
     </ul>
     <?php 
	if (Officials_User::is_logged_in()) {
	  ?> <ul id="admin">
	    <li class="howdy"><span>Howdy, <?php echo Officials_User::current_user_screenname(); 
            ?></span><?php 
              echo $officials_profile->profile_thumbimg(Officials_User::current_user_id()); 
            ?></li>
	    <li><a href="<?php echo BASEURL . 'profile.php'; ?>" title="Profile">Profile</a></li>
	    <li><a href="<?php echo BASEURL . 'logout.php'; ?>" title="Logout">Logout</a></li>
	    <?php if ((Officials_User::current_user_access() & USR_ADMIN) != 0) {
		?><li><a href="<?php echo BASEURL . 'admin/index.php'; ?>" title="Admin">Admin</a></li><?php
		} ?>
	  </ul> <?php
	}
     ?>
     <!-- Navigation bar end --></div>
     <?php
  }

   
