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
 *  Created       : Sat Sep 16 16:55:57 2023
 *  Last Modified : <230916.1716>
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
 * Base class for displaying a list of items in an ajaxified HTML table.
 * (lifted and modified from WordPress's WP_List_Table)
 */
 
class Officials_List_Table {
  /**                                                                     
   * The current list of items.                                           
   *                                                                      
   * @var array                                                           
   */                                                                     
  public $items;
  /**                                                                     
    * Various information about the current table.                         
    *                                                                      
    * @var array                                                           
    */                                                                     
  protected $_args;
  /**                                                                     
    * Various information needed for displaying the pagination.            
    *                                                                      
    * @var array                                                           
    */                                                                     
  protected $_pagination_args = array();  
  /**
    * Cached bulk actions.
    *
    * @var array
    */
  private $_actions;
  
  /**
    * Cached pagination output.
    *
    * @var string
    */
  private $_pagination;
  /**
    * Stores the value returned by ->get_column_info().
    *
    * @since 4.1.0
    * @var array
    */
  protected $_column_headers;
  /**
    * Constructor.
    *
    * The child class should call this constructor from its own constructor to override
    * the default $args.
    *
    * @since 3.1.0
    *
    * @param $plural    Plural value used for labels and the objects being listed.
    *                   This affects things such as CSS class-names and nonces used
    *                   in the list table, e.g. 'posts'. Default empty.
    * @param $singular  Singular label for an object being listed, e.g. 'post'.
    *                   Default empty
    */
  public function __construct( $plural = "", $singular = "" ) {
    $args['plural']   = sanitize_key( $plural );
    $args['singular'] = sanitize_key( $singular );
    $this->_args = $args;
  }
  /**
    * Prepares the list of items for displaying.
    *
    * @uses Officials_List_Table::set_pagination_args()
    *
    * @abstract
    */
  public function prepare_items() {
    die( 'function Officials_List_Table::prepare_items() must be overridden in a subclass.' );
  }
  /**
    * Sets all the necessary pagination arguments.
    *
    * @param array|string $args Array or string of arguments with information about the pagination.
    */
  protected function set_pagination_args( $total_items = 0, $total_pages = 0, $per_page = 0 ) {
    if ( ! $total_pages && $per_page > 0 ) {
      $total_pages = ceil( $total_items / $per_page );
    }
    
    // Redirect if page number is invalid and headers are not already sent.
    //if ( ! headers_sent() && ! wp_doing_ajax() && $total_pages > 0 && $this->get_pagenum() > $total_pages ) {
    //  wp_redirect( add_query_arg( 'paged', $total_pages ) );
    //  exit;
    //}
    
    $this->_pagination_args = array('total_items' => $total_items,
                                    'total_pages' => $total_pages,
                                    'per_page'    => $per_page);
                                    
  }
  /**
    * Access the pagination args.
    *
    * @param string $key Pagination argument to retrieve. Common values include 'total_items',
    *                    'total_pages', 'per_page', or 'infinite_scroll'.
    * @return int Number of items that correspond to the given pagination argument.
    */
  public function get_pagination_arg( $key ) {
    if ( 'page' === $key ) {
      return $this->get_pagenum();
    }
    
    if ( isset( $this->_pagination_args[ $key ] ) ) {
      return $this->_pagination_args[ $key ];
    }
    
    return 0;
  }
  /**
    * Determines whether the table has items to display or not
    *
    * @return bool
    */
  public function has_items() {
    return ! empty( $this->items );
  }
  
  /**
    * Message to be displayed when there are no items
    *
    */
  public function no_items() {
    'No items found.';
  }
  /**
    * Displays the search box.
    *
    * @param string $text     The 'submit' button label.
    * @param string $input_id ID attribute value for the search input field.
    */
  public function search_box( $text, $input_id ) {
    if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
      return;
    }
    
    $input_id = $input_id . '-search-input';
    
    if ( ! empty( $_REQUEST['orderby'] ) ) {
      echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
    }
    if ( ! empty( $_REQUEST['order'] ) ) {
      echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
    }
    if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
      echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
    }
    if ( ! empty( $_REQUEST['detached'] ) ) {
      echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
    }
  ?>
  <p class="search-box">
  <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
  <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
  <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
  </p>
  <?php
  }

  



  
  
?>
