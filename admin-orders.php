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
        
        // Edit link
        $link = "<a href='?page=shopper-customers&action=edit&profile=" . $profile[0]->id . "' title='Modificare cumparator'>";
        
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
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  
  // Add Edit to Status
  function column_status_id($item) {
    $actions = array(
        'edit'      => sprintf('<a href="?page=%s&action=%s&order=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),        
    );    
    
    global $wpdb;
    $status = $wpdb->get_results(
      "SELECT * FROM wp_shopper_order_status " .
      "WHERE id = " . $item->status_id
    ); 
    if (isset($status[0])) {
      $ret = $status[0]->name;        
    } else {
      $ret = $item->$column_name;
    }
    
    
    return sprintf('%1$s %2$s',
        /*$1%s*/ $ret,
        /*$3%s*/ $this->row_actions($actions)
    );
  }
  
  function get_sortable_columns() {
    $sortable_columns = array(
        'id'     => array('id', false),     //true means its already sorted
        'date'    => array('date', true),
        'customer'  => array('customer', false),
        'status_id'  => array('status_id', false)
    );
    return $sortable_columns;
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
