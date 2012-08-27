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

// Date formatting
define("DATE_FORMAT", "D, d M Y");

// Order types
$ORDER_TYPES = array("Online", "Phone");

// form data
define("FORM_TITLE_ADD", "Adaugare");
define("FORM_TITLE_MODIFY", "Modificare");
define("FORM_SUBMIT_ADD", "Adaugare");
define("FORM_SUBMIT_MODIFY", "Salvare modificari");

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
  
  // This makes the first submenu to be 'Overview' instead of the default 'Shopper'
  // - http://wordpress.stackexchange.com/questions/26499/naming-admin-menus-and-submenus
  add_submenu_page("shopper-menu", "Overview", "Overview", 'delete_others_posts', "shopper-menu", "shopper_main_page");
  
  // Separator, hack
  add_submenu_page("shopper-menu", "________________", "________________", 'delete_others_posts', "shopper-separator-menu", "shopper_separator_page");  
  
  add_submenu_page("shopper-menu", "Orders", "Orders", 'delete_others_posts', "shopper-orders", "shopper_orders_page");  
  add_submenu_page("shopper-menu", "Customers", "Customers", 'delete_others_posts', "shopper-profiles", "shopper_profiles_page");  
  add_submenu_page("shopper-menu", "Status & Emails", "Status & Emails", 'delete_others_posts', "shopper-status", "shopper_status_page");  
  add_submenu_page("shopper-menu", "Delivery", "Delivery", 'delete_others_posts', "shopper-delivery", "shopper_delivery_page"); 
  
  // Separator, hack
  add_submenu_page("shopper-menu", "________________", "________________", 'delete_others_posts', "shopper-separator-menu", "shopper_separator_page");
  
  add_submenu_page("shopper-menu", "Suppliers", "Suppliers", 'delete_others_posts', "shopper-suppliers", "shopper_suppliers_page"); 
  add_submenu_page("shopper-menu", "Supplier needs", "Supplier needs", 'delete_others_posts', "shopper-supplier_needs", "shopper_supplier_needs_page"); 
  
  
  // Separator, hack
  add_submenu_page("shopper-menu", "________________", "________________", 'delete_others_posts', "shopper-separator-menu", "shopper_separator_page");
  
  add_submenu_page("shopper-menu", "Addresses", "Addresses", 'delete_others_posts', "shopper-addresses", "shopper_addresses_page"); 
  add_submenu_page("shopper-menu", "Notes", "Notes", 'delete_others_posts', "shopper-notes", "shopper_notes_page"); 
  add_submenu_page("shopper-menu", "Order Items", "Order Items", 'delete_others_posts', "shopper-order_items", "shopper_order_items_page"); 
  
  // Separator, hack
  add_submenu_page("shopper-menu", "________________", "________________", 'delete_others_posts', "shopper-separator-menu", "shopper_separator_page");
  
  add_submenu_page("shopper-menu", "Import", "Import", 'delete_others_posts', "shopper-import", "shopper_import_page");  
  
  
} 
add_action('admin_menu', 'shopper_admin_menu');




// Include parts of the plugin

// General admin functions
// - it is like a framework (Rails) to display, edit, add, manage tables
include_once(plugin_dir_path( __FILE__ ) . 'admin-framework.php');

include_once(plugin_dir_path( __FILE__ ) . 'session.php');
include_once(plugin_dir_path( __FILE__ ) . 'cart.php');
include_once(plugin_dir_path( __FILE__ ) . 'product.php');
include_once(plugin_dir_path( __FILE__ ) . 'checkout.php');

include_once(plugin_dir_path( __FILE__ ) . 'admin-orders.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-profiles.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-status.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-delivery.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-addresses.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-notes.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-order-items.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-suppliers.php');
include_once(plugin_dir_path( __FILE__ ) . 'admin-supplier-needs.php');




// include admin.css, admin.js 
function shopper_admin_scripts() {
	wp_register_style( 'shopper-admin', plugins_url('admin.css', __FILE__) );
	wp_enqueue_style( 'shopper-admin' );
	wp_enqueue_script('shopper', plugins_url('admin.js', __FILE__), array('jquery'));
	wp_localize_script( 'shopper', 'shopper', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}  
add_action('admin_print_styles', 'shopper_admin_scripts');



// Dashboard
// --------------------------------------------------------------------------------

function shopper_main_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } ?>
  
  <h1>Overview</h1>
  
  <?php  
}

// This is an empty page corresponding to a separator in the Shopper menu
// - this is a hack to insert separators in the plugin menu
function shopper_separator_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  }
}



// Orders
// --------------------------------------------------------------------------------

function shopper_orders_page() {
  shopper_admin_display_submenu_page("Comenzi", "orders", new Orders_Table(), true, true, true);
}



// Customers
// --------------------------------------------------------------------------------

function shopper_profiles_page() {
  shopper_admin_display_submenu_page("Cumparatori", "profiles", new Customers_Table(), true, true, true);
}


// Delivery
// --------------------------------------------------------------------------------

