<?php
    /*
     * Database code
     */

/* User access bits */
define('USR_ADMIN',0x8000);
define('USR_BUILD',0x0001);
define('USR_VERIFIED',0x0002);

require_once(INCPATH . 'user_functions.php' );

class Officials_Database {

  var $user;
  var $pass;
  var $host;
  var $database;
  var $db;

  function __construct()
  {
	register_shutdown_function( array( &$this, '__destruct' ) );

	global $officials_db_user, $officials_db_pass, $officials_db_database, $officials_db_host;

	$this->user = $officials_db_user;
	$this->pass = $officials_db_pass;
	$this->host = $officials_db_host;
	$this->database = $officials_db_database;

        $this->db = new mysqli( $this->host, $this->user, $this->pass,
                               $this->database );
        if (!$this->db) die("Failed to open database");
  }

  function __destruct()
  {
     //mysql_close($this->db);
  }

  function get_post_var($var,$default='')
  {
    if (isset($_POST[$var])) {
      $val = $_POST[$var];
      if (get_magic_quotes_gpc())
      $val = stripslashes($val);
      return $val;
    } else {
      return $default;
    }
  }
  
  private function tableExists($name)
  {
    $result = $this->queryMySQL("SHOW TABLES LIKE '$name'");
    if ($result) {
      $numrows = $result->num_rows;
      $result->free();
      return $numrows > 0;
    } else {
      return false;
    }
  }

  function queryMySQL($query)
  {
      $result = $this->db->query($query) or die($this->db->error."\n".$query);
      return $result;
  }

  private function createTable($name, $query)
  {
    if ($this->tableExists($name)) {
      return false;
    } else {
      $this->queryMySQL("CREATE TABLE $name($query)");
      return true;
    }
  }
  private function checkColumnType($table, $column, $datatype) {
    $result = $this->queryMySQL("DESCRIBE ".$table." ".$column);
    if ($result) {
      if ($result->num_rows < 1) {
        $result->free();
        return false;
      }
      $existingDT = $result->fetch_row()[1];
      $result->free();
      //file_put_contents("php://stderr","*** Officials_Database::checkColumnType: datatype is $datatype\n");
      //file_put_contents("php://stderr","*** Officials_Database::checkColumnType: existingDT is $existingDT\n");
      return ($existingDT == $datatype);
    } else {
      return false;
    }
  }
  

