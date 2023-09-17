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
 *  Created       : Fri Sep 15 16:15:04 2023
 *  Last Modified : <230917.1446>
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
    * Load officials codebase.
    */

  define( 'ABSPATH', dirname(__FILE__) . '/' );
  define( 'INCPATH', ABSPATH . 'includes/');
  define( 'THEMEPATH', ABSPATH . 'theme/');
  
  if ( file_exists( ABSPATH . 'officials-config.php' ) ) {
	require_once( ABSPATH . 'officials-config.php' );
  } else {
	// A config file doesn't exist!
	require_once( INCPATH . 'die_no_config.php') ;
  }
  
  define( 'THEMEURL', BASEURL .'theme/');

  session_start();

  require_once( INCPATH . 'common_functions.php' );
  require_once( INCPATH . 'profile_functions.php' );
  require_once( INCPATH . 'Officials_Table.php' );  

  require_once( INCPATH . 'page_top.php' );
  require_once( INCPATH . 'navigation_bar.php' );
  require_once( INCPATH . 'footer.php' );
  require_once( INCPATH . 'registerlogin.php' );
  

?>
