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
    echo "<input id='submit' type='submit' value='Finalizare comanda'>";
    echo '</form>';
    echo '<div class="message"></div>';
    echo '</div>';  
  } else {
    echo "Nu sunt cadouri in cosul Dvs.";
  }
}


?>


