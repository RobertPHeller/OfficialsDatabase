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
 *  Created       : Fri Sep 15 16:17:49 2023
 *  Last Modified : <230915.1622>
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

/** Database access 
  * These four global variables define how to access the MySQL database used
  * by this code.
  */
global $officials_db_user, $officials_db_pass, $officials_db_database, $officials_db_host;
$officials_db_host = 'localhost';    /** Database host, almost always this will 
                                   * be localhost. */
$officials_db_user = 'testbed';        /** Database username. */
$officials_db_pass = 'testbed';        /** Database password. */
$officials_db_database = 'testbed';    /** Database name. */

/** System constants:
  *
  * BASEURL is the relative URL from the site's DOCUMENT_ROOT.
  * If the PHP code is loaded at the DOCUMENT_ROOT, BASEURL would be the
  * default value of '/'.  If, on the other hand, the PHP code is loaded
  * in a subfolder of the DOCUMENT_ROOT, that folder would need to be added
  * to the DOCUMENT_ROOT.
  */
define('BASEURL','/'); // relative URL

?>
