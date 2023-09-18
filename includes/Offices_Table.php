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
 *  Created       : Mon Sep 18 11:41:19 2023
 *  Last Modified : <230918.1337>
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

class Offices_Table extends Officials_List_Table {
  var $viewmode = 'add';
  var $viewitem;
  var $viewid = 0;
  
  function __construct() {
    parent::__construct("Office", "Offices");
  }
  public function get_columns() {
    return array(
                 'cb'              => '<input type="checkbox" />',
                 'name'            => 'Office Name',
                 'iselected'       => 'Elected?',
                 'officalemail'    => 'E-Mail',
                 'officetelephone' => 'Telephone'
                 );
  }
  public function get_hidden_columns() { return array(); }
  public function get_primary_column() {
    return 'name';
  }
  protected function column_default( $item, $column_name ) {
    return $item[$column_name];
  }
  protected function column_cb( $item ) {
    return '<input type="checkbox" name="checked[]" value="'.$item['id'].'" />';
  }
  protected function column_iselected ($item)
  {
    if ($item['iselected'])
    {
      return 'Yes';
    } else {
      return 'No';
    }
  }
  public function prepare_items() {
    global $officials_database;
    // Deal with columns
    $columns = $this->get_columns();    // All of our columns
    $hidden  = array();         // Hidden columns [none]
    $sortable = $this->get_sortable_columns(); // Sortable columns
    $this->_column_headers = array($columns,$hidden,$sortable); // Set up columns
    
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
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `offices` order by %i $order",$orderby);
    } else {
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `offices` WHERE %i LIKE %s order by %i $order",$field,'%'.$search.'%',$orderby);
    }
    $result = $officials_database->queryMySQL($sql);
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
    $this->set_pagination_args(count($items), 0, $per_page);
  }
  public function get_per_page()
  {
    return 20;
  }
  
  public function offices_page($page)
  {
    $message = $this->prepare_items();
  ?><h2>Offices</h2>
  <?php 
    if ($message != '') {
    ?><div id="message" class="update fade"><?php echo $message; ?></div><?php
    } ?>
  <form method="post" action="<?php echo $page; ?>">
  <?php $this->search_box('Search Offices', 'offices');
        $this->display(); ?></form><?php
  }
  
  
  public function edit_office_page($page)
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
  function add_item_h2()
  {
    switch ($this->viewmode) {
    case 'edit':
      return 'Edit Office';
    case 'add':
    default:
      return 'Add Office';
    }
  }
  function prepare_one_item() {
    $message = '';
    if ( isset($_REQUEST['addoffice']) ) {
      $message = $this->checkiteminform(0);
      $item    = $this->getitemfromform(0);
      if ($message == '') {
        $officials_database->insertMySQL('offices',
                                         array('name' => $item['name'],
                                               'iselected' => $item['iselected'],
                                               'officalemail' => $item['officalemail'],
                                               'officetelephone' => $item['officetelephone']),
                                         array('%s',"%d",'%s','%s'));
        $item['id'] = $officials_database->insert_id();
        $this->viewmode = 'edit';
        $this->viewid = $item['id'];
        $this->viewitem = $item;
      }
    } else if ( isset($_REQUEST['updateoffice']) ) {
      $message = $this->checkiteminform($_REQUEST['id']);
      $item    = $this->getitemfromform($_REQUEST['id']);
      if ($message == '') {
        $officials_database->replaceMySQL('offices',
                                          array('id' => $item['id'],
                                                'name' => $item['name'],
                                                'iselected' => $item['iselected'],
                                                'officalemail' => $item['officalemail'],
                                                'officetelephone' => $item['officetelephone']),
                                          array("%d",'%s',"%d",'%s','%s'));
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
                                'iselected' => 0,
                                'officalemail' => '',
                                'officetelephone' => '');
      } else {
        $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `offices` WHERE `id` = %d",$this->viewid);
        $result = $officials_database->queryMySQL($sql);
        if ($result->num_rows > 0) {
          $this->viewitem = $result->fetch_assoc();
        } else {
          $this->viewmode = 'add';
          $this->viewid = 0;
          $this->viewitem = array('id' => 0,
                                  'name' => '',
                                  'iselected' => 0,
                                  'officalemail' => '',
                                  'officetelephone' => '');
        }
        $result->free();
      }
    }
    return $message;
  }
  
  function display_one_item_form()
  {
    
  }
        
    
} 

$offices = new Offices_Table();

?>
