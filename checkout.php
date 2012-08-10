<?php

 
// Checkout
// --------------------------------------------------------------------------------
//
//  - displays a form
//  - does checkout via AJAX



// Processing the order
function shopper_checkout_ajax() {
  $nonce = $_POST['nonce'];  
  if ( wp_verify_nonce( $nonce, 'checkout' ) ) {
  
    $email = strval( $_POST['email'] );
    if (!is_email($email)) {
      $ret = array(
        'success' => false,
        'message' => 'Adresa de email nu este valida.'
      );      
    } else {
      $phone = strval( $_POST['phone'] );
      $delivery = strval( $_POST['delivery'] );
      $discount = strval( $_POST['discount'] );
      
      // Get cart items
      $session = shopper_load_session();
      $items = $session->cart;
      
      // Save all
      $msg = shopper_db_save_order($email, $phone, $delivery, $discount, $items);
      
      
      $ret = array(
        'success' => true,
        'message' => $msg
      );  
    }  
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
add_action('wp_ajax_shopper_checkout_ajax', 'shopper_checkout_ajax');
add_action( 'wp_ajax_nopriv_shopper_checkout_ajax', 'shopper_checkout_ajax' );



// The checkout form
//
function shopper_checkout_form() {
  $session = shopper_load_session();
  $items = $session->cart;
  
  if ($items) {
    echo '<div class="add-to-cart">';
    echo '<form id="checkout" method="post" data-nonce="'. wp_create_nonce('checkout') .'">';
    echo '<input type="text" name="email" id="email" placeholder="Email">';
    echo '<input type="text" name="phone" id="phone" placeholder="Telefon">';
    echo '<input type="hidden" name="delivery" id="delivery" value="1">';
    echo '<input type="hidden" name="discount" id="discount" value="0">';
    echo "<input id='submit' type='submit' value='Finalizare comanda'>";
    echo '</form>';
    echo '<div class="message"></div>';
    echo '</div>';  
  } else {
    echo "Nu sunt cadouri in cosul Dvs.";
  }
}



// Saving the order
function shopper_db_save_order($email, $phone, $delivery, $discount, $items) {
  $msg = 'OK';
  
  global $wpdb;
  $wpdb->show_errors();
  
  // Create a new profile
  $session_id = shopper_db_get_session($_COOKIE['shopper'])->id;
  $profile_id = shopper_db_save_profile(array('email' => $email, 'phone' => $phone), $session_id);
  
  if ($profile_id) {
    $status = 1;
    
    // Save the order 
    $order = $wpdb->query( 
      $wpdb->prepare( 
      "
        INSERT INTO wp_shopper_orders
        (profile_id, delivery_id, status_id, discount_id)
        VALUES (%s, %s, %s, %s)
      ", 
      array($profile_id, $delivery, $status, $discount)
      )
    );  
    
    // Save order items
    $order_id = $wpdb->insert_id;
    if ($order_id) {
      if ($items) {
        $order_total = 0;
        foreach ($items as $item) {
         $order_item = $wpdb->query( 
          $wpdb->prepare( 
            "
              INSERT INTO wp_shopper_order_items
              (order_id, product_post_id, product_name, product_qty, product_variation_name, product_variation_id, product_price)
              VALUES (%s, %s, %s, %s, %s, %s, %s)
            ", 
            array($order_id, $item['post_id'], $item['title'], $item['qty'], $item['variation_name'], $item['variation_id'], $item['price'])
            )
         );
         
         // Calculate order total 
         $order_total += $item['qty'] * $item['price'];
        }
      }
    }
    
    // Add total to orders    
    $order_update = $wpdb->query( 
      $wpdb->prepare("UPDATE wp_shopper_orders SET total = %s WHERE id = %s", 
      $order_total, $order_id)
    );        
  } else {
    $msg = "Eroare creare profil utilizator.";  
  }
  
  return $msg;
}


// Creating a new profile
function shopper_db_save_profile($args, $session_id) {
  global $wpdb;
  $wpdb->show_errors();
  
  $wpdb->query( 
    $wpdb->prepare( 
    "
      INSERT INTO wp_shopper_profiles
      (session_id, email, phone)
      VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE phone=VALUES(phone), session_id=VALUES(session_id)
    ", 
    array($session_id, $args['email'], $args['phone'])
    )
  );
  
  $id = $wpdb->insert_id;
  if ($id == 0) {
    // it was an update. get the profile id in a different way;
    $ret = $wpdb->get_results( 
	    "SELECT * FROM `wp_shopper_profiles` WHERE `email`='" . $args['email'] ."'"
    );
    return $ret[0]->id;     
  } else {
    return $id;
  }
}

/*
// Update session with profile
function shopper_db_add_profile_to_session($profile_id) {
  global $wpdb;
  $wpdb->show_errors();
  
  
  return $wpdb->query( 
    $wpdb->prepare("UPDATE wp_shopper_sessions SET profile_id = %s WHERE cookie = %s", 
    $profile_id, $_COOKIE['shopper']) 
  );
    
}
*/

?>


