<?php

 
// Shopping Cart
// --------------------------------------------------------------------------------
//
//  - displays a form
//  - add items via AJAX
//  - displays cart contents, the short way
//  - saves shopping history into cookie / db



// Init sessions
// - it is used to access $_SESSION in this plugin 
if ( !session_id() ) 
  add_action( 'init', 'session_start' );
  

// include shopper.js and set up AJAX
function shopper_scripts_method() {
	wp_enqueue_script('shopper', plugins_url('shopper.js', __FILE__), array('jquery'));
	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
  wp_localize_script( 'shopper', 'shopper', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}  
add_action('wp_enqueue_scripts', 'shopper_scripts_method');


// Display the add to cart form
function shopper_add_to_cart_form($post_id) {
  $product = shopper_product($post_id);
  if (isset($product)) {
  
    echo '<div class="add-to-cart">';
    echo '<form id="add-to-cart" method="post" data-id="'.$post_id.'" data-title="'.$product->name.'" data-nonce="'. wp_create_nonce('add-to-cart') .'">';
    
    echo '<select id="product-variations" name="option">';
    foreach ($product->variations as $p) {
      echo "<option value=" . $p['id'] . ">" . $p['name'] . " &mdash; " . $p['price'] . " RON</option>";
    } 
    echo "</select>";
    
    // cannot get these variables in the <option>, so this is a workaround   
    foreach ($product->variations as $p) {
      echo "<input type='hidden' id='variation' name='variation' data-id='" . $p['id'] . "' data-name='" . $p['name'] . "' data-price='" . $p['price'] . "'>";
    } 
    
    echo "<input id='submit' type='submit' value='Adauga la cos'>";    
    
    echo '</form>';    
    echo '<div class="message"></div>';  
    echo '</div>';
  } else {
    return "Produs invalid.";
  }  
}


// Add to cart (AJAX)
function shopper_add_to_cart_ajax() {
  $nonce = $_POST['nonce'];  
  if ( wp_verify_nonce( $nonce, 'add-to-cart' ) ) {
    
    // Create new cart item
    $item = array();
    
    $item['post_id'] = strval( $_POST['id'] );
    $item['title'] = strval( $_POST['title'] );
    $item['qty'] = strval( $_POST['qty'] );
    $item['variation_name'] = strval( $_POST['variation-name'] );
    $item['variation_id'] = strval( $_POST['variation-id'] );
    $item['price'] = strval( $_POST['price'] );
    
    // Save item
    
    $session = shopper_load_session();
    $items = $session->cart;
    
    // - check if this item is already added, then increase qty
    $item_exists = false;   
    
    if ($items) {
      $counter = 0;
      foreach ($items as $i) {
        if ( ($item['post_id'] == $i['post_id']) && 
             ($item['variation_id'] == $i['variation_id']) &&
             ($item['price'] == $i['price']) ) {
             
             $items[$counter]['qty'] += 1;
             $item_exists = true;            
             }
        $counter += 1;
      }         
    }
    
    if (!$item_exists) {
      $items[] = $item;      
    }
    
    
    // Register action
    if (function_exists('shopper_manage_session')) {
      shopper_manage_session($items);
    }
    
    
    $ret = array(
      'success' => true,
      'message' => 'Ok'
    );  
  
  } else {
    $ret = array(
      'success' => false,
      'message' => 'Nonce error'
    );
  }
    
  $response = json_encode($ret);
  header( "Content-Type: application/json" );
  echo $response;
  exit;
}
add_action('wp_ajax_shopper_add_to_cart_ajax', 'shopper_add_to_cart_ajax');
add_action( 'wp_ajax_nopriv_shopper_add_to_cart_ajax', 'shopper_add_to_cart_ajax' );




// Display cart contents
// - 'short' format is '2 products 200 RON'
function shopper_display_cart($format) {  
  $session = shopper_load_session();
  $items = $session->cart;
  
  if ($items) {
    $price = 0;
    $count = 0;
    foreach ($items as $item) {
      $price += $item['qty'] * $item['price'];    
      $count += $item['qty'];
    }
    $msg = $count . " cadouri, " . $price . " RON";
  } else {
    $msg = "Cosul Dvs. este gol";  
  }
  
  return $msg;
}


?>
