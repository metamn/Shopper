<?php

// Display customers in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Customers_Table extends WP_List_Table {

	function __construct() {
	  global $status, $page;
    
    parent::__construct(array(
      'singular'  => 'cumparator',
      'plural'  => 'cumparatori',
      'ajax'	=> true
    ));
  }
	 
    
  /**
  * Define the columns that are going to be used in the table
  * @return array $columns, the array of columns to use with the table
  */
  function get_columns() {
    return $columns= array(
	    'id'=>__('#'),
	    'name' => __('Nume'),
	    'email'=>__('Email'),
	    'phone' => __('Telefon')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    switch($column_name){  
      case 'id':
      case 'email':
      case 'phone':
      case 'name':
        return $item->$column_name;    
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Return which fields are editable
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
  	return $ret;
  }
  
  
  // Define detail tables
  function get_detail_tables($parent_id) {
  	$ret = array();
  	
  	// Addresses
  	$params = array(
  		'parent_id' => $parent_id
  	);
  	$ret[] = array(
  		'title' => "Adrese",
  		'page' => 'addresses',
  		'table' => new Addresses_Table($params)
  	);
  	
  	// Notes
  	$params = array(
  		'entry_id' => $parent_id,
  		'table_id' => 'profiles'
  	);
  	$ret[] = array(
  		'title' => "Observatii",
  		'page' => 'notes',
  		'table' => new Notes_Table($params)
  	);
  	
  	// Orders
  	$params = array(
  		'parent_id' => $parent_id
  	);
  	$ret[] = array(
  		'title' => "Comenzi",
  		'page' => 'orders',
  		'table' => new Orders_Table($params)
  	);
  	
  	
  	return $ret;
  }
  
  
  // Sort columns
  function get_sortable_columns() {
    $sortable_columns = array(
        'id'     => array('id', false),     //true means its already sorted
        'name'    => array('name', false),
        'city'  => array('city', false)
    );
    return $sortable_columns;
  }
  
  
  // Add Edit to email, name
  function column_email($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&profiles=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),        
    );
    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->email,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  function column_name($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&profiles=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),        
    );
    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->name,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  /**
  * Prepare the table with different parameters, pagination, columns and table elements
  */
  function prepare_items() {
    $per_page = 15;
    
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    
    $this->_column_headers = array($columns, $hidden, $sortable);
    
    // Do the search
    global $wpdb;            
    if (isset($_POST['s'])) {
      $s = $_POST['s'];
      $data = $wpdb->get_results(
        "SELECT * FROM wp_shopper_profiles " .
        "WHERE (email='" . $s . "' OR phone='" . $s . "' OR name='" . $s . "')"     
      );
    } else {
      $data = $wpdb->get_results(
        "SELECT * FROM wp_shopper_profiles ORDER BY id DESC"     
      );
    }    
    //print_r($data);
    
    
    function usort_reorder_profiles($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder_profiles');
    
    
        
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
