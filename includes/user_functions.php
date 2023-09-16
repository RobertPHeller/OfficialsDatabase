<?php
   /* User functions */
   
require_once (INCPATH . 'PasswordHash.php');

global $hash_cost_log2, $hash_portable;
$hash_cost_log2 = 8;
$hash_portable   = true;

class Officials_User {
  
  static function validate($screenname, $password)
  {
     global $officials_database;
     $qr = $officials_database->queryMySQL("SELECT id, password, access, origx, origy FROM users where screenname = '$screenname'");
     if ($qr->num_rows == 0) {return -1;}
     $result = $qr->fetch_object();
     $qr->free();
     file_put_contents("php://stderr","*** Officials_User::validate: result is ".print_r($result,true)."\n");
     if (Officials_User::CheckPassword($password,$result->password)) {
	$_SESSION['screenname'] = $screenname;
	$_SESSION['userid'] = $result->id;
	$_SESSION['access'] = $result->access;
	$_SESSION['locx']   = $result->origx;
	$_SESSION['locy']   = $result->origy;
        file_put_contents("php://stderr","*** Officials_User::validate: _SESSION is ".print_r($_SESSION,true)."\n");
	return $result->id;
     } else {
	return -2;
     }
  }
  static function get_user_access($userid)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT access FROM users where id = $userid");
    if ($qr->num_rows == 0) {return 0;}
    $result = $qr->fetch_object();
    $qr->free();
    return $result->access;
  }
  static function BlankUser()
  {
    return (OBJECT) array(
	'id' => 0,
	'screenname' => '',
	'password' => '',
	'fullname' => '',
	'email' => '',
	'access' => 0);
  }
  static function get_user_by_id($id)
  {
    global $officials_database; 
    $qr = $officials_database->queryMySQL("SELECT * FROM users where id = $id");
    if ($qr->num_rows == 0) {return -1;}
    $result = $qr->fetch_object();
    $qr->free();
    return $result;
  }
  static function find_users_by_screenname($pattern)
  {
    file_put_contents("php://stderr","*** Officials_User::find_users_by_screenname('$pattern')\n");
    global $officials_database;
    $qr = $officials_database->queryMySQL(
		"SELECT id FROM users where screenname LIKE '".
		$officials_database->sanitizeStringNoTagsHE($pattern)."'");
    $rows = $qr->num_rows;
    $result = array();
    while ($row = $qr->fetch_row()) {
      $result[] = $row[0];
    }
    $qr->free();
    return $result;    
  }
  static function find_user_by_email($email)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL(
		"SELECT id FROM users where email = '".
		$officials_database->sanitizeStringNoTagsHE($email)."'");
    $rows = $qr->num_rows;
    if ($rows == 0) {
      $result = 0;
    } else {
      $result = $qr->fetch_row()[0];
    }
    $qr->free();
    return $result;    
  }
  static function current_user_id()
  {
     if (isset($_SESSION['userid'])) {
	return $_SESSION['userid'];
     } else {
	return 0;
     }
  }
  static function current_user_screenname()
  {
     if (isset($_SESSION['screenname'])) {
	return $_SESSION['screenname'];
     } else {
	return NULL;
     }
  }
  static function current_user_access()
  {
     if (isset($_SESSION['access'])) {
	return $_SESSION['access'];
     } else {
	return 0;
     }
  }
  static function is_logged_in()
  {
     return isset($_SESSION['userid']);
  }
  static function get_user_email($id)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT email FROM users where id = $id");
    if ($qr->num_rows == 0) {return NULL;}
    $result = $qr->fetch_object();
    $qr->free();
    return stripslashes($result->email);
  }
  static function get_user_fullname($id)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT fullname FROM users where id = $id");
    if ($qr->num_rows == 0) {return NULL;}
    $result = $qr->fetch_object();
    $qr->free();
    return stripslashes($result->fullname);
  }
  static function get_user_screenname($id)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT screenname FROM users where id = $id");
    if ($qr->num_rows == 0) {return NULL;}
    $result = $qr->fetch_object();
    $qr->free();
    return stripslashes($result->screenname);
  }
  static function validate_user_email($id)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT access FROM users where id = $id");
    if ($qr->num_rows == 0) {return false;}
    $result = $qr->fetch_object();
    $qr->free();
    $access = $result->access | USR_VERIFIED;
    $qr = $officials_database->queryMySQL("UPDATE users set access = $access where id = $id");
    //$qr->free();
    return true;
  }
  
  static function update_user($id,$userobj,$force=false)
  {
    //file_put_contents("php://stderr","*** Officials_User::update_user($id,".print_r($userobj,true).")\n");
    global $officials_database;
    //file_put_contents("php://stderr","*** Officials_User::update_user(): current user is ".Officials_User::current_user_id()."\n");
    //file_put_contents("php://stderr","*** Officials_User::update_user(): current user access is ".Officials_User::current_user_access()."\n");
    if ($id == Officials_User::current_user_id() ||
	(Officials_User::current_user_access() & USR_ADMIN) != 0 ||
        $force) {
      $q = "UPDATE users set ";
      $comma = "";
      if (isset($userobj->password)) {
        //file_put_contents("php://stderr","*** Officials_User::update_user(): about to hash '$userobj->password':\n");
        $hash = Officials_User::HashPassword($userobj->password);
        $q .= $comma."password = '".$hash."'";
        $comma = ", ";
      }
      if (isset($userobj->fullname)) {
        $q .= $comma."fullname = '".$officials_database->sanitizeStringNoTagsHE($userobj->fullname)."'";
        $comma = ", ";
      }
      $q .= " where id = $id";
      //file_put_contents("php://stderr","*** Officials_User::update_user(): q is ".print_r($q,true)."\n");
      $qr = $officials_database->queryMySQL($q);
      //$qr->free();
    }
  }
  static function create_new_user($userobj)
  {
    global $officials_database;
    $existingUsers = Officials_User::find_users_by_screenname($userobj->screenname);
    if (count($existingUsers) > 0) {
      return -1;
    }
    $emailId = Officials_User::find_user_by_email($userobj->email);
    if ($emailId != 0) {
      return -2;
    }
    
    $hash = Officials_User::HashPassword($userobj->password);
    
    $q = "INSERT INTO users (screenname,password,fullname,email) VALUES (";
    $q .= "'".$officials_database->sanitizeStringNoTagsHE($userobj->screenname)."',";
    $q .= "'".$hash."',";
    $q .= "'".$officials_database->sanitizeStringNoTagsHE($userobj->fullname)."',";
    $q .= "'".$officials_database->sanitizeStringNoTagsHE($userobj->email).')';
    $qr = $officials_database->queryMySQL($q);
    $newuserid = $officials_database->db->insert_id;
    //$qr->free();
    return $newuserid;
  }
  
  static function HashPassword($password) {
    //file_put_contents("php://stderr","*** Officials_User::HashPassword('$password')\n");
    global $hash_cost_log2, $hash_portable;
    $hasher = new PasswordHash($hash_cost_log2, $hash_portable);
    $hash = $hasher->HashPassword($password);
    unset($hasher);
    //file_put_contents("php://stderr","*** Officials_User::HashPassword returns $hash\n");
    return $hash;
  }
  
  static function CheckPassword($pass, $hash) {
    //file_put_contents("php://stderr","*** Officials_User::CheckPassword('$pass','$hash')\n");
    global $hash_cost_log2, $hash_portable;
    $hasher = new PasswordHash($hash_cost_log2, $hash_portable);
    $result = $hasher->CheckPassword($pass, $hash);
    unset($hasher);
    return $result;
  }

  static function get_user_property($userid,$property_name)
  {
    global $officials_database;
    $q = "SELECT value FROM user_properties WHERE userid = $userid && name = '".$officials_database->sanitizeStringNoTagsHE($property_name)."'";
    $qr = $officials_database->queryMySQL($q);
    if ($qr->num_rows > 0) {
      $temp = $qr->fetch_row()[0];
      $qr->free();
      return stripslashes($temp);
    } else {
      return '';
    }
  }
  static function update_user_property($userid,$property_name,$sanitizedValue)
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT value FROM user_properties WHERE userid = $userid && name = '".$officials_database->sanitizeStringNoTagsHE($property_name)."'");
    if ($qr->num_rows == 0) { // Property does not exist, insert fresh
      $q = "INSERT INTO user_properties (userid, name, value) VALUES ($userid, '";
      $q .= $officials_database->sanitizeStringNoTagsHE($property_name)."','";
      $q .= $sanitizedValue."')";
    } else {
      $qr->free();
      $q = "UPDATE user_properties SET value = '".$sanitizedValue."' WHERE userid = $userid && name = '".$officials_database->sanitizeStringNoTagsHE($property_name)."'";
    }
    $qr = $officials_database->queryMySQL($q);
  }
  static function user_counts()
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT count(*) FROM users");
    $result = $qr->fetch_row()[0];
    $qr->free();
    return $result;
  }
  static function get_all_users()
  {
    global $officials_database;
    $qr = $officials_database->queryMySQL("SELECT * FROM users");
    $result = array();
    $rows = $qr->num_rows;
    for ($ir = 0; $ir < $rows; $ir++) {
      $result[] = $qr->fetch_object();
    }
    return $result;
  }
  static function SendLoginCreds($userid,$newpassword=false)
  {
    //file_put_contents("php://stderr","*** Officials_User::SendLoginCreds($userid)\n");
    global $officials_database;
    $userobj = Officials_User::get_user_by_id($userid);
    //file_put_contents("php://stderr","*** -: ".print_r($userobj,true)."\n");
    $body  = 'Dear '.stripslashes($userobj->fullname)."\n";
    $body .= "  Welcome to ".officials_title()."!\n";
    $body .= "\n";
    $body .= "Your screenname is ".stripslashes($userobj->screenname)."\n";
    $body .= "\n";
    if ($newpassword) {
      $validtimeout = time() + 72*60*60;
      $m = base64_encode("passwordtimeout=$validtimeout&userid=$userid");
      $validatepage = 'http://'.$_SERVER['HTTP_HOST'].BASEURL.'create_new_password.php';
      $validateurl = 'http://'.$_SERVER['HTTP_HOST'].BASEURL.'create_new_password.php?m='.$m;
      $body .= "You can reset your password by clicking on this link:\n";
      $body .= $validateurl."\n";
      $body .= "(or by copying and pasting the link into your browser's location field).\n";
      $body .= "\n";
      $body .= "Alternitively, you can visit the page at this URL:\n";
      $body .= $validatepage."\n";
      $body .= "And pasting the code $m into the form there.\n";
    } else {
      $body .= "You can login at this URL:\n";
      $body .= 'http://'.$_SERVER['HTTP_HOST'].BASEURL."login.php\n";
    }
    $headers = "From: PhpOfficials@".$_SERVER['HTTP_HOST']."\r\n";
    mail(stripslashes($userobj->email),"Welcome to ".officials_title(),$body,$headers);
  }
  static function SendValidationMessage($userid)
  {
    $fullname = Officials_User::get_user_fullname($userid);
    $email = Officials_User::get_user_email($userid);
    $validtimeout = time() + 72*60*60;
    $m = base64_encode("validtimeout=$validtimeout&newuserid=$userid");
    $validatepage = 'http://'.$_SERVER['HTTP_HOST'].BASEURL.'validate_email.php';
    $validateurl = 'http://'.$_SERVER['HTTP_HOST'].BASEURL.'validate_email.php?m='.$m;
    $body  = 'Dear '.$fullname."\n";
    $body .= "  Validate your E-Mail address by clicking on this link:\n";
    $body .= $validateurl."\n";
    $body .= "(or by copying and pasting the link into your browser's location field).\n";
    $body .= "\n";
    $body .= "Alternitively, you can visit the page at this URL:\n";
    $body .= $validatepage."\n";
    $body .= "And pasting the code $m into the form there.\n";
    $headers = "From: PhpOfficials@".$_SERVER['HTTP_HOST']."\r\n";
    mail($email,"E-Mail validation from ".officials_title(),$body,$headers);
  }
}
