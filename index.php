<?php
/* -*- php -*- ****************************************************************
 *
 *  System        : 
 *  Module        : 
 *  Object Name   : $RCSfile$
 *  Revision      : $Revision$
 *  Date          : $Date$
 *  Author        : $Author$
 *  Created By    : Robert Heller
 *  Created       : Fri Sep 15 16:05:52 2023
 *  Last Modified : <230915.2106>
 *
 *  Description	
 *
 *  Notes
 *
 *  History
 *	
 ****************************************************************************
 *
 *    Copyright (C) 2023  Robert Heller D/B/A Deepwoods Software
 *			51 Locke Hill Road
 *			Wendell, MA 01379-9728
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program; if not, write to the Free Software
 *    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * 
 *
 ****************************************************************************/

/*
  * Main index file
  */

require_once(dirname(__FILE__) . '/officials-load.php');

if (!Officials_User::is_logged_in()) {
  header('Location: '.BASEURL.'login.php?returnto='.urlencode($_SERVER['REQUEST_URI']));
  die();
}

require_once(INCPATH . 'officials-head.php' );?>
<body <?php officals_body_class(); ?> >
<?php officals_page_top(); ?>
<?php officals_navigation_bar(); ?>
<?php
?>
<?php officals_footer(); ?>
</body></html>
