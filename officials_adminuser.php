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
 *  Created       : Fri Sep 15 20:49:29 2023
 *  Last Modified : <230915.2052>
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
  * Admin user initialization.
  *
  */

global $admin_screenname, $admin_password, $admin_fullname, $admin_email;


/** Adminstrator credentials.
  *  Change these to suit.
  *  Obviously you will want a properly secure password!
  *  (The password can be changed in the database from the admin profile 
  *   page or the user list admin page.)
  *  Right now, the admin E-Mail is not actually used for anything.
  */
$admin_screenname = 'admin';
$admin_password   = 'admin';
$admin_fullname   = 'God';
$admin_email      = 'none@nowhere.net';

function officials_adminuser($database)
{
  global $admin_screenname, $admin_password, $admin_fullname, $admin_email;
  
  $adminuserid = Officials_User::create_new_user(
                       (OBJECT) array('screenname' => $admin_screenname,
                                      'password'   => $admin_password,
                                      'fullname'   => $admin_fullname,
                                      'email'      => $admin_email));
}


?>
