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
 *  Last Modified : <230919.1011>
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

class Officials_Table extends Officials_List_Table {
  var $viewmode = 'add';
  var $viewitem;
  var $viewid = 0;
  
  function __construct() {
    parent::__construct("Official", "Officials");
  }
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
  public function get_hidden_columns() { return array(); }
  public function get_primary_column() {
    return null;
  }
  protected function column_default( $item, $column_name ) {
    return $item[$column_name];
  }
  protected function column_office ($item)
  {
    global $officials_database;
    $sql = $officials_database->prepareQueryMySQL("SELECT name FROM `offices` WHERE `id` = %d",$item['officeid']);
    $result = $officials_database->queryMySQL($sql);
    if ($result->num_rows == 0)
    {
      $office = '';
    } else {
      $office = $result->fetch_row()[0];
    }
    $result->free();
    return $office;
  }
  protected function column_cb( $item ) {
    return '<input type="checkbox" name="checked[]" value="'.$item['id'].'" />';
  }
  protected function column_name ($item)
  {
    $id = $item['id'];
    $actions = array(
                     'edit' => '<a href="/newofficial.php?id='.$id.'&mode=edit">Edit</a>',
                     'delete' => '<a href="/officials.php?deleteid='.$id.'">Delete</a>'
                     );
    return $item['name'].$this->row_actions($actions);
  }
  
  public function prepare_items() {
    global $officials_database;
    if (isset($_REQUEST['deleteid']))
    {
      $deleteid = $_REQUEST['deleteid'];
      $sql = $officials_database->prepareQueryMySQL("DELETE FROM `people` WHERE `id` = %d", $deleteid);
      $result = $officials_database->queryMySQL($sql);
    }
    // Deal with columns
    $columns = $this->get_columns();    // All of our columns
    $hidden  = array();         // Hidden columns [none]
    $sortable = $this->get_sortable_columns(); // Sortable columns
    $this->_column_headers = array($columns,$hidden,$sortable,null); // Set up columns
    
    $message = '';
    //$this->process_bulk_action();
    $search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
    $field  = isset( $_REQUEST['f'] ) ? $_REQUEST['f'] : 'name'; 
    $orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'name';
    if ( empty( $orderby ) ) $orderby = 'name';
    $order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC'; 
    if ( empty( $order ) ) $order = 'ASC';
    $per_page = $this->get_per_page();
    if ($search == '') {
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `people` order by %i $order",$orderby);
    } else {
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `people` WHERE %i LIKE %s order by %i $order",$field,'%'.$search.'%',$orderby);
    }
    $result = $officials_database->queryMySQL($sql);
    $this->items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
    $this->set_pagination_args(count($this->items), 0, $per_page);
  }
  public function get_per_page()
  {
    return 20;
  }
  
