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
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  
  // Sessions
  $table = $wpdb->prefix . "shopper_sessions";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      cookie VARCHAR(80) NOT NULL,      
      visits VARCHAR(1200),
      clicks VARCHAR(1200),
      cart VARCHAR(1200),
      PRIMARY KEY (id),
      UNIQUE KEY cookie (cookie)
  );";  
  dbDelta($sql);
  
  
  // Profiles  
  // - a session can have many profiles associated, ie a visitor can buy from multiple emails
  $table = $wpdb->prefix . "shopper_profiles";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      session_id INT(9),
      email VARCHAR(120),
      phone VARCHAR(20),
      PRIMARY KEY (id),
      UNIQUE KEY email (email)
  );";  
  dbDelta($sql);
  
  
  // Orders
  $table = $wpdb->prefix . "shopper_orders";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      profile_id INT(9) NOT NULL,
      delivery_id INT(9),
      status_id INT(9),
      discount_id INT(9),      
      date TIMESTAMP,
      total VARCHAR(32),
      grand_total VARCHAR(32),
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
 
 
  // Ordered items
  $table = $wpdb->prefix . "shopper_order_items";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      order_id INT(9) NOT NULL,
      product_post_id VARCHAR(32),
      product_name VARCHAR(255),
      product_qty VARCHAR(32),
      product_variation_name VARCHAR(255),
      product_variation_id VARCHAR(32),
      product_price VARCHAR(255),
      PRIMARY KEY (id)
  );";  
  dbDelta($sql); 
  
  
  // Order Status
  $table = $wpdb->prefix . "shopper_order_status";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(32),
      PRIMARY KEY (id)
  );";  
  dbDelta($sql); 
  
  
  // Order Delivery
  $table = $wpdb->prefix . "shopper_order_delivery";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(32),
      description VARCHAR(255),
      price VARCHAR(32),
      duration VARCHAR(32),
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
}

?>
