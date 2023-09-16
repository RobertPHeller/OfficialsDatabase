<?php
	/* Profile code */

class Officials_Profile {
  private $database;

  function __construct($db)
  {
	$this->database = $db;
  }

  function display_profile($userid)
  {
     $user_screenname = Officials_User::get_user_screenname($userid);
     ?><div id="profile">
	<div id="profile_picture"><?php 
		echo $this->profile_img($userid); ?></div>
	<div id="profile_info"><p class="profile_screenname">Screen Name: <?php echo $user_screenname; ?></p>
		<p id="profile_fullname" class="profile_fullname"><?php
			echo $this->profile_fullname($userid); ?></p>
		<div id="profile_bio" class="profile_bio"><?php 
			echo $this->profile_bio($userid); ?></div>
	</div>
	<?php
     if ($userid == Officials_User::current_user_id()) {
	?><script type="text/javascript">
	  function  CustomizeEditDialog()
	  {
	    var theDiv = document.getElementById('edit-profile');
	    var theHTML = '<h3 class="dialog-header"><h3 class="dialog-header">Edit profile for '+
			'<?php echo $user_screenname; ?>.<a href="#" class="closeDOMWindow button">Close</a></h3>'+
		'<iframe id="dialog-container" class="dialog-contents" src="'+
		  '<?php echo BASEURL; ?>edit_profile.php?inedit=true"></iframe>';
	    theDiv.innerHTML = theHTML;
	    $.openDOMWindow({
	      height:400, width:700,
	      windowSourceID:'#edit-profile',
	      loader:1,
	      loaderImagePath:DOM_LoadingAnimation,
	      loaderWidth:208,
	      loaderHeight:13,
	      windowPadding:0
	    });
	    return false;
	  }
	</script><a title="Edit Profile" class="profile-edit button"
	   href="#edit-profile" onClick="CustomizeEditDialog();" ><img class="button-icon" src="<?php echo BASEURL; ?>images/editicon.png" width="16" height="16" border="0" alt="edit icon" />Edit Profile</a>
	<div id="edit-profile" style="display: none;"></div><?php
     }

  }
  function profile_img($userid)
  {
     $user_screenname = Officials_User::get_user_screenname($userid);
     if (!file_exists(ABSPATH . 'userimages/' . $user_screenname)) {
	mkdir(ABSPATH . 'userimages/' . $user_screenname);
     }
     if (!file_exists(ABSPATH . 'userimages/' . $user_screenname . '/profile_normal.jpg')) {
	$profile_image_url = BASEURL . 'images/blankprofile.png';
	list($img_w, $img_h) = getimagesize(ABSPATH . 'images/blankprofile.png');
     } else {
	$profile_image_url = BASEURL . 'userimages/' . $user_screenname . '/profile_normal.jpg?nocache='.time();
	list($img_w, $img_h) = getimagesize(ABSPATH . 'userimages/' . $user_screenname . '/profile_normal.jpg');
     }
     $fullname = htmlentities(Officials_User::get_user_fullname($userid));
     return '<img src="'.$profile_image_url.'" width="'.$img_w.
	    '" height="'.$img_h.
	    '" alt="'.$fullname.
	    '" class="profile_picture" /><br clear="all" />';
  }
  function profile_thumbimg($userid)
  {
     $user_screenname = Officials_User::get_user_screenname($userid);
     if (!file_exists(ABSPATH . 'userimages/' . $user_screenname)) {
	mkdir(ABSPATH . 'userimages/' . $user_screenname);
     }
     if (!file_exists(ABSPATH . 'userimages/' . $user_screenname . '/profile_thumb.jpg')) {
	$profile_thumb_url = BASEURL . 'images/blankthumb.png';
	list($img_w, $img_h) = getimagesize(ABSPATH . 'images/blankthumb.png');
     } else {
	$profile_thumb_url = BASEURL . 'userimages/' . $user_screenname . '/profile_thumb.jpg?nocache='.time();
	list($img_w, $img_h) = getimagesize(ABSPATH . 'userimages/' . $user_screenname . '/profile_thumb.jpg');
     }
     $fullname = htmlentities(Officials_User::get_user_fullname($userid));
     return '<img src="'.$profile_thumb_url.'" width="'.$img_w.
	    '" height="'.$img_h.
	    '" alt="'.$fullname.
	    '" class="profile_thumb" />';
  }
  function profile_fullname($userid) {
    return 'Full Name: '.Officials_User::get_user_fullname($userid);
  }
  function profile_bio($userid) {
    return 'Bio: '.Officials_User::get_user_property($userid,'_bio');
  }

