<?php

// A mini framework to manage (add, edit, display) tables in Admin
// --------------------------------------------------------------------------------
//

// Displays a submenu page with a table showing data
// - used in the main plugin file
//
// - title: the title of the page
// - page: the slug/name of the page, like: orders, customers, delivery etc...
// - table: on which data to operate (new Orders_Table())
// - searchable: if the table is searchable
// - editable: boolean, if the page will handle forms and updates
//
// Example:
// - shopper_admin_display_submenu_page("Comenzi", "orders", new Orders_Table(), true, true)
function shopper_admin_display_submenu_page($title, $page, $table, $searchable, $editable) {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  // Set up variables  
  // which file handles edit, update
  $edit_file = "admin-$page-edit.php";
  
  // Check if the data is editable
  if ($editable) {
    if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
      include(plugin_dir_path( __FILE__ ) . $edit_file);
    } else {
    
      echo "<h1>$title</h1>";
      $table->prepare_items();
      
      if ($searchable) {
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="shopper-orders">';
        $table->search_box( 'Cautare', 'search_id' );
        $table->display();
        echo '</form>';      
      } else {
        $table->display();
      }
    }
  }
}
