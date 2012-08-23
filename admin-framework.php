<?php

// A mini framework to manage (add, edit, display) tables in Admin
// --------------------------------------------------------------------------------
//


// Displays a submenu page with a table showing data
// - used in the main plugin file
// - Delas also with adding, editing records
//
// - title: the title of the page
// - page: the slug/name of the page, like: orders, customers, delivery etc...
// - table: on which data to operate (new Orders_Table())
// - editables: which fields to edit
// - addable: Add new item?
// - searchable: if the table is searchable
// - editable: boolean, if the page will handle forms and updates
//
// Example:
// - shopper_admin_display_submenu_page("Comenzi", "orders", new Orders_Table(), true, true)
function shopper_admin_display_submenu_page($title, $page, $table, $editables, $addable, $searchable, $editable) {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  // Set up variables  
  
  // which url handles the requests
  $form_url = "shopper-$page";
  
  // the nonce
  $nonce = "admin-$page-edit";
  
  echo "<div class='wrap'>";  
  if (($_POST) && ($_POST['action'] == 'submit-form')) {
    echo shopper_admin_form_save($_POST, $page, $nonce);
  }
  // Check if the data is editable
  if ($editable) {
    if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') ) {
      // Edit
      
      // Get which item to edit or create an empty one
      $item = shopper_admin_form_header($_REQUEST[$page], $page);
      echo "<h2>" . $item->page_title . " " . $title . "</h2>";
      
      print_r($item);
      
      // Display the form 
      echo shopper_admin_form_body($item, $editables, $nonce);
      
    } else {
      // Display the data in a table
      
      // Page title
      $t = '';
      $link = "?page=$form_url&action=edit";
      if ($addable) {
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
}


// Save the form
//
// post: the $_POST
// table_name: the SQL table
// nonce: the nonce
function shopper_admin_form_save($post, $table_name, $nonce) {
	if (wp_verify_nonce( $post['nonce'], $nonce )) {
      
      global $wpdb;
      $wpdb->show_errors();
      $table = $wpdb->prefix . "shopper_" . $table_name;
      
      $ret = $wpdb->query( 
        $wpdb->prepare( 
        "
          INSERT INTO $table
          (id, name, email, phone)
          VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE " .
          "name=VALUES(name), email=VALUES(email), phone=VALUES(phone)"
        , 
        array($post['id'], $post['name'], $post['email'], $post['phone'])
        )
      );
      
      if ($ret != false) {
        echo "Succes!";        
      } else {
      	echo "Error!";
      }
  } else {
  	echo "Nonce error";
  }
}



// Display a form to edit / add new data
// - it is like a partial for simpler forms
//
// - item: the existing or new record to edit / add
// - editables: an array of fields to show
// - nonce: the nonce string to secure the form
function shopper_admin_form_body($item, $editables, $nonce) { ?>    
  <form action="" method="post">
    <table class="form-table">
      <tbody>
        <?php foreach ($editables as $field) { ?>
          <tr>
            <th><label><?php echo $field['title'] ?></label></th>
            <td>
              <input type="text" class="regular-text" value="<?php echo $item->data[$field['id']] ?>" id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>">
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <input type="hidden" value="<?php echo $item->data->id ?>" id="id" name="id">
    <input type="hidden" value="<?php echo wp_create_nonce($nonce) ?>" id="nonce" name="nonce">
    <input type="hidden" id="action" name="action" value="submit-form">
    <p class="submit"><input type="submit" value="<?php echo $item->button_title ?>" class="button-primary" id="submit" name="submit"></p>
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

	// Covert data into an array
	$item->data = (array) $item->data;
  return $item;
}
