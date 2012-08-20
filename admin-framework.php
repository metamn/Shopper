<?php

// A mini framework to manage (add, edit, display) tables in Admin
// --------------------------------------------------------------------------------
//



// Display a form to edit / add new data
// - it is like a partial for simpler forms
//
// - id: the row to edit / add
// - fields: an array of fields to show
// - nonce: the nonce string to secure the form
// - submit: the submit text (for add or modify)
function shopper_admin_form_body($id, $fields, $nonce, $submit) { ?>    
  <form action="" method="post">
    <table class="form-table">
      <tbody>
        <?php foreach ($fields as $field) { ?>
          <tr>
            <th><label><?php echo $field['title'] ?></label></th>
            <td>
              <input type="text" class="regular-text" value="<?php echo $field['value'] ?>" id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>">
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <input type="hidden" value="<?php echo $id ?>" id="id" name="id">
    <input type="hidden" value="<?php echo wp_create_nonce($nonce) ?>" id="nonce" name="nonce">
    <p class="submit"><input type="submit" value="<?php echo $submit ?>" class="button-primary" id="submit" name="submit"></p>
  </form>
<?php }



// Decide if this is an edit or add new action
// - request: the query param to work with, ie $_REQUEST['status']
// - table_name: the SQL name of the table
//
// Returns
// - item: the row as a class, which is either empty (for add new),
//   or preloaded from db with data to edit
// - page_title: the "Add" or "Modify" text 
// - button_title: the "update" or "add" text
function shopper_admin_form_header($request, $table_name) {
  $id = $request;
  
  global $wpdb;
  $table = $wpdb->prefix . "shopper_" . $table_name;
  
  // the return object  
  $item = new stdClass();
  
  if (isset($id)) {
    // Edit
    $item->page_title = FORM_TITLE_MODIFY;
    $item->button_title = FORM_SUBMIT_MODIFY;    
    
    // Get the row to edit
    $row = $wpdb->get_results(
      "SELECT * FROM $table " .
      "WHERE id='" . $id . "'"     
    );    
    $item->data = $row[0];
    
  } else {
    // Add new
    $item->page_title = FORM_TITLE_ADD;
    $item->button_title = FORM_SUBMIT_ADD;    
    
    // Insert a new empty row        
    $wpdb->query( 
      $wpdb->prepare( 
        "INSERT INTO $table VALUES ()"
      )
    );
    $item->data->id = $wpdb->insert_id;
  }

  return $item;
}



// Displays a submenu page with a table showing data
// - used in the main plugin file
//
// - title: the title of the page
// - page: the slug/name of the page, like: orders, customers, delivery etc...
// - table: on which data to operate (new Orders_Table())
// - add: Add new item?
// - searchable: if the table is searchable
// - editable: boolean, if the page will handle forms and updates
//
// Example:
// - shopper_admin_display_submenu_page("Comenzi", "orders", new Orders_Table(), true, true)
function shopper_admin_display_submenu_page($title, $page, $table, $add, $searchable, $editable) {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  // Set up variables  
  // which file handles edit, update
  $edit_file = "admin-$page-edit.php";
  // which url handles the requests
  $form_url = "shopper-$page";
  
  
  echo "<div class='wrap'>";  
  if ($_POST) {
    print_r($_POST);
  }
  // Check if the data is editable
  if ($editable) {
    if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
      // Edit
      
      // Get which item to edit or create an empty one
      $item = shopper_admin_form_header($_REQUEST['profile'], "profiles");
      echo "<h2>" . $item->page_title . " " . $title . "</h2>";
      
      // Which fields to show in the edit form
      $fields = array();
      $fields[] = array(
        "title" => "Nume",
        "value" => $item->data->name,
        "id" => "name" 
      );
      $fields[] = array(
        "title" => "Email",
        "value" => $item->data->email,
        "id" => "email" 
      );  
      $fields[] = array(
        "title" => "Telefon",
        "value" => $item->data->phone,
        "id" => "phone" 
      );        
      
      // Display the form 
      echo shopper_admin_form_body($item->data->id, $fields, 'admin-customers-edit', $item->button_title);
      
    } else {
      // Display
      
      // Page title
      $t = '';
      $link = "?page=$form_url&action=edit";
      if ($add) {
        $t = '<a class="add-new-h2" href="' . $link . '">Adaugare</a>';
      }    
      echo "<h2>$title $t</h2>";
      
      // The table
      $table->prepare_items();
      
      if ($searchable) {
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="' . $form_url . '">';
        $table->search_box( 'Cautare', 'search_id' );
        $table->display();
        echo '</form>';      
      } else {
        $table->display();
      }
    }
  }  
  echo "</div>";
  
  print_r($table->get_columns());
}
