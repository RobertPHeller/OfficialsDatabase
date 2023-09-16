<?php
    /*
     * Common support code.
     */

  require_once(INCPATH . 'database.php');
  global $officials_database;

  $officials_database = new Officials_database();
  $officials_database->Check_And_Create_Tables();


  function officials_meta()
  {
     echo '<!-- Header meta tags -->'; echo "\n";
     echo '<meta name="generator" content="OfficialsDatabase 0.0">'; echo "\n";
  }

  function officials_css()
  {
     $cssURL = BASEURL . "css/";
     echo '<!-- Header CSS Links -->'; echo "\n";
     echo '<link rel="stylesheet" href="'.
	$cssURL.'jquery-ui/jquery.ui.all.css?nocache='.time().'" type="text/css" media="screen" />'."\n";
     foreach (glob(ABSPATH . 'css/jquery-ui/*.css') as $filename) {
	printf('<link rel="stylesheet" href="%s" type="text/css" media="screen" />'."\n",
		preg_replace('|'.ABSPATH.'|',BASEURL,$filename));
     }
     echo '<link rel="stylesheet" href="'.
        $cssURL.'perfect-scrollbar.min.css?nocache='.time().'" type="text/css" media="screen" />'."\n";
     echo '<link rel="stylesheet" href="'.
	$cssURL.'main.css?nocache='.time().'" type="text/css" media="screen" />'."\n";

     echo '<link rel="stylesheet" href="'.
	$cssURL.'login.css?nocache='.time().'" type="text/css" media="screen" />'."\n";

     echo '<link rel="stylesheet" href="'.
	$cssURL.'map.css?nocache='.time().'" type="text/css" media="screen" />'."\n";

     echo '<link rel="stylesheet" href="'.
	$cssURL.'dialog.css?nocache='.time().'" type="text/css" media="screen" />'."\n";

     echo '<link rel="stylesheet" href="'.
        $cssURL.'profile.css?nocache='.time().'" type="text/css" media="screen" />'; echo "\n";
     echo '<link rel="stylesheet" href="'.
        $cssURL.'inputs.css?nocache='.time().'" type="text/css" media="screen" />'; echo "\n";
     if (file_exists(THEMEPATH . "theme.css")) {
       echo '<link rel="stylesheet" href="'.THEMEURL.'theme.css?nocache='.
            time().'" type="text/css" media="screen" />'; echo "\n";
     }

  }

  function officials_js()
  {
     $jsURL = BASEURL . "js/";
     echo '<!-- Header JS Links -->'; echo "\n";
     echo '<script type="text/javascript" src="'.$jsURL.'jquery.js?nocache='.time().'"></script>'."\n";
     ?><script type="text/javascript">
	/* $(function() {$( "#tabs" ).tabs();});*/
	DOM_LoadingAnimation = "<?php echo BASEURL; ?>images/loadingAnimation.gif";
	</script><?php
     echo '<script type="text/javascript" src="'.$jsURL.'jquery.DOMWindow.js?nocache='.time().'"></script>'."\n";
     echo '<script type="text/javascript" src="'.$jsURL.'jquery.form.js?nocache='.time().'"></script>'."\n";
     foreach (glob(ABSPATH . 'js/jquery-ui/*.js') as $filename) {
	printf('<script type="text/javascript" src="%s"></script>'."\n",
		preg_replace('|'.ABSPATH.'|',BASEURL,$filename));
     }
     echo '<script type="text/javascript" src="'.$jsURL.'perfect-scrollbar.min.js?nocache='.time().'"></script>'."\n";
     echo '<script type="text/javascript" src="'.$jsURL.'officials-ajax.js?nocache='.time().'"></script>'."\n";
  }

  function officials_body_class($isiframe=false)
  {
    if ($isiframe) {
      echo 'class="sidebar_iframe"';
    } else {
      echo 'class="officials"';
    }
  }

  function officials_popup_body_class()
  {
     echo 'class="officials officials-popup"';
  }

  function officials_title()
  {
     global $officials_database;
     return $officials_database->get_sitesetting('SiteTitle');
  }

?>
