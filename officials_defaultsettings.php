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
 *  Created       : Fri Sep 15 20:55:26 2023
 *  Last Modified : <230915.2056>
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

/**
  * Default settings
  * Initial default contents for the site_settings table.  Messing with
  * this file is totally optional.  All of the site settings can be 
  * set or changed from the Admin pages and any missing or unset site 
  * settings behave as if they were set to the empty string -- no errors
  * will result from not having a site setting set.
  */

global $default_settings;
$default_settings = 
array('SiteTitle' => 'My Town Officials');

function officials_defaultsettings($database)
{
  global $default_settings;
  
  foreach ($default_settings as $name => $value) {
    $q = 'INSERT INTO site_settings (name, value) VALUES (';
    $q .= "'".$database->sanitizeString($name)."',";
    $q .= "'".$database->sanitizeString($value)."')";
    $database->queryMySQL($q);
  }
}

?>
