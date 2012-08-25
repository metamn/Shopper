<?php

// A mini framework to manage (add, edit, display) tables in Admin
// --------------------------------------------------------------------------------
//


// Displays a submenu page with a table showing data
// - used in the main plugin file
// - Deals also with adding, editing records
//
// - title: the title of the page
// - page: the slug/name of the page, and the table SQL name, like: orders, customers, delivery etc...
// - table: on which data to operate (new Orders_Table())
// - addable: Add new item?
// - searchable: if the table is searchable
// - editable: boolean, if the page will handle forms and updates
//
// Example:
// - shopper_admin_display_submenu_page("Comenzi", "orders", new Orders_Table(), true, true)
function shopper_admin_display_submenu_page($title, $page, $table, $addable, $searchable, $editable) {
  if (!current_user_can('delete_others_posts'))  {
    wp_die( 'Nu aveti drepturi suficiente de acces.' );
  } 
  
  // Set up variables  
  
  // which url handles the requests
  $form_url = "shopper-$page";
  
  // the nonce
  $nonce = "admin-$page-edit";
  
  
  if (($_POST) && ($_POST['action'] == 'submit-form')) {
    // ------------------------------------------------
    // Save
    // ------------------------------------------------
    
    // Display the main div
    echo shopper_admin_div($table->parent_id, $form_url, 'save');
    
    echo shopper_admin_form_save($_POST, $table->get_editables(), $page, $nonce);
  }
  // Check if the data is editable
  if ($editable) {
    if ( (isset($_REQUEST['action'])) && ($_REQUEST['action'] == 'edit') && ($_REQUEST['page'] == $form_url)) {
      // ------------------------------------------------
    	// Edit
    	// ------------------------------------------------
    	
    	// Display the main div
    	echo shopper_admin_div($table->parent_id, $form_url, 'edit');
      
      // Get which item to edit or create an empty one
      $item = shopper_admin_form_setup($_REQUEST[$page], $page);
      
      // Page title
      echo "<h2>" . $item->page_title . " " . $title . "</h2>";
      
      // Display the form 
      echo shopper_admin_form_body($item, $table, $nonce);
      
      // Display detail tables
      foreach ($table->get_detail_tables($item->data['id']) as $detail) {
      	shopper_admin_display_detail($detail);
      }
      
    } else {
      // ------------------------------------------------
    	// List
    	// ------------------------------------------------
    	
    	// Display the main div
    	echo shopper_admin_div($table->parent_id, $form_url, 'list');
    	
    	// The table
      $table->prepare_items();
      
      // Page title
      $t = '';
      $link = "?page=$form_url&action=edit&parent_id=" . $table->parent_id . "&table_id=" . $table->table_id;
      if ($addable) {
        $t = '<a class="add-new-h2" href="' . $link . '">Adaugare</a>';
      }    
      if (isset($table->parent_id)) {
      	echo "<h2>" . $table->total_items . " $title $t</h2>";
      } else {
      	echo "<h2>$title $t</h2>";
      }
      
      // The search
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


// Create the main div id and classes
// - it is important because detail tables sometimes must be hidden
// - and for the navigation between tables on edit and add
//
// parent: if this is a master detail relationship
// page: the page name like orders, customers etc
// action: edit, list or save
function shopper_admin_div($parent, $page, $action) {
  // Check if this is a master detail relationship
  if (isset($parent)) {
  	$detail = ' detail';
  } else {
  	$detail = '';
  }
  echo "<div id='" . $page . "' class='wrap " . $detail . " " . $action . "'>";  
}



// Display a detail table
//
function shopper_admin_display_detail($detail) {
	$page = "shopper-" . $detail['page'];
  if ($_REQUEST['page'] != $page) {
    shopper_admin_display_submenu_page($detail['title'], $detail['page'], $detail['table'], true, true, true);
  }
}


// Save the form
//
// post: the $_POST
// table_name: the SQL table
// nonce: the nonce
//
// - if one of the required fields are missing there is no save
// - moreover, the newly created empty record (for Add) will de deleted
function shopper_admin_form_save($post, $editables, $table_name, $nonce) {
	if (wp_verify_nonce( $post['nonce'], $nonce )) {
      
      global $wpdb;
      $wpdb->show_errors();
      
      $table = $wpdb->prefix . "shopper_" . $table_name;
      
      $required = shopper_admin_form_check_required_fields_for_save($post, $editables);
      if ($required == "") {
      
      	// Construct the SQL query
      	// (id, name, email, phone)
      	$fields = '(';
      
      	// (%s, %s, %s, %s)
      	$values = '(';
      
      	// name=VALUES(name), ...
				$update = '';
			
				// array($post['id'], ...
				$a = array();
      
      	foreach ($post as $k => $v) {
      		if (!(in_array($k, array("nonce", "action", "submit")))) {
      			$fields .= "$k, ";
      			$values .= "%s, ";
      			if ($k != "id") {
      				$update .= "$k=VALUES($k), ";
      			}
      			$a[] = $v;
      		}
      	}
      	$fields = chop($fields, ", ");
      	$fields .= ")";
      	$values = chop($values, ", ");
      	$values .= ")";
      	$update = chop($update, ", ");
      
      	$ret = $wpdb->query( 
        	$wpdb->prepare( 
        		"INSERT INTO $table $fields VALUES $values ON DUPLICATE KEY UPDATE $update ", $a
        	)
      	);
      
      	if ($ret != false) {
        	echo "Succes!";        
      	} else {
      		echo "Error!";
      	}
      
      } else {
      	// Required fields are missing
      	echo $required . " is empty";
      	
      	// Delete this empty record
      	$ret = $wpdb->query( 
        	$wpdb->prepare( 
        		"DELETE FROM $table WHERE id = " . $post['id']
        	)
      	);
      	
      }
  } else {
  	echo "Nonce error";
  }
}


// Check if all required fields are ok before save
function shopper_admin_form_check_required_fields_for_save($post, $editables) {
	$ret == "";
	
	foreach ($editables as $e) {
		if ($e['required'] == true) {
			$field = $e['id'];
			if ($post[$field] == "") {
				$ret .= $e['title'] . ", "; 
			}
		}
	}

	return $ret;
}


// Display a form to edit / add new data
// - it is like a partial for simpler forms
//
// - item: the existing or new record to edit / add
// - table: the WP List Table
// - nonce: the nonce string to secure the form
function shopper_admin_form_body($item, $table, $nonce) {
	// Get the fields to edit
	$editables = $table->get_editables(); ?>
	<form id="edit" action="" method="post">
    <table class="form-table">
      <tbody>
        <?php foreach ($editables as $field) { ?>
          <tr>
            <th><label><?php echo $field['title'] ?></label></th>
            <td>
            	<?php echo shopper_admin_form_field($field, $item, $table); ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <input type="hidden" value="<?php echo $item->data['id'] ?>" id="id" name="id">
    <input type="hidden" value="<?php echo wp_create_nonce($nonce) ?>" id="nonce" name="nonce">
    <input type="hidden" id="action" name="action" value="submit-form">
    <p class="submit"><input type="submit" value="<?php echo $item->button_title ?>" class="button-primary" id="submit" name="submit"></p>
  </form>
<?php }


// Display a hidden, text, textarea or select field
function shopper_admin_form_field($field, $item, $table) {
	$id = $field['id'];
	$value = $item->data[$id];
	
	// Hidden
	// Non editable field, usually the parent value
	if (isset($field['not_editable']) && ($field['not_editable'] == true)) {
		// Parent value comes from the WP List Table 
    $i = (object) $item->data;
    echo $table->column_default($i, $id); 
    $value = $field['value'];?>
    
    <input type="hidden" value="<?php echo $value ?>" id="<?php echo $id ?>" name="<?php echo $id ?>"> <?php
  } else {
  	
  	// Textarea
  	if (isset($field['type']) && ($field['type'] == 'textarea')) { ?>
  		<textarea cols="40" rows="5" name="<?php echo $id ?>" id="<?php echo $id ?>"><?php echo $value ?></textarea> <?php
  	} else { 
  		
  		// Normal input 
  		?>
  		<input type="text" class="regular-text" value="<?php echo $value ?>" id="<?php echo $id ?>" name="<?php echo $id ?>"> <?php
  	}
  	
  }
}



// Decide if this is an edit or add new action
// - request: the query param to work with, ie $_REQUEST['status']
// - table_name: the SQL name of the table
//
// Returns
// - item: the row as a class, which is either empty (for add new),
//   or preloaded from db with data to edit
// - page_title: the "Add" or "Modify" text 
// - button_title: the "update" or "add" text
function shopper_admin_form_setup($request, $table_name) {
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
