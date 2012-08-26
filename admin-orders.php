<?php

// Display orders in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//
// - Orders are differnt, more complicated than normal tables:
//	- they can be edited (phone order) or not (online)
//	- the form will be custom not using the general framework



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Orders_Table extends WP_List_Table {

	function __construct($params) {
	  global $status, $page;
	  
	  // This is to list Orders of a Customer
	  if ($params) {
	  	$this->parent_id = $params['parent_id'];
	  }
	  if (!isset($this->parent_id)) {
    	// Checking if parent exists when dealing with forms 
    	$this->parent_id = $_REQUEST['parent_id'];
    }
    
    parent::__construct(array(
      'singular'  => 'comanda',
      'plural'  => 'comenzi',
      'ajax'	=> false //We won't support Ajax for this table
    ));
  }
	 
    
  /**
  * Define the columns that are going to be used in the table
  * @return array $columns, the array of columns to use with the table
  */
  function get_columns() {
    return $columns= array(
	    'id'=>__('Numar<br/>comanda'),
	    'type' => __('Tip comanda'),
	    'date'=>__('Data'),
	    'profile_id' => __('Cumparator'),
	    'products' => __('Produse'),	    
	    'grand_total'=>__('Total'),
	    'status_id' => __('Statut'),
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'type':
      case 'grand_total':
        return $item->$column_name;
      case 'date':
        return date('Y M d', strtotime($item->$column_name));
      case 'profile_id':
        $profile = $wpdb->get_results(
          "SELECT * FROM wp_shopper_profiles " .
          "WHERE wp_shopper_profiles.id = " . $item->profile_id
        ); 
        
        // Edit link
        $link = "<a href='?page=shopper-customers&action=edit&profiles=" . $profile[0]->id . "' title='Modificare cumparator'>";
        
        if (isset($profile[0]->name) && ($profile[0]->name != '')) {
          return $link . $profile[0]->name . "</a>";
        } else {
          return $link . $profile[0]->email . "</a>";
        }        
      case 'products':
        $products = $wpdb->get_results(
          "SELECT * FROM wp_shopper_order_items " .
          "WHERE wp_shopper_order_items.order_id = " . $item->id
        );        
        $counter = 0;
        foreach ($products as $product) {
          $counter += $product->product_qty;
        }        
        if ($counter == 1) {
          return $counter . ' produs';
        } else {
          return $counter . ' produse';
        }       
    	case 'status_id':
    		$status = $wpdb->get_results(
      		"SELECT * FROM wp_shopper_order_status " .
      		"WHERE id = " . $item->status_id
    		); 
    		if (isset($status[0])) {
      		return $status[0]->name;        
    		} else {
      		return $item->$column_name;
    		}
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Status
  function column_status_id($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&orders=%s">Edit</a>','shopper-orders','edit',$item->id),        
    );    
    
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->status_id,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  // Sort table
  function get_sortable_columns() {
    $sortable_columns = array(
        'id'     => array('id', false),     //true means its already sorted
        'type'    => array('type', true),
        'date'    => array('date', true),
        'profile_id'  => array('profile_id', false),
        'status_id'  => array('status_id', false)
    );
    return $sortable_columns;
  }
  
  
  // Get editable columns
  // - they differ based on order type (phone, online) and on action (add, edit)
  function get_editables($action) {
  	$ret = array();
  	
  	// Add all fields by default
  	$columns = $this->get_columns();
  	foreach ($columns as $k => $v) {
  		if (!in_array($k, array('id', 'products'))) {
  			$ret[] = array(
  				'title' => $v,
  				'id' => $k,
  				'required' => true
  			);
  		}
  	}
  	
  	// Customer is a select box
  	$ret[2]['type'] = 'select';
  	$v = array();
  	global $wpdb;
  	$customers = $wpdb->get_results("SELECT * FROM wp_shopper_profiles");  
  	foreach ($customers as $c) {
  		$v[] = array(
  			'value' => $c->id,
  			'title' => $c->name
  		); 
  	}
		$ret[2]['values'] = $v;	
  	
  	// Total is removed
  	unset($ret[3]);
  	
  	// Remove fields by special cases
  	switch ($action) {
  		case FORM_TITLE_ADD:
  			// When Add the order type will be 'phone'
  			$ret[0]['not_editable'] = true;
  			$ret[0]['value'] = 'phone';
  			
  			// Date is automatically set to now
  			$ret[1]['not_editable'] = true;
  			$ret[1]['value'] = date("Y-m-d H:i:s");
  			break;
  		
  		case FORM_TITLE_MODIFY:
  			// Order type not modificable
  			$ret[0]['not_editable'] = true;
  			
  			// Order date not modificable
  			$ret[1]['not_editable'] = true;
  			
  			// Customer not modificable
  			$ret[2]['not_editable'] = true;
  			break;
  	}
  	
  	
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
    $sortable = $this->get_sortable_columns();
    
    $this->_column_headers = array($columns, $hidden, $sortable);
    
    // Do the search
    // - we will use search to filter orders by month
    global $wpdb;            
    if (isset($_POST['s'])) {
      // the filter query must have the format '2012 08'
      $s = explode(" ", $_POST['s']);
      $start = date('Y-m-d', mktime(0, 0, 0, $s[1], 1, $s[0]));
      $start .= " 00:00:00";
      $end = date('Y-m-t', mktime(0, 0, 0, $s[1], 1, $s[0]));
      $end .= " 23:59:59";
      $data = $wpdb->get_results(
        "SELECT * FROM wp_shopper_orders " .
        "WHERE date >= '" . $start . "' AND date <='" . $end . "' ORDER BY date DESC"      
      );
    } else {
      $data = $wpdb->get_results(
        "SELECT * FROM wp_shopper_orders ORDER BY date DESC"     
      );
    }    
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
