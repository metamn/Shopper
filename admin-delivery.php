<?php

// Display delivery in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Delivery_Table extends WP_List_Table {

	function __construct() {
	  global $status, $page;
    
    parent::__construct(array(
      'singular'  => 'statut',
      'plural'  => 'statut',
      'ajax'	=> false //We won't support Ajax for this table
    ));
  }
	 
    
  /**
  * Define the columns that are going to be used in the table
  * @return array $columns, the array of columns to use with the table
  */
  function get_columns() {
    return $columns= array(
	    'id'=>__('#'),
	    'name'=>__('Metoda'),
	    'description' => __('Descriere'),
	    'price' => __('Pret'),
	    'duration' => __('Timp livrare'),
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'name':
      case 'description':
      case 'price':
      case 'duration':
        return $item->$column_name;
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Status
  function column_name($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&order=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->name,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  /**
   * Add extra markup in the toolbars before or after the list
   * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
   */
  function extra_tablenav( $which ) {  
    $add = "<a href='?page=shopper-delivery&action=edit' title='Adaugare'>Adaugare</a>";  
	  if ( $which == "top" ){
		  //The code that goes before the table is here
		  echo $add;
	  }
	  if ( $which == "bottom" ){
		  //The code that goes after the table is there
		  echo $add;
	  }
  }

  
  
  /**
  * Prepare the table with different parameters, pagination, columns and table elements
  */
  function prepare_items() {
    $per_page = 15;
    
    $columns = $this->get_columns();
    $hidden = array();
    
    $this->_column_headers = array($columns, $hidden, array());
    
    global $wpdb;
    $data = $wpdb->get_results(
      "SELECT * FROM wp_shopper_order_delivery"     
    );
    //print_r($data);
    
    
    function usort_reorder($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder');
    
    
        
    $current_page = $this->get_pagenum();
    $total_items = count($data);
    $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
    $this->items = $data;
    $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
    ) );
  }
}


?>