  function generate_edit_profileforms($userid)
  {
    ?><div id="ajax-messages"></div>
      <form id="upload-profile-picture" class="edit-profile-form"
	    action="<?php 
	    echo BASEURL . 'edit_profile.php?function=UploadProfilePicture'; 
	    ?>" method="post"><p><span id="edit-profile-image"><?php 
	echo $this->profile_img($userid); 
	?></span><p>Upload a new profile picture: </p>
     <div class="profile-picture-upload"><div class="fileinputs">
       <input type="file" class="file" name="profile-image"/></div>
       <input 
			type="submit" class="button"
			value="Upload Image" /></div></form><script type="text/javascript">
			var options = {
				success: DoEditorUpdates,
				dataType: 'xml'
				};
			$('#upload-profile-picture').ajaxForm(options);
		</script><br clear="all" />
      <div id="edit-user-full-name">
	<p><label for="fullname">Full Name:</label><input id="fullname" 
		type="text" name="fullname" value="<?php 
		echo Officials_User::get_user_fullname($userid); 
		?>" /><input type="button" class="button" value="Change full name"
			     onClick="Editor_Ajax('ChangeFullName','edit-user-full-name','<?php
				echo BASEURL . 'edit_profile.php'; ?>');" /></p>
      </div>
      <div id="edit-user-password">
	<p><label for="oldpassword">Old Password:</label><input 
		id="oldpassword" type="password" name="oldpassword" value="" /></p>

	<p><label for="newpassword">New Password:</label><input 
		id="newpassword" type="password" name="newpassword" value="" /></p>

	<p><label for="newpassword2">New Password (confirm):</label><input 
		id="newpassword" type="password" name="newpassword" value="" /></p>

               <input type="button" class="button" value="Change Password"
		onClick="Editor_Ajax('ChangePassword','edit-user-password','<?php
				echo BASEURL . 'edit_profile.php'; ?>');" />
      </div>
      <div id="edit-user-bio">
	<p><label for="bio">Bio:</label><textarea cols="60" rows="15" id="bio" name="bio"><?php
         echo Officials_User::get_user_property($userid,'_bio'); ?></textarea></p>
       <p><input type="button" class="button" value="Update Bio"
		onClick="Editor_Ajax('ChangeBio','edit-user-bio','<?php
				echo BASEURL . 'edit_profile.php'; ?>');" /></p>
      </div><?php
  }
  
  function generate_emailform($userid,$toid)
  {
  ?>
  <div id="ajax-messages"></div>
  <div id="send-email-form">
    <p>Sending E-Mail to <?php 
      echo Officials_User::get_user_screenname($toid); ?>.</p>
    <p><label for="subject">Subject:</label><input id="subject" type="text" name="subject" value="" /></p>
    <p><label for="messagebody">Message:</label><textarea cols="60" rows="15" id="messagebody" name="messagebody"></textarea></p>
    <p><input type="button" class="button" value="Send" 
     onClick="Email_Ajax(<?php echo $toid;?>,'send-email-form','<?php 
       echo BASEURL . 'send_email.php'; ?>');"/></p></div><?php
 }
  