  function Check_And_Create_Tables()
  {
    $this->createTable('people',
                       'id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(64) NOT NULL,
                        ethicsexpires DATE NOT NULL DEFAULT "1969-01-01",
                        termends DATE NOT NULL DEFAULT "1969-01-01",
                        swornindate DATE NOT NULL DEFAULT "1969-01-01",
                        email VARCHAR(100),
                        telephone VARCHAR(15),
                        officeid int(11) NOT NULL DEFAULT "0"');
   $this->createTable('offices',
                      'id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(64) NOT NULL,
                       iselected tinyint(4) NOT NULL DEFAULT "0",
                       officalemail VARCHAR(100),
                       officetelephone VARCHAR(15)');
                       
    $this->createTable('user_properties',
	'id INT AUTO_INCREMENT PRIMARY KEY,
	 userid INT NOT NULL,
	 name VARCHAR(32) NOT NULL,
	 value TEXT,
	 INDEX (userid), INDEX(name)');
    if ($this->createTable('users',
	'id INT AUTO_INCREMENT PRIMARY KEY,
	 screenname VARCHAR(32) NOT NULL,
	 password   VARCHAR(64) NOT NULL,
	 fullname   VARCHAR(64),
	 email      VARCHAR(64),
	 access	    INT NOT NULL DEFAULT 0,
	 INDEX (screenname)')) {
      require_once(ABSPATH . 'officials_adminuser.php');
      officials_adminuser($this);
    } else if (!$this->checkColumnType('users','password','varchar(64)')) {
      $q = 'ALTER TABLE users MODIFY password   VARCHAR(64) NOT NULL';
      $result = $this->queryMySQL($q);
      //file_put_contents("php://stderr","*** Officials_Database::Check_And_Create_Tables(): result (ALTER TABLE users MODIFY password   VARCHAR(64) NOT NULL)  is $result\n");
      $this->hashOldPasswords();
    }
    if ($this->createTable('site_settings',
			   'id INT AUTO_INCREMENT PRIMARY KEY,
			    name VARCHAR(32) NOT NULL,
			    value TEXT,
			    INDEX (name)') ) {
      require_once(ABSPATH . 'officials_defaultsettings.php');
      officials_defaultsettings($this);
    }
  }

  private function hashOldPasswords() {
    //file_put_contents("php://stderr","*** Officials_Database::hashOldPasswords()\n");
    $qr = $this->queryMySQL('SELECT id,password FROM users');
    while ($row = $qr->fetch_row()) {
      $id = $row[0];
      $oldpassword = $row[1];
      $newpassword = Officials_User::HashPassword($oldpassword);
      $this->queryMySQL("UPDATE users SET password='".$newpassword."' WHERE id = ".$id);
    }
    $qr->free();
  }
    
  function sanitizeString($var)
  {
    $var = stripslashes($var);
    return $this->db->real_escape_string($var);
  }

  function sanitizeStringHE($var)
  {
    $var = htmlentities($var);
    $var = stripslashes($var);
    return $this->db->real_escape_string($var);
  }

  function sanitizeStringNoTagsHE($var)
  {
    //file_put_contents("php://stderr","*** Officials_Database::sanitizeStringNoTagsHE('$var')\n");
    $var = strip_tags($var);
    //file_put_contents("php://stderr","*** Officials_Database::sanitizeStringNoTagsHE() (after strip_tags) var = '$var'\n");
    $var = htmlentities($var);
    //file_put_contents("php://stderr","*** Officials_Database::sanitizeStringNoTagsHE() (after htmlentities) var = '$var'\n");
    $var = stripslashes($var);
    //file_put_contents("php://stderr","*** Officials_Database::sanitizeStringNoTagsHE() (after stripslashes) var = '$var'\n");
    return $this->db->real_escape_string($var);
  }

  function hasOtherTags($var)
  {
    $allowed_tags = array('b','big','br','cite','code','hr','i','p','pre',
			  'small','strong');

    preg_match_all('|<[\s/]*([^\s>]+)|',strtolower($var),$matches,
			PREG_PATTERN_ORDER);
    //file_put_contents("php://stderr","*** Officials_Database::hasOtherTags: matches is ".print_r($matches,true)."\n");
    //file_put_contents("php://stderr","*** Officials_Database::hasOtherTags: allowed_tags is ".print_r($allowed_tags,true)."\n");
    $othertags = array_diff($matches[1],$allowed_tags);
    //file_put_contents("php://stderr","*** Officials_Database::hasOtherTags: othertags is ".print_r($othertags,true)."\n");
    return (count($othertags) > 0);
  }

  function get_sitesetting($name)
  {
     $q = "select value from site_settings where name = '".$this->sanitizeStringNoTagsHE($name)."'";
     $qr = $this->queryMySQL($q);
     if ($qr->num_rows > 0) {
	$row = $qr->fetch_row();
	$result = stripslashes($row[0]);
     } else {
	$result = '';
     }
     $qr->free();
     return $result;
  }
  function update_sitesetting($name,$value)
  {
    $q = "SELECT value FROM site_settings WHERE name = '".$this->sanitizeStringNoTagsHE($name)."'";
    $qr = $this->queryMySQL($q);
    if ($qr->num_rows == 0) { // Setting does not exist, insert fresh
      $q = "INSERT INTO site_settings (name, value) VALUES ('".
		$this->sanitizeStringNoTagsHE($name)."','".
		$this->sanitizeString($value)."')";
    } else {
      $qr->free();
      $q = "UPDATE site_settings SET value = '".
		$this->sanitizeString($value)."' WHERE name = '".
		$this->sanitizeStringNoTagsHE($name)."'";
    }
    $qr = $this->queryMySQL($q);
  }
}
