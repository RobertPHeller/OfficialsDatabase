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
 *  Created       : Sun Sep 17 14:21:34 2023
 *  Last Modified : <230917.1443>
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

require_once(INCPATH . 'Officials_TableClass.php');

class Officials_Table extends Officials_TableClass {
  public function get_columns() {
    return array(
                 'cb'              => '<input type="checkbox" />',
                 'name'            => 'Name',
                 'ethicsexpires'   => 'Ethics Expired',
                 'termends'        => 'Term Ends',
                 'swornindate'     => 'Sworn In Date',
                 'email'           => 'E-Mail',
                 'telephone'       => 'Telephone',
                 'office'          => 'Position'
                 );
  }
  public function prepare_items() {
    $this->items = array();
    $this->set_pagination_args(0, 0, 10);
  }
  
} 


?>