  function change_bio($userid)
  {
    $this->xmlStart();
    $message = ''; $updates = ''; $deletes = ''; $adds = ''; $clearforms = '';
    $oldbio = Officials_User::get_user_property($userid,'_bio');
    $newbio = $officials_database->get_post_var('bio',$oldbio);
    //file_put_contents("php://stderr","*** Officials_Profile::change_bio(): newbio = $newbio\n");
    //file_put_contents("php://stderr","*** -: this is ".print_r($this,true)."\n");
    //file_put_contents("php://stderr","*** -: this->database is ".print_r($this->database)."\n");
    if ($this->database->hasOtherTags($newbio)) {
      $message .= '<p class="error">Bad update request:  invalid Bio, can only contain b, big, br, cite, code, hr, i, p, pre, small, and strong tags!</p>';
    } else {
      $sanitizedBio = $this->database->sanitizeString($newbio);
      Officials_User::update_user_property($userid,'_bio',$sanitizedBio);
      $updates .= '<update><id>profile_bio</id><content>'.
			$this->profile_bio($userid).'</content></update>';
      $message .= '<p>Bio Updated.</p>';
    }
    if ($message != '') {
      echo '<messages>'.$message.'</messages>';
    }
    if ($updates != '') echo $updates;
    if ($deletes != '') echo $deletes;
    if ($adds    != '') echo $adds;
    if ($clearforms!= '') echo $clearforms;
    $this->xmlFinish();
  }

  function change_password($userid)
  {
    $this->xmlStart();
    $message = ''; $updates = ''; $deletes = ''; $adds = ''; $clearforms = '';
    $currentPassword = stripslashes(Officials_User::get_user_by_id($userid)->password);
    $oldpassword = $officials_database->get_post_var('oldpassword','');
    $newpassword = $officials_database->get_post_var('newpassword','');
    $newpassword2 = $officials_database->get_post_var('newpassword2','');
    if ($currentPassword != $oldpassword) {
      $message .= '<p class="error">Old password does not match!</p>';
    } else if ($newpassword != $newpassword2) {
      $message .= '<p class="error">New passwords do not match!</p>';
    } else if (strlen($newpassword) < 6) {
      $message .= '<p class="error">Passwords must be at least 6 characters!</p>';
    } else if (!preg_match("/[a-z]/",$newpassword) ||
	       !preg_match("/[A-Z]/",$newpassword) ||
	       !preg_match("/[0-9]/",$newpassword)) {
      $message .= '<p class="error">Passwords require 1 each of a-z, A-Z, and 0-9!</p>';
    } else {
      $userobj = (OBJECT) array('password' => $this->database->sanitizeString($newpassword));
      Officials_User::update_user($userid,$userobj);
      $message .= '<p>Password changed.</p>';
    }
    if ($message != '') {
      echo '<messages>'.$message.'</messages>';
    }
    if ($updates != '') echo $updates;
    if ($deletes != '') echo $deletes;
    if ($adds    != '') echo $adds;
    if ($clearforms!= '') echo $clearforms;
    $this->xmlFinish();
  }

  function change_fullname($userid)
  {
    $this->xmlStart();
    $message = ''; $updates = ''; $deletes = ''; $adds = ''; $clearforms = '';
    if (isset($_POST['fullname'])) {
      $newFullname = $this->sanitizeStringNoTagsHE($officials_database->get_post_var('fullname'));
      $userobj = (OBJECT) array('fullname' => $newFullname);
      Officials_User::update_user($userid,$userobj);
      $updates .= '<update><id>profile_fullname</id><content>'.
		$this->profile_fullname($userid).
		'</content></update>';
      $message .= '<p>Full name updated.</p>';
    }
    if ($message != '') {
      echo '<messages>'.$message.'</messages>';
    }
    if ($updates != '') echo $updates;
    if ($deletes != '') echo $deletes;
    if ($adds    != '') echo $adds;
    if ($clearforms!= '') echo $clearforms;
    $this->xmlFinish();
  }

