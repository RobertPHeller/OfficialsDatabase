<?php
   /* Page top code */

  function officials_page_top()
  {
     global $officials_database;
     ?><!-- Page top begin --><div id="page_top">
       <?php if (file_exists(THEMEPATH . "page_top.php")) {
           @require_once(THEMEPATH . "page_top.php");
           } else { ?>
           <h1 class="title">Welcome to <?php echo $officials_database->get_sitesetting('SiteTitle'); ?></h1>
           <?php } ?>
     <!-- Page top end --></div>
     <?php
  }

   
