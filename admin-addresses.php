<?php

// Display addresses in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//
// Adresses cannot be edited directly only via Customers / Profiles




if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Addresses_Table extends WP_List_Table {

	function __construct($params) {
	  global $status, $page;
	  
	  // Checking if the parent exists when new Addresses_Table is initialized
	  if ($params) {
	  	$this->parent_id = $params['parent_id'];
	  }
	  
    if (!isset($this->parent_id)) {
    	
    	// Checking if parent exists when dealing with forms 
    	$this->parent_id = $_REQUEST['parent_id'];
    	if (!isset($this->parent_id)) {
    		wp_die("Modificarea directa acestor date nu este permis.");
    	}
    }
    
    parent::__construct(array(
      'singular'  => 'adresa',
      'plural'  => 'adrese',
      'ajax'	=> false 
    ));
  }
	 
    
  /**
  * Define the columns that are going to be used in the table
  * @return array $columns, the array of columns to use with the table
  */
  function get_columns() {
    return $columns= array(
	    'id'=>__('#'),
	    'profile_id' => __('Cumparator'),
	    'address'=>__('Adresa'),
	    'city' => __('Oras'),
	    'judet' => __('Judet')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'address':
      case 'city':
      case 'judet':
        return $item->$column_name;
      case 'profile_id':
      	global $wpdb;
      	$customer = $wpdb->get_results(
      		"SELECT * FROM wp_shopper_profiles WHERE id = " . $this->parent_id      
    		);
    		
    		// Clicking on parent goes back to the parents edit page
    		return "<a href='?page=shopper-profiles&action=edit&profiles=" . $this->parent_id . "'>" . $customer[0]->name . "</a>";
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Address
  function column_address($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=shopper-addresses&action=%s&addresses=%s&parent_id=%s">Edit</a>','edit',$item->id, $this->parent_id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->address,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  // Get editable columns
  function get_editables() {
  	$ret = array();
  	
  	$columns = $this->get_columns();
  	foreach ($columns as $k => $v) {
  		if ($k != 'id') {
  			$ret[] = array(
  				'title' => $v,
  				'id' => $k,
  				'required' => true
  			);
  		}
  	}
  	
  	// Add customer relationship ...
  	$ret[0]['not_editable'] = true;
  	$ret[0]['value'] = $this->parent_id;
  	
  	return $ret;
  }
  
  // Define detail tables
  function get_detail_tables($parent_id) {
  	$ret = array();
  	
  	return $ret;
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
      "SELECT * FROM wp_shopper_addresses WHERE profile_id = " . $this->parent_id      
    );
    //print_r($data);
    
    
    function usort_reorder_addresses($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder_addresses');
    
    
        
    $current_page = $this->get_pagenum();
    
    $total_items = count($data);
    $this->total_items = $total_items;
    
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
