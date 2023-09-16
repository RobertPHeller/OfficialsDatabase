<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<?php officials_meta(); ?>
<?php officials_css(); ?>
<?php officials_js(); ?>
<?php if (Officials_User::is_logged_in()) {
?><script type="text/javascript">
  var instanse = false;
  var thechatbox = null;
  var reusechatbox = false;
  var other = 0;
  var chatURL = "<?php echo BASEURL . 'chat_process.php' ?>";
  var userScreen = "<?php echo Officials_User::current_user_screenname(); ?>";
  var userId = "<?php echo Officials_User::current_user_id(); ?>";
  </script>
<?php } ?>
<?php 
   //file_put_contents("php://stderr","*** officials-head.php: _REQUEST is ".print_r($_REQUEST,true)."\n");
   //file_put_contents("php://stderr","*** officials-head.php: _SESSION is ".print_r($_SESSION,true)."\n");
   //file_put_contents("php://stderr","*** officials-head.php: _SERVER is ".print_r($_SERVER,true)."\n"); 
   if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//file_put_contents("php://stderr","*** officials-head.php: raw post data is '".file_get_contents("php://input")."'\n");
   }
?>
<title><?php echo officials_title(); ?></title>
</head>
