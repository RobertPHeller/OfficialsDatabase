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
 *  Last Modified : <230918.1047>
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
    return 'name';
  }
  protected function column_default( $item, $column_name ) {
    return $item[$column_name];
  }
    
  protected function column_cb( $item ) {
    return '<input type="checkbox" name="checked[]" value="'.$item['id'].'" />';
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
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `people` order by %i $order",$orderby);
    } else {
      $sql = $officials_database->prepareQueryMySQL("SELECT * FROM `people` WHERE %i LIKE %s order by %i $order",$field,'%'.$search.'%',$orderby);
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
  
  public function officials_page()
  {
    $this->prepare_items();
  ?><h2>Officials</h2>
  <form method="post" action="index.php">
  <?php $this->search_box('Search Officials', 'officials');
        $this->display(); ?></form><?php
    
   
  }
  
} 

$officials = new Officials_Table();

?>
