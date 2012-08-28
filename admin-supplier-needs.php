<?php

// Display Supplier Needs in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class SupplierNeeds_Table extends WP_List_Table {

	function __construct($params) {
	  global $status, $page;
	  
	  // Checking for product id and variation id 
    $this->post_id = $_REQUEST['post_id'];
	  $this->variation_id = $_REQUEST['variation_id'];
	  
    parent::__construct(array(
      'singular'  => 'supplier need',
      'plural'  => 'supplier needs',
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
	    'supplier_id'=>__('Supplier'),
	    'product_id' => __('Produs'),
	    'variation_id'=>__('Variatie'),
	    'qty' => __('Cantitate'),
	    'date' => __('Data')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'qty':
        return $item->$column_name;
      case 'product_id':
      	$product = shopper_product($item->$column_name);
      	return $product->name;
      case 'variation_id':
      	$product = shopper_product($item->product_id);
      	return $product->variations[$item->$column_name-1]['name'];
      case 'supplier_id':
      	$supplier = $wpdb->get_results(
          "SELECT * FROM wp_shopper_suppliers " .
          "WHERE id = " . $item->$column_name
        ); 
        // Edit link
        return "<a href='?page=shopper-suppliers&action=edit&suppliers=" . $supplier[0]->id . "'>" . $supplier[0]->name . "</a>";
      case 'date':
      	return date('Y M d', strtotime($item->$column_name));
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Product Name
  function column_qty($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=shopper-supplier_needs&action=%s&supplier_needs=%s&parent_id=%s">Edit</a>','edit',$item->id, $this->parent_id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->qty,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  // Get editable columns
  // - $item is the current item loaded from db
  // - it is used to determine which selectbox value to display on edit
  function get_editables($item) {
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
  	
  	// Supplier, Product are select boxes
  	$suppliers = array();
  	global $wpdb;
  	$data = $wpdb->get_results("SELECT * FROM wp_shopper_suppliers");
  	foreach ($data as $d) {
  		
  		// Check selected
  		$selected = '';
  		if (isset($item->data['supplier_id'])) {
  			if ($d->id == $item->data['supplier_id']) {
  				$selected = 'selected';
  			}
  		}
  		
  		$suppliers[] = array(
    		'title' => $d->name,
    		'value' => $d->id,
    		'selected' => $selected
    	);
  	}
  	$ret['0']['type'] = 'select';
  	$ret['0']['value'] = $suppliers;
  	
  	
  	$all_products = shopper_products();
  	if ($all_products->have_posts()) {
    	foreach($all_products->posts as $post) {	
    		$p = shopper_product($post->ID);
    		
    		// Products are returned together vith their variations
    		foreach ($p->variations as $v) {
    			$product_name = $p->name;
    			
    			// Add variation name to product
    			if ($v['name'] != 'default') {
    				$product_name .= ' (' . $v['name'] . ')';
    			}
    			
    			// Get variation details
    			$snippet = " data-variation_id='" . $v['id'] . "'";
    			
    			// Check selected
    			$selected = '';
    			if (isset($this->post_id) && isset($this->variation_id)) {
    				if (($p->post_id == $this->post_id) && ($v['id'] == $this->variation_id)) {
    					$selected = 'selected';
    				}
    			}
    			if (isset($item->data['product_id']) && isset($item->data['variation_id'])) {
    				if (($p->post_id == $item->data['product_id']) && ($v['id'] == $item->data['variation_id'])) {
    					$selected = 'selected';
    				}
    			}
    			
    			$products[] = array(
    				'title' => $product_name,
    				'value' => $p->post_id,
    				'selected' => $selected,
    				'snippet' => $snippet 
    			);
    		}
  		}
  	}
  	$ret['1']['type'] = 'select';
  	$ret['1']['value'] = $products;
  	
  	$ret['2']['type'] = 'hidden';
  	
  	$ret['4']['type'] = 'hidden';
  	$ret['4']['value'] = date(DATE_MYSQL);
  	
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
      "SELECT * FROM wp_shopper_supplier_needs"      
    );
    
    function usort_reorder_supplier_needs($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder_supplier_needs');
    
    
        
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