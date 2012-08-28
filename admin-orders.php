<?php

// Display orders in Admin
// --------------------------------------------------------------------------------



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
	    'type' => __('Tip<br/>comanda'),
	    'date'=>__('Data'),
	    'profile_id' => __('Cumparator'),
	    'products' => __('Produse'),	    
	    'total'=>__('Total'),
	    'status_id' => __('Statut'),
	    'delivery_date' => __('Data livrarii')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'total':
        return $item->$column_name;
    	case 'type':
    		global $ORDER_TYPES;
      	return $ORDER_TYPES[$item->$column_name];
      case 'date':
      case 'delivery_date':
        return date(DATE_FORMAT, strtotime($item->$column_name));
      case 'profile_id':
        $profile = $wpdb->get_results(
          "SELECT * FROM wp_shopper_profiles " .
          "WHERE wp_shopper_profiles.id = " . $item->$column_name
        ); 
        
        // Edit link
        $link = "<a href='?page=shopper-profiles&action=edit&profiles=" . $profile[0]->id . "' title='Modificare cumparator'>";
        
        // Sometimes there is no name but email for a Customer
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
      		"WHERE id = " . $item->$column_name
    		); 
    		return $status[0]->name;        
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to ID
  function column_id($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&orders=%s">Edit</a>','shopper-orders','edit',$item->id),        
    );    
    
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->id,
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
  function get_editables($item) {
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
  	$v = array();
  	global $wpdb;
  	$customers = $wpdb->get_results("SELECT * FROM wp_shopper_profiles");  
  	foreach ($customers as $c) {
  		// Check selected
  		$selected = '';
  		if (isset($item)) {
  			$current = $item->data['profile_id'];
  			if ($c->id == $current) {
  				$selected = 'selected';
  			}
  		}
  	
  		$v[] = array(
  			'value' => $c->id,
  			'title' => $c->name,
  			'selected' => $selected
  		); 
  	}
  	$ret[2]['type'] = 'select';
		$ret[2]['value'] = $v;

		
		// Status is a select box
		$ret[4]['type'] = 'select';
  	$v = array();
  	$s = $wpdb->get_results("SELECT * FROM wp_shopper_order_status");  
  	foreach ($s as $c) {
  		// Check selected
  		$selected = '';
  		if (isset($item)) {
  			$current = $item->data['status_id'];
  			if ($c->id == $current) {
  				$selected = 'selected';
  			}
  		}
  	
  		$v[] = array(
  			'value' => $c->id,
  			'title' => $c->name
  		); 
  	}
		$ret[4]['value'] = $v;
  	
  	
  	// Remove fields by special cases
  	switch ($item->page_title) {
  		case FORM_TITLE_ADD:
  			// When Add the order type will be 'phone'
  			$ret[0]['type'] = 'hidden';
  			$ret[0]['value'] = 0;
  			
  			// Date is automatically set to now
  			$ret[1]['type'] = 'hidden';
  			$ret[1]['value'] = date(DATE_MYSQL);
  			
  			// Customer has an "Add new" button / link attached
				$ret[2]['snippet'] = "<a class='add-new-h2' href='?page=shopper-profiles&action=edit'>Adaugare cumparator</a>";
		
				// Total is hidden
  			$ret[3]['type'] = 'hidden';
  			$ret[3]['value'] = 0;
		
  			// Status is PENDING
  			$ret[4]['type'] = 'hidden';
  			$ret[4]['value'] = 1;
  			break;
  		
  		case FORM_TITLE_MODIFY:
  			// Order type not modificable
  			$ret[0]['type'] = 'not editable';
  			
  			// Order date not modificable
  			$ret[1]['type'] = 'not editable';
  			
  			// Customer not modificable
  			$ret[2]['type'] = 'not editable';
  			$ret[2]['value'] = $item->profile_id;
  			
  			// Total is not modificable
  			$ret[3]['type'] = 'not editable';
  			$ret[3]['value'] = $item->total;
  			
  			break;
  	}
  	
  	// Show date format for Delivery Date
  	$ret[5]['snippet'] = '&nbsp;&nbsp;Format: 2012-08-23';
  	
  	
  	return $ret;
  }
  
  // Define detail tables
  function get_detail_tables($parent_id) {
  	$ret = array();
  	
  	// Order items
  	$params = array(
  		'parent_id' => $parent_id
  	);
  	$ret[] = array(
  		'title' => "Produse comandate",
  		'page' => 'order_items',
  		'table' => new OrderItems_Table($params)
  	);
  	
  	// Notes
  	$params = array(
  		'entry_id' => $parent_id,
  		'table_id' => 'orders'
  	);
  	$ret[] = array(
  		'title' => "Observatii",
  		'page' => 'notes',
  		'table' => new Notes_Table($params)
  	);
  	
  	
  	return $ret;
  }
  
  
  // Callback, after the save, just in case 
  // - $id: which order item was saved
  function after_save($id) {
  	
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