function shopper_delivery_page() {
  shopper_admin_display_submenu_page("Livrare comanda", "delivery", new Delivery_Table(), true, false, true);
}



// Status
// --------------------------------------------------------------------------------

function shopper_status_page() {
  shopper_admin_display_submenu_page("Statut comanda si trimitere email", "status", new Status_Table(), true, false, true);
}


// Addresses
// --------------------------------------------------------------------------------

function shopper_addresses_page() {
	shopper_admin_display_submenu_page("Adrese", "addresses", new Addresses_Table(), true, true, true);
}

// Notes
// --------------------------------------------------------------------------------

function shopper_notes_page() {
	shopper_admin_display_submenu_page("Observatii", "notes", new Notes_Table(), true, true, true);
}

// Order Items
// --------------------------------------------------------------------------------

function shopper_order_items_page() {
	shopper_admin_display_submenu_page("Produse comandate", "order_items", new OrderItems_Table(), true, true, true);
}

// Suppliers
// --------------------------------------------------------------------------------

function shopper_suppliers_page() {
	shopper_admin_display_submenu_page("Suppliers", "suppliers", new Suppliers_Table(), true, true, true);
}

// Supplier Needs
// --------------------------------------------------------------------------------

function shopper_supplier_needs_page() {
	shopper_admin_display_submenu_page("Supplier Needs", "supplier_needs", new SupplierNeeds_Table(), true, true, true);
}




// Import 
// --------------------------------------------------------------------------------

function shopper_import_page() {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } ?> 
    
  <div id="shopper-import">
    <h1>Import</h1>   
    
    <form action="admin.php?page=shopper-import" method="post">
      <select id="import" name="import">
        <option value="posts">Posts</option>
        <option value="comments">Comments</option>
        <option value="orders">Orders & Customers</option>
      </select>
      <p class="submit"><input type="submit" value="Import" class="button-primary" id="submit" name="submit"></p>
    </form>
  </div>
  <?php include_once(plugin_dir_path( __FILE__ ) . 'admin-import.php'); ?>
    
<?php   
}





// Database tables
// --------------------------------------------------------------------------------


register_activation_hook(__FILE__,'shopper_tables');

// Create database tables
function shopper_tables() {
  global $wpdb;
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  
  
  // Profiles  
  // - stores customer and session profiles
  // - id: the unique identifier
  // - name, email, phone: they uniquely identify a person
  $table = $wpdb->prefix . "shopper_profiles";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(255),
      email VARCHAR(120) NOT NULL,      
      phone VARCHAR(20),
      date TIMESTAMP,
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
  
  
  // Addresses
  // - stores delivery, billing, etc addresses
  // - profile_id: a profile can have many addresses
  $table = $wpdb->prefix . "shopper_addresses";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      profile_id INT(9) NOT NULL,
      address VARCHAR(255),
      city VARCHAR(100),
      judet VARCHAR(100),
      date TIMESTAMP,
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
  
  
  // Common, shared tables
  //
  
  // Notes
  // - stores any additional information to any other database tables
  // - it is like comments in a blog to posts, pages
  // - table_id: a profile, an order, etc can have (many) notes
  // - entry_id: to which entry in a table this note belongs
  $table = $wpdb->prefix . "shopper_notes";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      table_id VARCHAR(100) NOT NULL,
      entry_id INT(9) NOT NULL,
      body VARCHAR(1200) NOT NULL,
      date TIMESTAMP,
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
  
  
  // Changes / History
  // - registers who, which internal user modified what
  // - table_id: which table was modified
  // - entry_id: which entry was modified
  // - user_id: who did the modification
  // - before: the original data
  // - after: the modified data
  $table = $wpdb->prefix . "shopper_changes";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      table_id VARCHAR(100) NOT NULL,
      entry_id INT(9) NOT NULL,
      user_id INT(9) NOT NULL,
      before VARCHAR(1200) NOT NULL,
      after VARCHAR(1200) NOT NULL,
      date TIMESTAMP,
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
  
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
  
  
  // Orders
  $table = $wpdb->prefix . "shopper_orders";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      old_id INT(9) NOT NULL,
      profile_id INT(9) NOT NULL,
      delivery_id INT(9),
      delivery VARCHAR(20), 
      status_id INT(9),
      discount_id INT(9),      
      date TIMESTAMP,
      total VARCHAR(32),
      grand_total VARCHAR(32),
      type INT(9) NOT NULL,
      delivery_date DATE,
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
  
  
  // Suppliers
  $table = $wpdb->prefix . "shopper_suppliers";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(32),
      description VARCHAR(255),
      contact VARCHAR(255),
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
  
  // Supplier Needs
  $table = $wpdb->prefix . "shopper_supplier_needs";
  $sql = "CREATE TABLE $table (
      id INT(9) NOT NULL AUTO_INCREMENT,
      supplier_id INT(9),
      product_id VARCHAR(255),
      variation_id VARCHAR(255),
      qty INT(9),
      date TIMESTAMP,
      PRIMARY KEY (id)
  );";  
  dbDelta($sql);
}

?>
