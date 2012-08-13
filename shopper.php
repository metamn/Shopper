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
  add_menu_page('Dashboard', 'Shopper', 'delete_others_posts', 'shopper-menu', 'shopper_main_page' );   
  add_submenu_page("shopper-menu", "Orders", "Orders", 'delete_others_posts', "shopper-orders", "shopper_orders_page");  
  add_submenu_page("shopper-menu", "Customers", "Customers", 'delete_others_posts', "shopper-customers", "shopper_customers_page");  
  add_submenu_page("shopper-menu", "Status & Emails", "Status & Emails", 'delete_others_posts', "shopper-status", "shopper_status_page");  
  add_submenu_page("shopper-menu", "Delivery", "Delivery", 'delete_others_posts', "shopper-delivery", "shopper_delivery_page");  
  add_submenu_page("shopper-menu", "Import", "Import", 'delete_others_posts', "shopper-import", "shopper_import_page");  
} 
add_action('admin_menu', 'shopper_admin_menu');




// Include parts of the plugin
//
include_once(plugin_dir_path( __FILE__ ) . 'session.php');
include_once(plugin_dir_path( __FILE__ ) . 'cart.php');
include_once(plugin_dir_path( __FILE__ ) . 'product.php');
include_once(plugin_dir_path( __FILE__ ) . 'checkout.php');

include_once(plugin_dir_path( __FILE__ ) . 'admin-orders.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-customers.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-status.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-delivery.php');







// Dashboard
// --------------------------------------------------------------------------------

function shopper_main_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  }   
}



// Orders
// --------------------------------------------------------------------------------

function shopper_orders_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  
  if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
    include(plugin_dir_path( __FILE__ ) . 'admin-orders-edit.php');
  } else { ?>  
    <div id="shopper-orders">
      <h1>Comenzi</h1>   
      
      <?php
        $orders = new Orders_Table();
        $orders->prepare_items();
      ?>
       
      <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">
        <?php
          $orders->search_box( 'Cautare', 'search_id' );
          $orders->display();
        ?>
      </form>  
    </div>
  
  <?php }
}



// Customers
// --------------------------------------------------------------------------------

function shopper_customers_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
    include(plugin_dir_path( __FILE__ ) . 'admin-customers-edit.php');
  } else { ?>
    <div id="shopper-customers">
      <h1>Cumparatori</h1>   
      
      <?php
        $customers = new Customers_Table();
        $customers->prepare_items();
      ?>
       
      <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">
        <?php
          $customers->search_box( 'Cautare', 'search_id' );
          $customers->display();
        ?>
      </form>  
    </div>
  
  <?php }
}



// Delivery
// --------------------------------------------------------------------------------

function shopper_delivery_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  
  if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
    include(plugin_dir_path( __FILE__ ) . 'admin-delivery-edit.php');
  } else { ?>  
    <div id="shopper-delivery">
      <h1>Livrare comanda</h1>   
      
      <?php
        $delivery = new Delivery_Table();
        $delivery->prepare_items();
        $delivery->display();
      ?>
  <?php }
}


// Status
// --------------------------------------------------------------------------------

function shopper_status_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  
  if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
    include(plugin_dir_path( __FILE__ ) . 'admin-status-edit.php');
  } else { ?>  
    <div id="shopper-status">
      <h1>Statut comanda si trimitere email</h1>   
      
      <?php
        $status = new Status_Table();
        $status->prepare_items();
        $status->display();
      ?>
  <?php }
}



// Import 
// --------------------------------------------------------------------------------

function shopper_import_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } ?> 
  
  
  <div id="shopper-import">
    <h1>Import</h1>   
    
<?php 
  include_once(plugin_dir_path( __FILE__ ) . 'admin-import.php');   
}


// Database tables
// --------------------------------------------------------------------------------


register_activation_hook(__FILE__,'shopper_tables');

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
      name VARCHAR(255),
      address VARCHAR(255),
      city VARCHAR(120),
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
      email_subject VARCHAR(255),
      email_body VARCHAR(1200),
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
