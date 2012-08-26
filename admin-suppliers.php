<?php

// Display Suppliers in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Suppliers_Table extends WP_List_Table {

	function __construct($params) {
	  global $status, $page;
	  
    parent::__construct(array(
      'singular'  => 'supplier',
      'plural'  => 'suppliers',
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
	    'name' => __('Nume'),
	    'description'=>__('Descriere'),
	    'contact' => __('Contact')
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
      case 'contact':
        return $item->$column_name;
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Product Name
  function column_name($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=shopper-suppliers&action=%s&suppliers=%s&parent_id=%s">Edit</a>','edit',$item->id, $this->parent_id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->name,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  // Get editable columns
  function get_editables() {
  	$ret = array();
  	
  	$columns = $this->get_columns();
  	foreach ($columns as $k => $v) {
  		if (!in_array($k,  array('id'))) {
  			$ret[] = array(
  				'title' => $v,
  				'id' => $k,
  				'required' => true
  			);
  		}
  	}
  	
  	$ret[1]['type'] = 'textarea';
  	$ret[2]['type'] = 'textarea';
  	
  	return $ret;
  }
  
  // Define detail tables
  function get_detail_tables($parent_id) {
  	$ret = array();
  	
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
      "SELECT * FROM wp_shopper_suppliers"      
    );
    
    function usort_reorder_suppliers($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder_suppliers');
    
    
        
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