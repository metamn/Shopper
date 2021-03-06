<?php

// Display Ordered Items in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/
//



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class OrderItems_Table extends WP_List_Table {

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
      'singular'  => 'produs comandat',
      'plural'  => 'produse comandate',
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
	    'order_id' => __('Nr. <br/>Comanda'),
	    'product_name'=>__('Produs'),
	    'product_variation_name' => __('Variatie'),
	    'product_price' => __('Pret'),
	    'product_qty' => __('Cantitate')
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':
      case 'product_name':
      case 'product_variation_name':
      case 'product_price':
      case 'product_qty':
      case 'product_post_id':
      case 'product_variation_id':
        return $item->$column_name;
      case 'order_id':
      	global $wpdb;
      	$order = $wpdb->get_results(
      		"SELECT * FROM wp_shopper_orders WHERE id = " . $this->parent_id      
    		);
    		// Clicking on parent goes back to the parents edit page
    		return "<a href='?page=shopper-orders&action=edit&orders=" . $this->parent_id . "'>" . $order[0]->id . "</a>";
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Product Name
  function column_product_name($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=shopper-order_items&action=%s&order_items=%s&parent_id=%s">Edit</a>','edit',$item->id, $this->parent_id),        
    );    
    //Return the title contents
    return sprintf('%1$s %2$s',
        /*$1%s*/ $item->product_name,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  
  // Get editable columns
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
  	
  	// price, product_post_id, variation_id, variation_name is hidden
  	$ret['2']['type'] = 'hidden';
  	$ret['3']['type'] = 'hidden';
		$ret[] = array(
			'title' => 'Nr. produs',
			'id' => 'product_post_id',
			'type' => 'hidden');
		
		$ret[] = array(
			'title' => 'Nr. variatie',
			'id' => 'product_variation_id',
			'type' => 'hidden');	
			
  	
  	// Order_id is not editable
  	$ret[0]['type'] = 'not editable';
  	$ret[0]['value'] = $this->parent_id;
  	
  	// Quantity has an 'Add new order" button
  	$ret['4']['snippet'] = '<a id="import" class="add-new-h2" href="?page=shopper-supplier_needs&action=edit">Import more</a>';
  	
  	
  	// Product_name is a selectbox
  	$products = array();
  	$products[] = array(
    	'title' => 'Va rugam selectati un produs',
    	'value' => '',
    	'selected' => '',
    	'snippet' => '' 
    );
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
    			$snippet = " data-postid='" . $p->post_id . "' data-name='" . $p->name . "' data-variationname='" . $v['name'] . "'";
    			$snippet .= " data-variationid='" . $v['id'] . "' data-price='" . $v['price'] . "' data-stock='" . $v['stock'] . "'";
    			
    			// Check selected
  				$selected = '';
  				if (isset($item)) {
  					$current_post_id = $item->data['product_post_id'];
  					$current_variation_id = $item->data['product_variation_id'];
  					if (($p->post_id == $current_post_id) && ($v['id'] == $current_variation_id)) {
  						$selected = 'selected';
  						
  						// Update post_id, variation, and price
  						$ret['2']['value'] = $v['name'];
  						$ret['3']['value'] = $v['price'];
  						$ret['5']['value'] = $p->post_id;
  						$ret['6']['value'] = $v['id'];
  						
  						// Update Add new order / Import more
  						$ret['4']['snippet'] = '<a id="import" class="add-new-h2" href="?page=shopper-supplier_needs&action=edit&post_id=' . $p->post_id . '&variation_id=' . $v['id'] . '">Import more</a>';
  					}
  				}
    			
    			$products[] = array(
    				'title' => $product_name,
    				'value' => $p->name,
    				'selected' => $selected,
    				'snippet' => $snippet 
    			);
    		}
  		}
  	}
  	$ret['1']['type'] = 'select';
  	$ret['1']['value'] = $products;
  	$ret['1']['snippet'] = "&nbsp;&nbsp;	<span class='stock'>Stoc: </span>";
  	
  	
  	
  	
		
  	return $ret;
  }
  
  // Define detail tables
  function get_detail_tables($parent_id) {
  	$ret = array();
  	
  	return $ret;
  }
  
  
  // Callback, after the save, to update Order total
  // - $id: which order item was saved
  function after_save($id) {
  	global $wpdb;
  	
  	echo "id: $id";
  	
  	// Get the current order id
  	$item = $wpdb->get_results(
  		"SELECT * FROM wp_shopper_order_items WHERE id = " . $id
  	);
  	$order_id = $item[0]->order_id;
  	
  	// Get all products of an order and calculate the total
  	$total = 0;
  	$items = $wpdb->get_results(
  		"SELECT * FROM wp_shopper_order_items WHERE order_id = " . $order_id
  	);
  	foreach ($items as $i) {
  		$total += $i->product_qty * $i->product_price;
  	}
  	
  	// Update order total
  	$ret = $wpdb->query( 
    	$wpdb->prepare( 
      	"INSERT INTO wp_shopper_orders (id, total) VALUES (%d, %d) ON DUPLICATE KEY UPDATE total=VALUES(total)", 
      	array($order_id, $total)
      )
  	);
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
      "SELECT * FROM wp_shopper_order_items WHERE order_id = " . $this->parent_id      
    );
    
    function usort_reorder_order_items($a, $b){
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to this
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
      $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
      return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
    }
    usort($data, 'usort_reorder_order_items');
    
    
        
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
