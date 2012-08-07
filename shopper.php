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



// Product creation
//

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

  // The actual fields for data entry
  echo '<label for="shopper_product_name">';
       _e("Product Name", 'shopper_textdomain' );
  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
  echo '</label> ';
  echo '<input type="text" id="shopper_product_name" name="shopper_product_name" value="" size="25" />';
  echo '<br/>';
  
  echo '<label for="shopper_product_description">';
       _e("Short description", 'shopper_textdomain' );
  echo '</label> ';
  echo '<input type="text" id="shopper_product_description" name="shopper_product_description" value="" size="25" />';
  echo '<br/>';
  echo '<br/>';
  
  for ($i = 1; $i < 10; $i++) {
    
    $default = '';
    if ($i == 1) {
      $default = "default";
    } 
  
    echo '<label for="shopper_product_variation_name">';
    echo 'Variation #' . $i;    
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_name-'. $i .'" name="shopper_product_variation_name-'. $i .'" value="' . $default . '" size="25" />';
    
    echo '<label for="shopper_product_variation_price">';
    echo "&nbsp;&nbsp;";
    _e("Price", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_price-'. $i .'" name="shopper_product_variation_price-'. $i .'" value="" size="5" />';
    
    echo '<label for="shopper_product_variation_saleprice">';
    echo "&nbsp;&nbsp;";
    _e("Sale", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_saleprice-'. $i .'" name="shopper_product_variation_saleprice-'. $i .'" value="" size="5" />';
    
    echo '<label for="shopper_product_variation_delivery">';
    echo "&nbsp;&nbsp;";
    _e("Delivery", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_delivery-'. $i .'" name="shopper_product_variation_delivery-'. $i .'" value="" size="2" />';

    echo '<label for="shopper_product_variation_image">';
    echo "&nbsp;&nbsp;";
    _e("Image #", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_image-'. $i .'" name="shopper_product_variation_image-'. $i .'" value="" size="2" />';

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

  $mydata = $_POST['myplugin_new_field'];
  echo "mydata: $mydata";

  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
}







// Dashboard
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
