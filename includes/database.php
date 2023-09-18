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

  function insert_id () {
    return mysqli_insert_id( $this->db );
  }
  
  function queryMySQL($query)
  {
    $result = $this->db->query($query) or die($this->db->error."\n".$query);
    return $result;
  }
  
  /**
    * Prepares a SQL query for safe execution.
    *
    * Uses `sprintf()`-like syntax. The following placeholders can be used in the query string:
    *
    * - `%d` (integer)
    * - `%f` (float)
    * - `%s` (string)
    * - `%i` (identifier, e.g. table/field names)
    *
    * All placeholders MUST be left unquoted in the query string. A corresponding argument
    * MUST be passed for each placeholder.
    *
    * Note: There is one exception to the above: for compatibility with old behavior,
    * numbered or formatted string placeholders (eg, `%1$s`, `%5s`) will not have quotes
    * added by this function, so should be passed with appropriate quotes around them.
    *
    * Literal percentage signs (`%`) in the query string must be written as `%%`. Percentage wildcards
    * (for example, to use in LIKE syntax) must be passed via a substitution argument containing
    * the complete LIKE string, these cannot be inserted directly in the query string.
    * Also see wpdb::esc_like().
    *
    * Arguments may be passed as individual arguments to the method, or as a single array
    * containing all arguments. A combination of the two is not supported.
    *
    * Examples:
    *
    *     $wpdb->prepare(
    *         "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d OR `other_field` LIKE %s",
    *         array( 'foo', 1337, '%bar' )
    *     );
    *
    *     $wpdb->prepare(
    *         "SELECT DATE_FORMAT(`field`, '%%c') FROM `table` WHERE `column` = %s",
    *         'foo'
    *     );
    *
    * @since 2.3.0
    * @since 5.3.0 Formalized the existing and already documented `...$args` parameter
    *              by updating the function signature. The second parameter was changed
    *              from `$args` to `...$args`.
    * @since 6.2.0 Added `%i` for identifiers, e.g. table or field names.
    *              Check support via `wpdb::has_cap( 'identifier_placeholders' )`.
    *              This preserves compatibility with `sprintf()`, as the C version uses
    *              `%d` and `$i` as a signed integer, whereas PHP only supports `%d`.
    *
    * @link https://www.php.net/sprintf Description of syntax.
    *
    * @param string      $query   Query statement with `sprintf()`-like placeholders.
    * @param array|mixed $args    The array of variables to substitute into the query's placeholders
    *                             if being called with an array of arguments, or the first variable
    *                             to substitute into the query's placeholders if being called with
    *                             individual arguments.
    * @param mixed       ...$args Further variables to substitute into the query's placeholders
    *                             if being called with individual arguments.
    * @return string|void Sanitized query string, if there is a query to prepare.
    */
  function prepareQueryMySQL($query, ...$args ) {
    if ( is_null( $query ) ) {
      return;
    }
    if ( false === strpos( $query, '%' ) ) {
      die("Bad Call to prepareQueryMySQL!");
    }
    /*
      * Specify the formatting allowed in a placeholder. The following are allowed:
      *
      * - Sign specifier, e.g. $+d
      * - Numbered placeholders, e.g. %1$s
      * - Padding specifier, including custom padding characters, e.g. %05s, %'#5s
      * - Alignment specifier, e.g. %05-s
      * - Precision specifier, e.g. %.2f
      */
    $allowed_format = '(?:[1-9][0-9]*[$])?[-+0-9]*(?: |0|\'.)?[-+0-9]*(?:\.[0-9]+)?';
    
    /*
      * If a %s placeholder already has quotes around it, removing the existing quotes
      * and re-inserting them ensures the quotes are consistent.
      *
      * For backward compatibility, this is only applied to %s, and not to placeholders like %1$s,
      * which are frequently used in the middle of longer strings, or as table name placeholders.
      */
    $query = str_replace( "'%s'", '%s', $query ); // Strip any existing single quotes.
    $query = str_replace( '"%s"', '%s', $query ); // Strip any existing double quotes.
    
    // Escape any unescaped percents (i.e. anything unrecognised).
    $query = preg_replace( "/%(?:%|$|(?!($allowed_format)?[sdfFi]))/", '%%\\1', $query );
    
    // Extract placeholders from the query.
    $split_query = preg_split( "/(^|[^%]|(?:%%)+)(%(?:$allowed_format)?[sdfFi])/", $query, -1, PREG_SPLIT_DELIM_CAPTURE );
    
    $split_query_count = count( $split_query );
    
    /*
      * Split always returns with 1 value before the first placeholder (even with $query = "%s"),
      * then 3 additional values per placeholder.
      */
    $placeholder_count = ( ( $split_query_count - 1 ) / 3 );
    
    // If args were passed as an array, as in vsprintf(), move them up.
    $passed_as_array = ( isset( $args[0] ) && is_array( $args[0] ) && 1 === count( $args ) );
    if ( $passed_as_array ) {
      $args = $args[0];
    }
    
    $new_query       = '';
    $key             = 2; // Keys 0 and 1 in $split_query contain values before the first placeholder.
    $arg_id          = 0;
    $arg_identifiers = array();
    $arg_strings     = array();
    
    while ( $key < $split_query_count ) {
      $placeholder = $split_query[ $key ];
      
      $format = substr( $placeholder, 1, -1 );
      $type   = substr( $placeholder, -1 );
      
      if ( 'f' === $type && true === $this->allow_unsafe_unquoted_parameters
          /*
            * Note: str_ends_with() is not used here, as this file can be included
            * directly outside of WordPress core, e.g. by HyperDB, in which case
            * the polyfills from wp-includes/compat.php are not loaded.
            */
          && '%' === substr( $split_query[ $key - 1 ], -1, 1 )
          ) {
      
        /*
          * Before WP 6.2 the "force floats to be locale-unaware" RegEx didn't
          * convert "%%%f" to "%%%F" (note the uppercase F).
          * This was because it didn't check to see if the leading "%" was escaped.
          * And because the "Escape any unescaped percents" RegEx used "[sdF]" in its
          * negative lookahead assertion, when there was an odd number of "%", it added
          * an extra "%", to give the fully escaped "%%%%f" (not a placeholder).
          */
        
        $s = $split_query[ $key - 2 ] . $split_query[ $key - 1 ];
        $k = 1;
        $l = strlen( $s );
        while ( $k <= $l && '%' === $s[ $l - $k ] ) {
          $k++;
        }
      
        $placeholder = '%' . ( $k % 2 ? '%' : '' ) . $format . $type;
        
        --$placeholder_count;
      
      } else {
      
        // Force floats to be locale-unaware.
        if ( 'f' === $type ) {
          $type        = 'F';
          $placeholder = '%' . $format . $type;
        }
        
        if ( 'i' === $type ) {
          $placeholder = '`%' . $format . 's`';
          // Using a simple strpos() due to previous checking (e.g. $allowed_format).
          $argnum_pos = strpos( $format, '$' );
          
          if ( false !== $argnum_pos ) {
            // sprintf() argnum starts at 1, $arg_id from 0.
            $arg_identifiers[] = ( ( (int) substr( $format, 0, $argnum_pos ) ) - 1 );
          } else {
            $arg_identifiers[] = $arg_id;
          }
        } elseif ( 'd' !== $type && 'F' !== $type ) {
          /*
            * i.e. ( 's' === $type ), where 'd' and 'F' keeps $placeholder unchanged,
            * and we ensure string escaping is used as a safe default (e.g. even if 'x').
            */
          $argnum_pos = strpos( $format, '$' );
          
          if ( false !== $argnum_pos ) {
            $arg_strings[] = ( ( (int) substr( $format, 0, $argnum_pos ) ) - 1 );
          } else {
            $arg_strings[] = $arg_id;
          }
          
          /*
            * Unquoted strings for backward compatibility (dangerous).
            * First, "numbered or formatted string placeholders (eg, %1$s, %5s)".
            * Second, if "%s" has a "%" before it, even if it's unrelated (e.g. "LIKE '%%%s%%'").
            */
          if ( true !== $this->allow_unsafe_unquoted_parameters
              /*
                * Note: str_ends_with() is not used here, as this file can be included
                * directly outside of WordPress core, e.g. by HyperDB, in which case
                * the polyfills from wp-includes/compat.php are not loaded.
                */
              || ( '' === $format && '%' !== substr( $split_query[ $key - 1 ], -1, 1 ) )
              ) {
            $placeholder = "'%" . $format . "s'";
          }
        }
      }
  
      // Glue (-2), any leading characters (-1), then the new $placeholder.
      $new_query .= $split_query[ $key - 2 ] . $split_query[ $key - 1 ] . $placeholder;
      
      $key += 3;
      $arg_id++;
    }

    // Replace $query; and add remaining $query characters, or index 0 if there were no placeholders.
    $query = $new_query . $split_query[ $key - 2 ];

    $dual_use = array_intersect( $arg_identifiers, $arg_strings );

    if ( count( $dual_use ) > 0 ) {
      die("Arguments cannot be prepared as both an Identifier and Value.");
    }
    
    $args_count = count( $args );
    
    if ( $args_count !== $placeholder_count ) {
      if ( 1 === $placeholder_count && $passed_as_array ) {
        /*
          * If the passed query only expected one argument,
          * but the wrong number of arguments was sent as an array, bail.
          */
        die("The query only expected one placeholder, but an array of multiple placeholders was sent." );
      } else {
        /*
          * If we don't have the right number of placeholders,
          * but they were passed as individual arguments,
          * or we were expecting multiple arguments in an array, throw a warning.
          */
        die(sprintf('The query does not contain the correct number of placeholders (%d) for the number of arguments passed (%d)',$placeholder_count, $args_count));
        
      }
    }
    
    $args_escaped = array();
    
    foreach ( $args as $i => $value ) {
      if ( in_array( $i, $arg_identifiers, true ) ) {
        $args_escaped[] = str_replace('`', '``', $value );
      } elseif ( is_int( $value ) || is_float( $value ) ) {
        $args_escaped[] = $value;
      } else {
        if ( ! is_scalar( $value ) && ! is_null( $value ) ) {
          die(sprintf('Unsupported value type (%s).',gettype( $value )));
          $value = '';
        }
        
        $args_escaped[] = $this->_real_escape( $value );
      }
    }
    
    $query = vsprintf( $query, $args_escaped );
    
    return $query;
  }
  public function insertMySQL($table, $data, $format)
  {
    return $this->_insert_replace_helper( $table, $data, $format, 'INSERT' );
  }
  public function replaceMySQL($table, $data, $format)
  {
    return $this->_insert_replace_helper( $table, $data, $format, 'REPLACE' );
  }
  private function process_field_formats( $data, $format ) {
    $formats          = (array) $format;
    $original_formats = $formats;
    
    foreach ( $data as $field => $value ) {
      $value = array(
                     'value'  => $value,
                     'format' => '%s',
                     );
      
      if ( ! empty( $format ) ) {
        $value['format'] = array_shift( $formats );
        if ( ! $value['format'] ) {
          $value['format'] = reset( $original_formats );
        }
      }
      
      $data[ $field ] = $value;
    }
    
    return $data;
  }

  
  private function process_fields( $table, $data, $format ) {
    $data = $this->process_field_formats( $data, $format );
    if ( false === $data ) {
      return false;
    }
    
    return $data;
  }
  private function _insert_replace_helper( $table, $data, $format, $type = 'INSERT' ) {
    if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ), true ) ) {
      return false;
    }
    
    $data = $this->process_fields( $table, $data, $format );
    if ( false === $data ) {
      return false;
    }
    
    $formats = array();
    $values  = array();
    foreach ( $data as $value ) {
      if ( is_null( $value['value'] ) ) {
        $formats[] = 'NULL';
        continue;
      }
      
      $formats[] = $value['format'];
      $values[]  = $value['value'];
    }
    
    $fields  = '`' . implode( '`, `', array_keys( $data ) ) . '`';
    $formats = implode( ', ', $formats );
    
    $sql = "$type INTO `$table` ($fields) VALUES ($formats)";
    
    return $this->queryMySQL( $this->prepareMySQL( $sql, $values ) );
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