  function upload_profile_picture($userid)
  {
    $this->xmlStart();
    $message = ''; $updates = ''; $deletes = ''; $adds = ''; $clearforms = '';
    //$message .= '<pre>_POST is '.print_r($_POST,true).'</pre>';
    //$message .= '<pre>_SESSION is '.print_r($_SESSION,true).'</pre>';
    //$message .= '<pre>_FILES is '.print_r($_FILES,true).'</pre>';

    //file_put_contents("php://stderr","*** Officials_Profile::upload_profile_picture: _FILES is ".print_r($_FILES,true)."\n");
    if (isset($_FILES['profile-image']['name']))
    {
      $user_screenname = Officials_User::get_user_screenname($userid);
      $newimage = ABSPATH . 'userimages/' . $user_screenname . '/profile_new.jpg';
      $rawimage = ABSPATH . 'userimages/' . $user_screenname . '/profile_raw.jpg';
      $normal   = ABSPATH . 'userimages/' . $user_screenname . '/profile_normal.jpg';
      $thumb    = ABSPATH . 'userimages/' . $user_screenname . '/profile_thumb.jpg';
      //file_put_contents("php://stderr","*** -: newimage = $newimage, rawimage = $rawimage, normal = $normal, thumb = $thumb\n");
      move_uploaded_file($_FILES['profile-image']['tmp_name'], $newimage);
      //file_put_contents("php://stderr","*** -: moved to newimage\n");
      $typeok = TRUE;
      switch ($_FILES['profile-image']['type'])
      {
	case "image/gif": $src = imagecreatefromgif($newimage); break;
	case "image/jpeg":
	case "image/pjpeg": $src = imagecreatefromjpeg($newimage); break;
	case "image/png": $src = imagecreatefrompng($newimage); break;
	default: $typeok = FALSE;
      }
      if (!$typeok) {
	$message .= '<p class="error">Only GIF, JPEG, and PNG are supported.</p>';
      } else {
	imagejpeg($src,$rawimage);	// Save original as raw
	//file_put_contents("php://stderr","*** -: saved rawimage\n");
	list($w, $h) = getimagesize($newimage);
	//file_put_contents("php://stderr","*** -: w = $w, h = $h\n");
	$max = 150;
	$nw  = $w;
	$nh  = $h;
	if ($w > $h && $max < $w) {
	  $nh = $max / $w * $h;
	  $nw = $max;
	} else if ($h > $w && $max < $h) {
	  $nw = $max / $h * $w;
	  $nh = $max;
	} else if ($max < $w) {
	  $nw = $nh = $max;
	}
	//file_put_contents("php://stderr","*** -: norm size is $nw, $nh\n");
	$norm = imagecreatetruecolor($nw, $nh);
	imagecopyresampled($norm, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
	imageconvolution($norm, array(	// Sharpen image
					array(-1,-1,-1),
					array(-1,16,-1),
					array(-1,-1,-1)
					),8,0);
	imagejpeg($norm,$normal);
	//file_put_contents("php://stderr","*** -: saved normal\n");
	imagedestroy($norm);
	//file_put_contents("php://stderr","*** -: destroied norm\n");
	$max = 32;
	$tw  = $w;
	$th  = $h;
	if ($w > $h && $max < $w) {
	  $th = $max / $w * $h;
	  $tw = $max;
	} else if ($h > $w && $max < $h) {
	  $tw = $max / $h * $w;
	  $th = $max;
	} else if ($max < $w) {
	  $tw = $th = $max;
	}
	//file_put_contents("php://stderr","*** -: thumb size is $tw, $th\n");
	$thmb = imagecreatetruecolor($tw, $th);
	imagecopyresampled($thmb, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
	imageconvolution($thmb, array(	// Sharpen image
					array(-1,-1,-1),
					array(-1,16,-1),
					array(-1,-1,-1)
					),8,0);
	imagejpeg($thmb,$thumb);
	//file_put_contents("php://stderr","*** -: saved thumb\n");
	imagedestroy($thmb);
	imagedestroy($src);
	//file_put_contents("php://stderr","*** -: destroied temps\n");
	$updates .= '<update><id>profile_picture</id><content>'.
				$this->profile_img($userid).
				'</content></update>';
	$updates .= '<update><id>edit-profile-image</id><content>'.
				$this->profile_img($userid).
				'</content></update>';
	$message .= '<p>Profile image updated.</p>';
      }
    }


    if ($message != '') {
      echo '<messages>'.$message.'</messages>';
    }
    if ($updates != '') echo $updates;
    if ($deletes != '') echo $deletes;
    if ($adds    != '') echo $adds;
    if ($clearforms!= '') echo $clearforms;
    $this->xmlFinish();
  }
  function xmlStart()
  {
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UFT-8" ?>';
    echo '<editprofile>';
  }

  function xmlFinish()
  {
    echo '</editprofile>';
  }
}