  public function officials_page($page)
  {
    $this->prepare_items();
  ?><h2>Officials</h2>
  <?php 
    if ($message != '') {
    ?><div id="message" class="update fade"><?php echo $message; ?></div><?php
    } ?>
  <form method="post" action="<?php echo $page; ?>">
  <?php $this->search_box('Search Officials', 'officials');
        $this->display(); ?></form><?php
    
  }
  public function edit_people_page($page)
  {
    $message = $this->prepare_one_item();
  ?><h2><?php echo $this->add_item_h2(); ?></h2>
  <?php
   if ($message != '') {
   ?><div id="message" class="update fade"><?php echo $message; ?></div><?php
   } ?>
  <form action="<?php echo $page; ?>" method="post">
  <?php $this->display_one_item_form(); ?></form></div><?php
  }
  function add_item_h2() {
    switch ($this->viewmode) {
    case 'edit': 
      return 'Edit Person';
    case 'add': 
    default:
      return 'Add Person';
    }
  }
  function prepare_one_item() {
    global $officials_database;
    $message = '';
    if ( isset($_REQUEST['addperson']) ) {
      $message = $this->checkiteminform(0);
      $item    = $this->getitemfromform(0);
      if ($message == '') {
        $r = $officials_database->insertMySQL('people',
                                              array('name' => $item['name'],
                                                    'ethicsexpires' => $item['ethicsexpires'],
                                                    'termends' => $item['termends'],
                                                    'swornindate' => $item['swornindate'],
                                                    'email' => $item['email'],
                                                    'telephone' => $item['telephone'],
                                                    'officeid' => $item['officeid']),
                                              array('%s','%s','%s','%s','%s','%s',"%d"));
        $item['id'] = $officials_database->insert_id();
        $this->viewmode = 'edit';
        $this->viewid = $item['id'];
        $this->viewitem = $item;
      }
    } else if ( isset($_REQUEST['updateperson']) ) {
      $message = $this->checkiteminform($_REQUEST['id']);
      $item    = $this->getitemfromform($_REQUEST['id']);
      if ($message == '') {
        $r = $officials_database->replaceMySQL('people',
                                               array('id' => $item['id'],
                                                     'name' => $item['name'],
                                                     'ethicsexpires' => $item['ethicsexpires'],
                                                     'termends' => $item['termends'],
                                                     'swornindate' => $item['swornindate'],
                                                     'email' => $item['email'],
                                                     'telephone' => $item['telephone'],
                                                     'officeid' => $item['officeid']),
                                               array("%d",'%s','%s','%s','%s','%s','%s',"%d"));
        $this->viewmode = 'edit';
        $this->viewid = $item['id'];
        $this->viewitem = $item;
      }
    } else {
      $this->viewmode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'add';
      $this->viewid = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
      switch ($this->viewmode) {
      case 'edit': 
        if ($this->viewid == 0) {$this->viewmode = 'add';}
        break;
      case 'add':
        $this->viewid = 0;
        break;
      default: 
        $this->viewmode = 'add';
        $this->viewid = 0;
        break;
      }
      if ($this->viewid == 0) {
        $this->viewitem = array('id' => 0,
                                'name' => '',
                                'ethicsexpires' => '1969-01-01',
                                'termends' => '1969-01-01',
                                'swornindate' => '1969-01-01',
                                'email' => '',
                                'telephone' => '',
                                'officeid' => 0);
      } else {
        $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `people` WHERE `id` = %d",$this->viewid);
        $result = $officials_database->queryMySQL($sql);
        if ($result->num_rows > 0) {
          $this->viewitem = $result->fetch_assoc();
        } else {
          $this->viewmode = 'add'; 
          $this->viewid = 0;
          $this->viewitem = array('id' => 0,
                                'name' => '',
                                'ethicsexpires' => '1969-01-01',
                                'termends' => '1969-01-01',
                                'swornindate' => '1969-01-01',
                                'email' => '',
                                'telephone' => '',
                                'officeid' => 0);
        }
        $result->free();
      }
    }
    return $message;
  }
  function display_one_item_form()
  {
  ?><table class="form-table">
    <tr valign="top">
      <th scope="row"><label for="id" style="width:20%;">Id</label></th>
      <td><input id="id" name="id" style="width:75%;" maxlength="10"
         value="<?php echo $this->viewid; ?>" readonly="readonly"/></td></tr>
    <tr valign="top">
      <th scope="row"><label for="name" style="width:20%;">Name</label></th>
      <td><input id="name" name="name" style="width:75%;" maxlength="64"
         value="<?php echo stripslashes($this->viewitem['name']); ?>" /></td></tr>
    <tr valign="top">
      <th scope="row"><label for="ethicsexpires" style="width:20%;">Ethics Expires</label></th>
      <td><input id="ethicsexpires" name="ethicsexpires" style="width:75%;" maxlength="30"
         value="<?php echo stripslashes($this->viewitem['ethicsexpires']); ?>"/></td></tr>
    <tr valign="top">
      <th scope="row"><label for="termends" style="width:20%;">Term Ends</label></th>
      <td><input id="termends" name="termends" style="width:75%;" maxlength="30"
         value="<?php echo stripslashes($this->viewitem['termends']); ?>"/></td></tr>
    <tr valign="top">
      <th scope="row"><label for="swornindate" style="width:20%;">Sworn In Date</label></th>
      <td><input id="swornindate" name="swornindate" style="width:75%;" maxlength="30"
         value="<?php echo stripslashes($this->viewitem['swornindate']); ?>"/></td></tr>
        <tr valign="top">
      <th scope="row"><label for="email" style="width:20%;">E-Mail</label></th>
      <td><input id="email" name="email" style="width:75%;" 
         maxlength="100" value="<?php echo stripslashes($this->viewitem['email']); ?>" /></td></tr>
    <tr valign="top">
      <th scope="row"><label for="telephone" style="width:20%;">Telephone</label></th>
      <td><input id="telephone" name="telephone" style="width:75%;" 
         maxlength="100" value="<?php echo stripslashes($this->viewitem['telephone']); ?>" /></td></tr>
    <tr valign="top">
      <th scope="row"><label for="officeid" style="width:20%;">Office</label></th>
      <td><select name="officeid" id="officeid">
          <option value="0" <?php if ($oid == $item['officeid']) echo 'selected="selected"'; ?>>--Select Office--</option>
          <?php
            global $officials_database;
            $result = $officials_database->queryMySQL("SELECT id,name FROM `offices`");
            while ($row = $result->fetch_row()) {
              $oid = $row[0];
              $officename = $row[1];
              ?><option value="<?php echo $oid; ?>" <?php if ($oid == $item['officeid']) echo 'selected="selected"'; ?>><?php echo stripslashes($officename); ?></option><?php
            }
           ?></select></td></tr>
   </table>
   <p>
   <?php switch($this->viewmode) {
     case 'add':
       ?><input type="submit" name="addperson" class="button-primary" value="Add New Office" /><?php
       break;
     case 'edit':
       ?><input type="submit" name="updateperson" class="button-primary" value="Update Item" /><?php
       break;
     } ?>
   </p><?php
  }
  function checkiteminform($id)
  {
    $result = '';
    if ($_REQUEST['name'] == '') {
      $result .= '<br /><span id="error">'.'Name is invalid'.'</span>';
    }
    if ($_REQUEST['officeid'] == 0) {
      $result .= '<br /><span id="error">'.'Please select an office'.'</span>';
    }
    if (!$this->checkdate($_REQUEST['ethicsexpires'])) {
      $result .= '<br /><span id="error">'.'Ethics Expires date is invalid'.'</span>';
    }
    if (!$this->checkdate($_REQUEST['termends'])) {
      $result .= '<br /><span id="error">'.'Term Ends date is invalid'.'</span>';
    }
    if (!$this->checkdate($_REQUEST['swornindate'])) {
      $result .= '<br /><span id="error">'.'Sworn In date is invalid'.'</span>';
    }
    return $result;
  }
  function getitemfromform($id)
  {
    global $officials_database;
    $item = array ('id' => $id,
                   'name' => $officials_database->sanitizeStringNoTagsHE($_REQUEST['name']),
                   'ethicsexpires' => $this->makeMySQLDate($_REQUEST['ethicsexpires']),
                   'termends' => $this->makeMySQLDate($_REQUEST['termends']),
                   'swornindate' => $this->makeMySQLDate($_REQUEST['swornindate']),
                   'email' => $officials_database->sanitizeStringNoTagsHE($_REQUEST['email']),
                   'telephone' => $officials_database->sanitizeStringNoTagsHE($_REQUEST['telephone']),
                   'officeid' => $_REQUEST['officeid']);
    return $item;
  }
  private function checkdate($maybedate)
  {
    if (strtotime($maybedate)) {
      return true;
    } else {
      return false;
    }
  }
  private function makeMySQLDate($thedate) {
    return date("Y-m-d", strtotime($thedate));
  }
  
    
} 

$officials = new Officials_Table();

?>
