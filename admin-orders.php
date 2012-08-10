<?php

// Display orders in Admin
// --------------------------------------------------------------------------------
//
// - tutorial: http://wordpress.org/extend/plugins/custom-list-table-example/



if(!class_exists('WP_List_Table')) :
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;


class Orders_Table extends WP_List_Table {

	function __construct() {
	  global $status, $page;
    
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
	    'date'=>__('Data'),
	    'customer' => __('Cumparator'),
	    'products' => __('Produse'),
	    'total'=>__('Total'),	    
	    'delivery_id' => __('Livrare'),
	    'grand_total'=>__('Final'),
	    'status_id' => __('Statut'),
    );
  }
  
  
  // Return what data to display for each column
  //
  function column_default($item, $column_name){
    global $wpdb;
    
    switch($column_name){
      case 'id':      
      case 'total':
      case 'delivery_id':
      case 'status_id':
      case 'grand_total':
        return $item->$column_name;
      case 'date':
        return date('Y M d', strtotime($item->$column_name));
      case 'customer':
        $profile = $wpdb->get_results(
          "SELECT * FROM wp_shopper_profiles " .
          "WHERE wp_shopper_profiles.id = " . $item->profile_id
        ); 
        return $profile[0]->email . "<br/>Tel: " . $profile[0]->phone;
      case 'products':
        $products = $wpdb->get_results(
          "SELECT * FROM wp_shopper_order_items " .
          "WHERE wp_shopper_order_items.order_id = " . $item->id
        ); 
        $ret = "";
        foreach ($products as $product) {
          $ret .= $product->product_name;
          if ($product->product_variation_name != 'default') {
            $ret .= ' (' . $product->product_variation_name . ')';
          }
          $q = $product->product_qty;
          if ($q > 1) {
            $ret .= ' &mdash; ' . $product->product_qty . ' x ' . $product->product_price . 'RON';
          } else {
            $ret .= ' &mdash; ' . $product->product_price . 'RON';
          }          
          $ret .= '<br/><br/>';
        }
        return $ret;
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
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
    
    global $wpdb;    
    $data = $wpdb->get_results(
      "SELECT * FROM wp_shopper_orders"     
    );
    
        
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
