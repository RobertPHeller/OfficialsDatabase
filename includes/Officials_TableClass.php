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
 *  Last Modified : <230917.1630>
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
    $args['plural']   = $this->sanitize_key( $plural );
    $args['singular'] = $this->sanitize_key( $singular );
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
      echo '<input type="hidden" name="orderby" value="' . $_REQUEST['orderby']  . '" />';
    }
    if ( ! empty( $_REQUEST['order'] ) ) {
      echo '<input type="hidden" name="order" value="' .  $_REQUEST['order']  . '" />';
    }
    if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
      echo '<input type="hidden" name="post_mime_type" value="' . $_REQUEST['post_mime_type'] . '" />';
    }
    if ( ! empty( $_REQUEST['detached'] ) ) {
      echo '<input type="hidden" name="detached" value="' .  $_REQUEST['detached'] . '" />';
    }
  ?>
  <p class="search-box">
  <label class="screen-reader-text" for="<?php echo  $input_id ; ?>"><?php echo $text; ?>:</label>
  <input type="search" id="<?php echo  $input_id ; ?>" name="s" value="<?php _admin_search_query(); ?>" />
  <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
  </p>
  <?php
  }
  /**
    * Retrieves the list of bulk actions available for this table.
    *
    * The format is an associative array where each element represents either a top level option value and label, or
    * an array representing an optgroup and its options.
    *
    * For a standard option, the array element key is the field value and the array element value is the field label.
    *
    * For an optgroup, the array element key is the label and the array element value is an associative array of
    * options as above.
    *
    * Example:
    *
    *     [
    *         'edit'         => 'Edit',
    *         'delete'       => 'Delete',
    *         'Change State' => [
    *             'feature' => 'Featured',
    *             'sale'    => 'On Sale',
    *         ]
    *     ]
    *
    *
    * @return array
    */
  protected function get_bulk_actions() {
    return array();
  }

  /**
    * Displays the bulk actions dropdown.
    *
    *
    * @param string $which The location of the bulk actions: 'top' or 'bottom'.
    *                      This is designated as optional for backward compatibility.
    */
  protected function bulk_actions( $which = '' ) {
    if ( is_null( $this->_actions ) ) {
      $this->_actions = $this->get_bulk_actions();
      $two = '';
    } else {
      $two = '2';
    }
    
    if ( empty( $this->_actions ) ) {
      return;
    }
    
    echo '<label for="bulk-action-selector-' .  $which . '" class="screen-reader-text">' .
    'Select bulk action' .
    '</label>';
    echo '<select name="action' . $two . '" id="bulk-action-selector-' . $which . "\">\n";
    echo '<option value="-1">' . 'Bulk actions' . "</option>\n";
    
    foreach ( $this->_actions as $key => $value ) {
      if ( is_array( $value ) ) {
        echo "\t" . '<optgroup label="' . $key . '">' . "\n";
        
        foreach ( $value as $name => $title ) {
          $class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';
          
          echo "\t\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
        }
        echo "\t" . "</optgroup>\n";
      } else {
        $class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';
        
        echo "\t" . '<option value="' . $key . '"' . $class . '>' . $value . "</option>\n";
      }
    }
    
    echo "</select>\n";
    
    submit_button( 'Apply', 'action', '', false, array( 'id' => "doaction$two" ) );
    echo "\n";
  }

  /**
    * Gets the current action selected from the bulk actions dropdown.
    *
    * @since 3.1.0
    *
    * @return string|false The action name. False if no action was selected.
    */
  public function current_action() {
    if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) {
      return false;
    }
    
    if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
      return $_REQUEST['action'];
    }
    
    return false;
  }

  /**
    * Generates the required HTML for a list of row action links.
    *
    * @since 3.1.0
    *
    * @param string[] $actions        An array of action links.
    * @param bool     $always_visible Whether the actions should be always visible.
    * @return string The HTML for the row actions.
    */
  protected function row_actions( $actions, $always_visible = false ) {
    $action_count = count( $actions );
    
    if ( ! $action_count ) {
      return '';
    }
    
    $output = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
    
    $i = 0;
    
    foreach ( $actions as $action => $link ) {
      ++$i;
      
      $separator = ( $i < $action_count ) ? ' | ' : '';
      
      $output .= "<span class='$action'>{$link}{$separator}</span>";
    }
    
    $output .= '</div>';
    
    $output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
    'Show more details' .
    '</span></button>';
    
    return $output;
  }

  /**
    * Gets the current page number.
    *
    * @since 3.1.0
    *
    * @return int
    */
  public function get_pagenum() {
    $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
    
    if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
      $pagenum = $this->_pagination_args['total_pages'];
    }
    
    return max( 1, $pagenum );
  }

  /**
    * Gets the number of items to display on a single page.
    *
    * @since 3.1.0
    *
    * @param string $option        User option name.
    * @param int    $default_value Optional. The number of items to display. Default 20.
    * @return int
    */
  protected function get_items_per_page( $option, $default_value = 20 ) {
    $per_page = (int) get_user_option( $option );
    if ( empty( $per_page ) || $per_page < 1 ) {
      $per_page = $default_value;
    }
    return (int) $per_page;
  }
  private function sanitize_key( $key ) {
    $sanitized_key = '';
    
    if ( is_scalar( $key ) ) {
      $sanitized_key = strtolower( $key );
      $sanitized_key = preg_replace( '/[^a-z0-9_\-]/', '', $sanitized_key );
    }
    return $sanitized_key;
  }
  private function esc_url( $url ) {
    $original_url = $url;
    
    if ( '' === $url ) {
      return $url;
    }
    
    $url = str_replace( ' ', '%20', ltrim( $url ) );
    $url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );
    
    if ( '' === $url ) {
      return $url;
    }
    
    $good_protocol_url = $url;
    return $good_protocol_url;
  }
  private function map_deep( $value, $callback ) {
    if ( is_array( $value ) ) {
      foreach ( $value as $index => $item ) {
        $value[ $index ] = $this->map_deep( $item, $callback );
      }
    } elseif ( is_object( $value ) ) {
      $object_vars = get_object_vars( $value );
      foreach ( $object_vars as $property_name => $property_value ) {
        $value->$property_name = $this->map_deep( $property_value, $callback );
      }
    } else {
      $value = call_user_func( $callback, $value );
    }
    
    return $value;
  }
  
  private function urlencode_deep( $value ) {
    return $this->map_deep( $value, 'urlencode' );
  }
  private function add_query_arg( ...$args ) {
    if ( is_array( $args[0] ) ) {
      if ( count( $args ) < 2 || false === $args[1] ) {
        $uri = $_SERVER['REQUEST_URI'];
      } else {
        $uri = $args[1];
      }
    } else {
      if ( count( $args ) < 3 || false === $args[2] ) {
        $uri = $_SERVER['REQUEST_URI'];
      } else {
        $uri = $args[2];
      }
    }
    
    $frag = strstr( $uri, '#' );
    if ( $frag ) {
      $uri = substr( $uri, 0, -strlen( $frag ) );
    } else {
      $frag = '';
    }
    
    if ( str_contains( $uri, '?' ) ) {
      list( $base, $query ) = explode( '?', $uri, 2 );
      $base                .= '?';
    } elseif ( ! str_contains( $uri, '=' ) ) {
      $base  = $uri . '?';
      $query = '';
    } else {
      $base  = '';
      $query = $uri;
    }
    
    parse_str( $query, $qs );
    $qs = $this->urlencode_deep( $qs ); // This re-URL-encodes things that were already in the query string.
    if ( is_array( $args[0] ) ) {
      foreach ( $args[0] as $k => $v ) {
        $qs[ $k ] = $v;
      }
    } else {
      $qs[ $args[0] ] = $args[1];
    }
    
    foreach ( $qs as $k => $v ) {
      if ( false === $v ) {
        unset( $qs[ $k ] );
      }
    }
    
    $ret = http_build_query( $qs, "", '&' );
    $ret = trim( $ret, '?' );
    $ret = preg_replace( '#=(&|$)#', '$1', $ret );
    $ret = $base . $ret . $frag;
    $ret = rtrim( $ret, '?' );
    $ret = str_replace( '?#', '#', $ret );
    return $ret;
  }
  
  private function remove_query_arg( $key, $query = false ) {
    if ( is_array( $key ) ) { // Removing multiple keys.
      foreach ( $key as $k ) {
        $query = $this->add_query_arg( $k, false, $query );
      }
      return $query;
    }
    return $this->add_query_arg( $key, false, $query );
  }
  
  /**
    * Displays the pagination.
    *
    * @since 3.1.0
    *
    * @param string $which
    */
  protected function pagination( $which ) {
    if ( empty( $this->_pagination_args ) ) {
      return;
    }
    
    $total_items     = $this->_pagination_args['total_items'];
    $total_pages     = $this->_pagination_args['total_pages'];
    $infinite_scroll = false;
    if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
      $infinite_scroll = $this->_pagination_args['infinite_scroll'];
    }
    
    if ( 'top' === $which && $total_pages > 1 ) {
      //$this->screen->render_screen_reader_content( 'heading_pagination' );
    }
    
    $output = '<span class="displaying-num">' . 
              sprintf($total_items==1?'%s item':'%s items',
                      number_format( $total_items ) ) . '</span>';
    
    $current              = $this->get_pagenum();
    $removable_query_args = array(
                                  'activate',
                                  'activated',
                                  'admin_email_remind_later',
                                  'approved',
                                  'core-major-auto-updates-saved',
                                  'deactivate',
                                  'delete_count',
                                  'deleted',
                                  'disabled',
                                  'doing_wp_cron',
                                  'enabled',
                                  'error',
                                  'hotkeys_highlight_first',
                                  'hotkeys_highlight_last',
                                  'ids',
                                  'locked',
                                  'message',
                                  'same',
                                  'saved',
                                  'settings-updated',
                                  'skipped',
                                  'spammed',
                                  'trashed',
                                  'unspammed',
                                  'untrashed',
                                  'update',
                                  'updated',
                                  );
    $current_url = $_SERVER['REQUEST_URI'];
    
    $page_links = array();
    
    $total_pages_before = '<span class="paging-input">';
    $total_pages_after  = '</span></span>';
    
    $disable_first = false;
    $disable_last  = false;
    $disable_prev  = false;
    $disable_next  = false;
    
    if ( 1 == $current ) {
      $disable_first = true;
      $disable_prev  = true;
    }
    if ( $total_pages == $current ) {
      $disable_last = true;
      $disable_next = true;
    }
    
    if ( $disable_first ) {
      $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
    } else {
      $page_links[] = sprintf(
                              "<a class='first-page button' href='%s'>" .
                              "<span class='screen-reader-text'>%s</span>" .
                              "<span aria-hidden='true'>%s</span>" .
                              '</a>',
                              $this->esc_url( $this->remove_query_arg( 'paged', $current_url ) ),
                              'First page',
                              '&laquo;'
                              );
    }
    
    if ( $disable_prev ) {
      $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
    } else {
      $page_links[] = sprintf(
                              "<a class='prev-page button' href='%s'>" .
                              "<span class='screen-reader-text'>%s</span>" .
                              "<span aria-hidden='true'>%s</span>" .
                              '</a>',
                              $this->esc_url( $this->add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
                              'Previous page',
                              '&lsaquo;'
                              );
    }
    
    if ( 'bottom' === $which ) {
      $html_current_page  = $current;
      $total_pages_before = sprintf(
                                    '<span class="screen-reader-text">%s</span>' .
                                    '<span id="table-paging" class="paging-input">' .
                                    '<span class="tablenav-paging-text">',
                                    'Current Page'
                                    );
    } else {
      $html_current_page = sprintf(
                                   '<label for="current-page-selector" class="screen-reader-text">%s</label>' .
                                   "<input class='current-page' id='current-page-selector' type='text'
                                   name='paged' value='%s' size='%d' aria-describedby='table-paging' />" .
                                   "<span class='tablenav-paging-text'>",
                                   'Current Page',
                                   $current,
                                   strlen( $total_pages )
                                   );
    }
    
    $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format( $total_pages ) );
    
    $page_links[] = $total_pages_before . sprintf('%s of %s',
                                                  $html_current_page,
                                                  $html_total_pages
                                                  ) . $total_pages_after;
    
    if ( $disable_next ) {
      $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
    } else {
      $page_links[] = sprintf(
                              "<a class='next-page button' href='%s'>" .
                              "<span class='screen-reader-text'>%s</span>" .
                              "<span aria-hidden='true'>%s</span>" .
                              '</a>',
                              $this->esc_url( $this->add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
                              'Next page',
                              '&rsaquo;'
                              );
    }
    
    if ( $disable_last ) {
      $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
    } else {
      $page_links[] = sprintf(
                              "<a class='last-page button' href='%s'>" .
                              "<span class='screen-reader-text'>%s</span>" .
                              "<span aria-hidden='true'>%s</span>" .
                              '</a>',
                              $this->esc_url( $this->add_query_arg( 'paged', $total_pages, $current_url ) ),
                              'Last page',
                              '&raquo;'
                              );
    }
    
    $pagination_links_class = 'pagination-links';
    if ( ! empty( $infinite_scroll ) ) {
      $pagination_links_class .= ' hide-if-js';
    }
    $output .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';
    
    if ( $total_pages ) {
      $page_class = $total_pages < 2 ? ' one-page' : '';
    } else {
      $page_class = ' no-pages';
    }
    $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";
    
    echo $this->_pagination;
  }
  
  /**
    * Gets a list of columns.
    *
    * The format is:
    * - `'internal-name' => 'Title'`
    *
    * @since 3.1.0
    * @abstract
    *
    * @return array
    */
  public function get_columns() {
    die( 'function Officials_TableClass::get_columns() must be overridden in a subclass.' );
  }
  
  /**
    * Gets a list of sortable columns.
    *
    * The format is:
    * - `'internal-name' => 'orderby'`
    * - `'internal-name' => array( 'orderby', bool, 'abbr', 'orderby-text', 'initially-sorted-column-order' )` -
    * - `'internal-name' => array( 'orderby', 'asc' )` - The second element sets the initial sorting order.
    * - `'internal-name' => array( 'orderby', true )`  - The second element makes the initial order descending.
    *
    * In the second format, passing true as second parameter will make the initial
    * sorting order be descending. Following parameters add a short column name to
    * be used as 'abbr' attribute, a translatable string for the current sorting,
    * and the initial order for the initial sorted column, 'asc' or 'desc' (default: false).
    *
    * @since 3.1.0
    * @since 6.3.0 Added 'abbr', 'orderby-text' and 'initially-sorted-column-order'.
    *
    * @return array
    */
  protected function get_sortable_columns() {
    return array();
  }
  
  /**
    * Gets the name of the default primary column.
    *
    * @since 4.3.0
    *
    * @return string Name of the default primary column, in this case, an empty string.
    */
  protected function get_default_primary_column_name() {
    $columns = $this->get_columns();
    $column  = '';
    
    if ( empty( $columns ) ) {
      return $column;
    }
    
    /*
      * We need a primary defined so responsive views show something,
      * so let's fall back to the first non-checkbox column.
      */
    foreach ( $columns as $col => $column_name ) {
      if ( 'cb' === $col ) {
        continue;
      }
      
      $column = $col;
      break;
    }
    
    return $column;
  }
  
  /**
    * Gets the name of the primary column.
    *
    * Public wrapper for WP_List_Table::get_default_primary_column_name().
    *
    * @since 4.4.0
    *
    * @return string Name of the default primary column.
    */
  public function get_primary_column() {
    return $this->get_primary_column_name();
  }
  
  /**
    * Gets the name of the primary column.
    *
    * @since 4.3.0
    *
    * @return string The name of the primary column.
    */
  protected function get_primary_column_name() {
    $columns = $this->get_columns( );
    $default = $this->get_default_primary_column_name();
    
    /*
      * If the primary column doesn't exist,
      * fall back to the first non-checkbox column.
      */
    if ( ! isset( $columns[ $default ] ) ) {
      $default = self::get_default_primary_column_name();
    }
    
    $column = $default;
    
    if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
      $column = $default;
    }
    
    return $column;
  }
  
  /**
    * Gets a list of all, hidden, and sortable columns, with filter applied.
    *
    * @since 3.1.0
    *
    * @return array
    */
  protected function get_column_info() {
    // $_column_headers is already set / cached.
    if (
        isset( $this->_column_headers ) &&
        is_array( $this->_column_headers )
        ) {
      /*
        * Backward compatibility for `$_column_headers` format prior to WordPress 4.3.
        *
        * In WordPress 4.3 the primary column name was added as a fourth item in the
        * column headers property. This ensures the primary column name is included
        * in plugins setting the property directly in the three item format.
        */
      if ( 4 === count( $this->_column_headers ) ) {
        return $this->_column_headers;
      }
      
      $column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
      foreach ( $this->_column_headers as $key => $value ) {
        $column_headers[ $key ] = $value;
      }
      
      $this->_column_headers = $column_headers;
      
      return $this->_column_headers;
    }
    
    $columns = $this->get_columns( );
    $hidden  = $this->get_hidden_columns( );
    
    $sortable_columns = $this->get_sortable_columns();
    /**
      * Filters the list table sortable columns for a specific screen.
      *
      * The dynamic portion of the hook name, `$this->screen->id`, refers
      * to the ID of the current screen.
      *
      * @since 3.1.0
      *
      * @param array $sortable_columns An array of sortable columns.
      */
    $_sortable = $sortable_columns;
    
    $sortable = array();
    foreach ( $_sortable as $id => $data ) {
      if ( empty( $data ) ) {
        continue;
      }
      
      $data = (array) $data;
      // Descending initial sorting.
      if ( ! isset( $data[1] ) ) {
        $data[1] = false;
      }
      // Current sorting translatable string.
      if ( ! isset( $data[2] ) ) {
        $data[2] = '';
      }
      // Initial view sorted column and asc/desc order, default: false.
      if ( ! isset( $data[3] ) ) {
        $data[3] = false;
      }
      // Initial order for the initial sorted column, default: false.
      if ( ! isset( $data[4] ) ) {
        $data[4] = false;
      }
      
      $sortable[ $id ] = $data;
    }
    
    $primary               = $this->get_primary_column_name();
    $this->_column_headers = array( $columns, $hidden, $sortable, $primary );
    
    return $this->_column_headers;
  }

  /**
    * Returns the number of visible columns.
    *
    * @since 3.1.0
    *
    * @return int
    */
  public function get_column_count() {
    list ( $columns, $hidden ) = $this->get_column_info();
    $hidden                    = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
    return count( $columns ) - count( $hidden );
  }
  
  /**
    * Prints column headers, accounting for hidden and sortable columns.
    *
    * @since 3.1.0
    *
    * @param bool $with_id Whether to set the ID attribute or not
    */
  public function print_column_headers( $with_id = true ) {
    list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
    
    $current_url = $_SERVER['REQUEST_URI'];
    $current_url = $this->remove_query_arg( 'paged', $current_url );
    
    // When users click on a column header to sort by other columns.
    if ( isset( $_GET['orderby'] ) ) {
      $current_orderby = $_GET['orderby'];
      // In the initial view there's no orderby parameter.
    } else {
      $current_orderby = '';
    }
    
    // Not in the initial view and descending order.
    if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
      $current_order = 'desc';
    } else {
      // The initial view is not always 'asc', we'll take care of this below.
      $current_order = 'asc';
    }
    
    if ( ! empty( $columns['cb'] ) ) {
      static $cb_counter = 1;
      $columns['cb']     = '<label class="label-covers-full-cell" for="cb-select-all-' . $cb_counter . '">' .
      '<span class="screen-reader-text">' .
      /* translators: Hidden accessibility text. */
      'Select All'.
      '</span>' .
      '</label>' .
      '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
      $cb_counter++;
    }
    
    foreach ( $columns as $column_key => $column_display_name ) {
      $class          = array( 'manage-column', "column-$column_key" );
      $aria_sort_attr = '';
      $abbr_attr      = '';
      $order_text     = '';
      
      if ( in_array( $column_key, $hidden, true ) ) {
        $class[] = 'hidden';
      }
      
      if ( 'cb' === $column_key ) {
        $class[] = 'check-column';
      } elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
        $class[] = 'num';
      }
      
      if ( $column_key === $primary ) {
        $class[] = 'column-primary';
      }
      
      if ( isset( $sortable[ $column_key ] ) ) {
        $orderby       = isset( $sortable[ $column_key ][0] ) ? $sortable[ $column_key ][0] : '';
        $desc_first    = isset( $sortable[ $column_key ][1] ) ? $sortable[ $column_key ][1] : false;
        $abbr          = isset( $sortable[ $column_key ][2] ) ? $sortable[ $column_key ][2] : '';
        $orderby_text  = isset( $sortable[ $column_key ][3] ) ? $sortable[ $column_key ][3] : '';
        $initial_order = isset( $sortable[ $column_key ][4] ) ? $sortable[ $column_key ][4] : '';
        
        /*
          * We're in the initial view and there's no $_GET['orderby'] then check if the
          * initial sorting information is set in the sortable columns and use that.
          */
        if ( '' === $current_orderby && $initial_order ) {
          // Use the initially sorted column $orderby as current orderby.
          $current_orderby = $orderby;
          // Use the initially sorted column asc/desc order as initial order.
          $current_order = $initial_order;
        }
        
        /*
          * True in the initial view when an initial orderby is set via get_sortable_columns()
          * and true in the sorted views when the actual $_GET['orderby'] is equal to $orderby.
          */
        if ( $current_orderby === $orderby ) {
          // The sorted column. The `aria-sort` attribute must be set only on the sorted column.
          if ( 'asc' === $current_order ) {
            $order          = 'desc';
            $aria_sort_attr = ' aria-sort="ascending"';
          } else {
            $order          = 'asc';
            $aria_sort_attr = ' aria-sort="descending"';
          }
          
          $class[] = 'sorted';
          $class[] = $current_order;
        } else {
          // The other sortable columns.
          $order = strtolower( $desc_first );
          
          if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
            $order = $desc_first ? 'desc' : 'asc';
          }
          
          $class[] = 'sortable';
          $class[] = 'desc' === $order ? 'asc' : 'desc';
          
          /* translators: Hidden accessibility text. */
          $asc_text = 'Sort ascending.';
          /* translators: Hidden accessibility text. */
          $desc_text  = 'Sort descending.';
          $order_text = 'asc' === $order ? $asc_text : $desc_text;
        }
        
        if ( '' !== $order_text ) {
          $order_text = ' <span class="screen-reader-text">' . $order_text . '</span>';
        }
        
        // Print an 'abbr' attribute if a value is provided via get_sortable_columns().
        $abbr_attr = $abbr ? ' abbr="' . $abbr . '"' : '';
        
        $column_display_name = sprintf(
                                       '<a href="%1$s">' .
                                       '<span>%2$s</span>' .
                                       '<span class="sorting-indicators">' .
                                       '<span class="sorting-indicator asc" aria-hidden="true"></span>' .
                                       '<span class="sorting-indicator desc" aria-hidden="true"></span>' .
                                       '</span>' .
                                       '%3$s' .
                                       '</a>',
                                       $this->esc_url( $this->add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
                                       $column_display_name,
                                       $order_text
                                       );
      }
      
      $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
      $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
      $id    = $with_id ? "id='$column_key'" : '';
      
      if ( ! empty( $class ) ) {
        $class = "class='" . implode( ' ', $class ) . "'";
      }
      
      echo "<$tag $scope $id $class $aria_sort_attr $abbr_attr>$column_display_name</$tag>";
    }
  }
  
  /**
    * Print a table description with information about current sorting and order.
    *
    * For the table initial view, information about initial orderby and order
    * should be provided via get_sortable_columns().
    *
    * @since 6.3.0
    * @access public
    */
  public function print_table_description() {
    list( $columns, $hidden, $sortable ) = $this->get_column_info();
    
    if ( empty( $sortable ) ) {
      return;
    }
    
    // When users click on a column header to sort by other columns.
    if ( isset( $_GET['orderby'] ) ) {
      $current_orderby = $_GET['orderby'];
      // In the initial view there's no orderby parameter.
    } else {
      $current_orderby = '';
    }
    
    // Not in the initial view and descending order.
    if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
      $current_order = 'desc';
    } else {
      // The initial view is not always 'asc', we'll take care of this below.
      $current_order = 'asc';
    }
    
    foreach ( array_keys( $columns ) as $column_key ) {
      
      if ( isset( $sortable[ $column_key ] ) ) {
        $orderby       = isset( $sortable[ $column_key ][0] ) ? $sortable[ $column_key ][0] : '';
        $desc_first    = isset( $sortable[ $column_key ][1] ) ? $sortable[ $column_key ][1] : false;
        $abbr          = isset( $sortable[ $column_key ][2] ) ? $sortable[ $column_key ][2] : '';
        $orderby_text  = isset( $sortable[ $column_key ][3] ) ? $sortable[ $column_key ][3] : '';
        $initial_order = isset( $sortable[ $column_key ][4] ) ? $sortable[ $column_key ][4] : '';
        
        if ( ! is_string( $orderby_text ) || '' === $orderby_text ) {
          return;
        }
        /*
          * We're in the initial view and there's no $_GET['orderby'] then check if the
          * initial sorting information is set in the sortable columns and use that.
          */
        if ( '' === $current_orderby && $initial_order ) {
          // Use the initially sorted column $orderby as current orderby.
          $current_orderby = $orderby;
          // Use the initially sorted column asc/desc order as initial order.
          $current_order = $initial_order;
        }
        
        /*
          * True in the initial view when an initial orderby is set via get_sortable_columns()
          * and true in the sorted views when the actual $_GET['orderby'] is equal to $orderby.
          */
        if ( $current_orderby === $orderby ) {
          /* translators: Hidden accessibility text. */
          $asc_text = 'Ascending.';
          /* translators: Hidden accessibility text. */
          $desc_text  = 'Descending.';
          $order_text = 'asc' === $current_order ? $asc_text : $desc_text;
          echo '<caption class="screen-reader-text">' . $orderby_text . ' ' . $order_text . '</caption>';
          
          return;
        }
      }
    }
  }
  
  /**
    * Displays the table.
    *
    * @since 3.1.0
    */
  public function display() {
    $singular = $this->_args['singular'];
    
    $this->display_tablenav( 'top' );
    
    //$this->screen->render_screen_reader_content( 'heading_list' );
  ?>
  <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    <?php $this->print_table_description(); ?>
    <thead>
    <tr>
      <?php $this->print_column_headers(); ?>
    </tr>
    </thead>
    
    <tbody id="the-list"
     <?php
       if ( $singular ) {
         echo " data-wp-lists='list:$singular'";
       }
     ?>
     >
    <?php $this->display_rows_or_placeholder(); ?>
    </tbody>
    
    <tfoot>
    <tr>
      <?php $this->print_column_headers( false ); ?>
    </tr>
    </tfoot>
    
  </table>
  <?php
    $this->display_tablenav( 'bottom' );
  }
  
  /**
    * Gets a list of CSS classes for the WP_List_Table table tag.
    *
    * @since 3.1.0
    *
    * @return string[] Array of CSS classes for the table tag.
    */
  protected function get_table_classes() {
    $mode =  'list';
    
    $mode_class = 'table-view-' . $mode;
    
    return array( 'widefat', 'fixed', 'striped', $mode_class, $this->_args['plural'] );
  }
  
  /**
    * Generates the table navigation above or below the table
    *
    * @since 3.1.0
    * @param string $which
    */
  protected function display_tablenav( $which ) {
  ?>
  <div class="tablenav <?php echo $which; ?>">
    
    <?php if ( $this->has_items() ) : ?>
    <div class="alignleft actions bulkactions">
      <?php $this->bulk_actions( $which ); ?>
    </div>
    <?php
      endif;
      $this->extra_tablenav( $which );
      $this->pagination( $which );
    ?>
    
    <br class="clear" />
  </div>
  <?php
  }
  
  /**
    * Displays extra controls between bulk actions and pagination.
    *
    * @since 3.1.0
    *
    * @param string $which
    */
  protected function extra_tablenav( $which ) {}
  
  /**
    * Generates the tbody element for the list table.
    *
    * @since 3.1.0
    */
  public function display_rows_or_placeholder() {
    if ( $this->has_items() ) {
      $this->display_rows();
    } else {
      echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
      $this->no_items();
      echo '</td></tr>';
    }
  }
  
  /**
    * Generates the table rows.
    *
    * @since 3.1.0
    */
  public function display_rows() {
    foreach ( $this->items as $item ) {
      $this->single_row( $item );
    }
  }
  
  /**
    * Generates content for a single row of the table.
    *
    * @since 3.1.0
    *
    * @param object|array $item The current item
    */
  public function single_row( $item ) {
    echo '<tr>';
    $this->single_row_columns( $item );
    echo '</tr>';
  }
  
  /**
    * @param object|array $item
    * @param string $column_name
    */
  protected function column_default( $item, $column_name ) {}
  
  /**
    * @param object|array $item
    */
  protected function column_cb( $item ) {}
  
  /**
    * Generates the columns for a single row of the table.
    *
    * @since 3.1.0
    *
    * @param object|array $item The current item.
    */
  protected function single_row_columns( $item ) {
    list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
    
    foreach ( $columns as $column_name => $column_display_name ) {
      $classes = "$column_name column-$column_name";
      if ( $primary === $column_name ) {
        $classes .= ' has-row-actions column-primary';
      }
      
      if ( in_array( $column_name, $hidden, true ) ) {
        $classes .= ' hidden';
      }
      
      /*
        * Comments column uses HTML in the display name with screen reader text.
        * Strip tags to get closer to a user-friendly string.
        */
      $data = 'data-colname="' . $column_display_name . '"';
      
      $attributes = "class='$classes' $data";
      
      file_put_contents("php://stderr","*** Officials_List_Table::single_row_columns(): column_name is $column_name\n");
      if ( 'cb' === $column_name ) {
        echo '<th scope="row" class="check-column">';
        echo $this->column_cb( $item );
        echo '</th>';
      } elseif ( method_exists( $this, '_column_' . $column_name ) ) {
        echo call_user_func(
                            array( $this, '_column_' . $column_name ),
                            $item,
                            $classes,
                            $data,
                            $primary
                            );
      } elseif ( method_exists( $this, 'column_' . $column_name ) ) {
        echo "<td $attributes>";
        echo call_user_func( array( $this, 'column_' . $column_name ), $item );
        echo $this->handle_row_actions( $item, $column_name, $primary );
        echo '</td>';
      } else {
        echo "<td $attributes>";
        echo $this->column_default( $item, $column_name );
        echo $this->handle_row_actions( $item, $column_name, $primary );
        echo '</td>';
      }
    }
  }

  /**
    * Generates and display row actions links for the list table.
    *
    * @since 4.3.0
    *
    * @param object|array $item        The item being acted upon.
    * @param string       $column_name Current column name.
    * @param string       $primary     Primary column name.
    * @return string The row actions HTML, or an empty string
    *                if the current column is not the primary column.
    */
  protected function handle_row_actions( $item, $column_name, $primary ) {
    return $column_name === $primary ? '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
    /* translators: Hidden accessibility text. */
    'Show more details'.
    '</span></button>' : '';
  }
  
}

  



  
  
?>

