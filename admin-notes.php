<?php

// Display Notes in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//
// Notes cannot be edited directly only via parents




if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Notes_Table extends WP_List_Table {

	function __construct($params) {
	  global $status, $page;
	  
	  // Checking if the parent exists when new Notes_Table is initialized
	  if ($params) {
	  	$this->parent_id = $params['entry_id'];
	  	$this->table_id = $params['table_id'];
	  }
	  
    if ((!isset($this->parent_id)) || (!isset($this->table_id))) {
    	
    	// Checking if parent exists when dealing with forms 
    	$this->parent_id = $_REQUEST['parent_id'];
    	$this->table_id = $_REQUEST['table_id'];
    	if ((!isset($this->parent_id)) || (!isset($this->table_id))) {
    		wp_die("Modificarea directa acestor date nu este permis.");
    	}
    }
    
    parent::__construct(array(
      'singular'  => 'observatie',
      'plural'  => 'observatii',
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
	    'table_id' => __('Apartine'),
	    'entry_id'=>__('Nume'),
	    'body' => __('Observatie')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'body':
        return $item->$column_name;
    	case 'table_id':
    		echo "column_name: " . $item->$column_name;
    		switch ($item->$column_name) {
    			case 'profiles':
    				return "Cumparatori";
    			default:
    				return $item->$column_name;
    		}
      case 'entry_id':
      	global $wpdb;
      	$table = $wpdb->prefix . 'shopper_' . $this->table_id;
      	$parent = $wpdb->get_results(
      		"SELECT * FROM $table WHERE id = " . $this->parent_id      
    		);
    		
    		// Clicking on parent goes back to the parents edit page
    		return "<a href='?page=shopper-profiles&action=edit&profiles=" . $this->parent_id . "'>" . $parent[0]->name . "</a>";
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Address
  function column_body($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=shopper-notes&action=%s&notes=%s&parent_id=%s&table_id=%s">Edit</a>','edit',$item->id, $this->parent_id, $this->table_id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->body,
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
  	
  	// Mark table_id relationship ...
  	$ret[0]['not_editable'] = true;
  	$ret[0]['value'] = $this->table_id;
  	$ret[0]['foreign_name'] = $this->column_default($columns[0], 'table_id');
  	
  	// Mark entry_id relationship ...
  	$ret[1]['not_editable'] = true;
  	$ret[1]['value'] = $this->parent_id;
  	$ret[1]['foreign_name'] = $this->column_default($columns[0], 'entry_id');
  	
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
      "SELECT * FROM wp_shopper_notes WHERE entry_id = " . $this->parent_id      
    );
    //print_r($data);
    
    
    function usort_reorder2($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder2');
    
    
        
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