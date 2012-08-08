<?php
  /*
  Plugin Name: Shopper
  Plugin URI: https://github.com/metamn/Shopper
  Description: A Wordpress E-commerce Plugin
  Version: 0.0.1
  Author: metamn
  Author URI: https://github.com/metamn
  License: GPL2
  */



// Admin menu
function shopper_admin_menu() {  
  add_menu_page('Shopper', 'Shopper', 'delete_others_posts', 'session-manager-menu', 'shopper_main_page' );
  add_action( 'admin_init', 'shopper_tables' );  
} 
add_action('admin_menu', 'shopper_admin_menu');




// Shopping Cart
// --------------------------------------------------------------------------------

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
function shopper_add_to_cart($post_id) {
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
    
    $item['postid'] = strval( $_POST['id'] );
    $item['title'] = strval( $_POST['title'] );
    $item['qty'] = strval( $_POST['qty'] );
    $item['variation_name'] = strval( $_POST['variation-name'] );
    $item['variation_id'] = strval( $_POST['variation-id'] ) + 1;
    $item['price'] = strval( $_POST['price'] );
    
    // Save item
    
    // - check if this item is already added, then increase qty
    $item_exists = false;
    
    $items = $_SESSION['shopper'];
    if ($items) {
      $counter = 0;
      foreach ($items as $product => $value) {
        if ( ($item['postid'] == $value['postid']) && 
             ($item['variation_id'] == $value['variation_id']) &&
             ($item['price'] == $value['price']) ) {
             
             $_SESSION['shopper'][$counter]['qty'] += 1;
             $counter += 1; 
             
             $item_exists = true;            
             }       
        
      }    
    }
    
    if (!$item_exists) {
      $_SESSION['shopper'][] = $item;      
    }
    
    // Register action
    if (function_exists('manage_session')) {
      manage_session('cart-a-' . $id);
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




// Product
// --------------------------------------------------------------------------------
//
// - inserts a 'Product info' box in Posts and Pages editor
// - stores Product Name, Product Description and Product Variations in meta fields
// - retrieves complete Product information


// insert Product box into Posts and Page
add_action( 'add_meta_boxes', 'shopper_add_custom_box' );
// save Product when saving a Post
add_action( 'save_post', 'shopper_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function shopper_add_custom_box() {
  add_meta_box( 
      'shopper_sectionid',
      __( 'Product info', 'shopper_textdomain' ),
      'shopper_inner_custom_box',
      'post' 
  );
  add_meta_box(
      'shopper_sectionid',
      __( 'Product info', 'shopper_textdomain' ), 
      'shopper_inner_custom_box',
      'page'
  );
}

// Prints the box content
function shopper_inner_custom_box($post) {

  // Use nonce for verification
  wp_nonce_field(plugin_basename( __FILE__ ), 'shopper_noncename');
  
  // If this is an Edit action then read data from post meta
  $product = shopper_product($post->ID);
  
 
  // The actual fields for data entry
  echo '<label for="shopper_product_name">';
       _e("Product Name", 'shopper_textdomain' );
  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
  echo '</label> ';
  echo '<input type="text" id="shopper_product_name" name="shopper_product_name" value="' . $product->name . '" size="25" />';
  echo '<br/>';
  
  echo '<label for="shopper_product_description">';
       _e("Short description", 'shopper_textdomain' );
  echo '</label> ';
  echo '<input type="text" id="shopper_product_description" name="shopper_product_description" value="' . $product->description . '" size="25" />';
  echo '<br/>';
  echo '<br/>';
  
  for ($i = 1; $i < 10; $i++) {
    
    // If this is an Edit action then read data from post meta
    if (isset($product->variations[$i-1])) {
      $variation_name = $product->variations[$i-1]['name'];
      $variation_price = $product->variations[$i-1]['price'];
      $variation_saleprice = $product->variations[$i-1]['saleprice'];
      $variation_delivery = $product->variations[$i-1]['delivery'];
      $variation_image = $product->variations[$i-1]['image'];
    } else {
      $variation_name = '';
      $variation_price = '';
      $variation_saleprice = '';
      $variation_delivery = '';
      $variation_image = '';      
    }   
    
    if (($i == 1) && ($variation_name == '')) {
      $variation_name = "default";
    } 
  
    echo '<label for="shopper_product_variation_name">';
    echo 'Variation #' . $i;    
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_name-'. $i .'" name="shopper_product_variation_name-'. $i .'" value="' . $variation_name . '" size="25" />';
    
    echo '<label for="shopper_product_variation_price">';
    echo "&nbsp;&nbsp;";
    _e("Price", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_price-'. $i .'" name="shopper_product_variation_price-'. $i .'" value="' . $variation_price . '" size="5" />';
    
    echo '<label for="shopper_product_variation_saleprice">';
    echo "&nbsp;&nbsp;";
    _e("Sale", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_saleprice-'. $i .'" name="shopper_product_variation_saleprice-'. $i .'" value="' . $variation_saleprice . '" size="5" />';
    
    echo '<label for="shopper_product_variation_delivery">';
    echo "&nbsp;&nbsp;";
    _e("Delivery", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_delivery-'. $i .'" name="shopper_product_variation_delivery-'. $i .'" value="' . $variation_delivery . '" size="2" />';

    echo '<label for="shopper_product_variation_image">';
    echo "&nbsp;&nbsp;";
    _e("Image #", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_image-'. $i .'" name="shopper_product_variation_image-'. $i .'" value="' . $variation_image . '" size="2" />';

    echo '<br/>';
  }
  
  echo '<br/><br/>';
  echo 'Delivery:';
  echo '<br/><br/>';
  echo '  1: 1-2 zile';
  echo '<br/>';
  echo '  2: 2-4 zile';
  echo '<br/>';
  echo '  not set: 5-7 zile';
}


// When the post is saved, saves our custom data
function shopper_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['shopper_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $name = sanitize_text_field($_POST['shopper_product_name']);
  $description = sanitize_text_field($_POST['shopper_product_description']);
    
  $variations = array();
  for ($i = 1; $i < 10; $i++ ) {
    $variation_name = sanitize_text_field($_POST['shopper_product_variation_name-' . $i]);
    if (isset($variation_name) && ($variation_name != '')) {      
      $v = array();
      $v['id'] = $i;
      $v['name'] = $variation_name;
      $v['price'] = sanitize_text_field($_POST['shopper_product_variation_price-' . $i]);
      $v['saleprice'] = sanitize_text_field($_POST['shopper_product_variation_saleprice-' . $i]);
      $v['delivery'] = sanitize_text_field($_POST['shopper_product_variation_delivery-' . $i]);       
      $v['image'] = sanitize_text_field($_POST['shopper_product_variation_image-' . $i]);
      
      $variations[] = $v;
    }    
  }  
  
  // Save the data into post meta
  // TODO there is no flash message in WP so errors are invisible
  update_post_meta($post_id, 'product_name', $name);
  update_post_meta($post_id, 'product_description', $description);
  update_post_meta($post_id, 'product_variations', $variations);
}


// Get the product
// - this function is available in the theme
function shopper_product($post_id) {
  $product = new stdClass();
  
  $product->name = '';
  $product->description = '';
  $product->variations = '';
  
  $product->name = get_post_meta($post_id, 'product_name', true);
  $product->description = get_post_meta($post_id, 'product_description', true);
  $product->variations = get_post_meta($post_id, 'product_variations', true);
  
  return $product;
}




// Dashboard
// --------------------------------------------------------------------------------

function shopper_main_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  ?>
  
  <div id="shopper">
    <h1>Shopper</h1>    
  </div>
  
  <?php
}





// Create database tables
function shopper_tables() {
  /*
  global $wpdb;
  
  // Main table
  $table = $wpdb->prefix . "shopper";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      cookie VARCHAR(80) NOT NULL,      
      visits VARCHAR(1200),
      clicks VARCHAR(1200),
      type VARCHAR(80),
      PRIMARY KEY(id),
      UNIQUE KEY cookie (cookie)
  );";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
  */
}

?>
