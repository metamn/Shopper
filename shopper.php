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


// How it works?
//
// - product info is metadata / custom fields added to posts and pages
// - shopping cart is persistent using cookies and database
// - all user actions (ajax) and clicks is registered into db to analyze behaviour




// Global variables
//
// the slug of the shopping cart page
define("CART", 'cos-cumparaturi');
define("CHECKOUT", 'confirmare-comanda');

// in how many hours a session expires
define("NEW_SESSION_HRS", 3);

// visitor types
define("PASSIVE", 1);
define("INTERACTIVE", 2);
define("CONTACTABLE", 3);






// Admin menu & Plugin init
//
function shopper_admin_menu() {  
  add_menu_page('Shopper', 'Shopper', 'delete_others_posts', 'session-manager-menu', 'shopper_main_page' );
  add_action( 'admin_init', 'shopper_tables' );  
} 
add_action('admin_menu', 'shopper_admin_menu');




// Include parts of the plugin
//
include_once(plugin_dir_path( __FILE__ ) . 'session.php');
include_once(plugin_dir_path( __FILE__ ) . 'cart.php');
include_once(plugin_dir_path( __FILE__ ) . 'product.php');
include_once(plugin_dir_path( __FILE__ ) . 'checkout.php');






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




// Database tables
// --------------------------------------------------------------------------------


// Create database tables
function shopper_tables() {
  global $wpdb;
  
  // Main table
  $table = $wpdb->prefix . "shopper_sessions";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      cookie VARCHAR(80) NOT NULL,      
      visits VARCHAR(1200),
      clicks VARCHAR(1200),
      cart VARCHAR(1200),
      UNIQUE KEY cookie (cookie)
  );";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

?>
