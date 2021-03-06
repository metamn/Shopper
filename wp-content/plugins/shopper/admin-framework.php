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
    
    echo shopper_admin_form_save($_POST, $table->get_editables(), $page, $nonce, $table);
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
// table: the table
//
// - if one of the required fields are missing there is no save
function shopper_admin_form_save($post, $editables, $table_name, $nonce, $table) {
	$msg = '';

	if (wp_verify_nonce( $post['nonce'], $nonce )) {
      
      global $wpdb;
      $wpdb->show_errors();
      
      $table_name2 = $wpdb->prefix . "shopper_" . $table_name;
      
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
        		"INSERT INTO $table_name2 $fields VALUES $values ON DUPLICATE KEY UPDATE $update ", $a
        	)
      	);
      
      	if ($ret != false) {
        	$msg = "Succes!";      
        	
        	// Callback, after the save
        	$table->after_save($wpdb->insert_id);
        	
      	} else {
      		$msg = "Error!";
      	}
      
      } else {
      	// Required fields are missing
      	$msg = $required . " trebuie completat";
      }
  } else {
  	$msg = "Nonce error";
  }
  
  return "<div id='message' class='updated below-h2'>" . $msg . "</div>";
}


// Check if all required fields are ok before save
// Returns a string with empty required fields
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
	$editables = $table->get_editables($item); ?>
	<form id="edit" action="" method="post">
    <table class="form-table">
      <tbody>
        <?php foreach ($editables as $field) { echo shopper_admin_form_field($field, $item, $table); } ?>
      </tbody>
    </table>
    <input type="hidden" value="<?php echo $item->data['id'] ?>" id="id" name="id">
    <input type="hidden" value="<?php echo wp_create_nonce($nonce) ?>" id="nonce" name="nonce">
    <input type="hidden" id="action" name="action" value="submit-form">
    <p class="submit"><input type="submit" value="<?php echo $item->button_title ?>" class="button-primary" id="submit" name="submit"></p>
  </form>
<?php }


// Display a hidden, text, textarea or select field
// - field: the display properties of the field
// - item: the data, value of the field
// - table: to access the column_default function to get associated data (like parent name, etc)
//
// - the field can have the following structure:
//
// 	- title: the name of the column, ie Cumparator
//	-	id: the id of the form field, ie profile_id
//	- value: the value of the column, ie 12
//	- type: the form input type: hidden, select, ...
//	- required: if this field is required or not
//	- snippet: a html/text snippet to be added after the field
//	- nonce: if this field supports ajax calls a nonce is a must
//		- the nonce string convention is: 'fieldname_nonce'
//
// - the selectbox values are stored in an array of arrays, with 'title' and 'value' fields set
// - selectbox options can have a $snippet variable storing extra information
// - selected item is marked with 'selected' field
//
function shopper_admin_form_field($field, $item, $table) {
	
	// Each field is a table row
	$row_start = "<tr><th><label>" . $field['title'] . "</label></th><td>";
	// A HTML / text snippet can be added after each field
	$row_end = $field['snippet'] . "</td></tr>";

	// Commonly used data saved into variables
	$id = $field['id'];
	
	// The field value is either:
	// - predefined, like parent_id in a relationship
	// - or loaded from database via $item
	if (isset($field['value'])) {
		$value = $field['value'];
	} else {
		$value = $item->data[$id];
	}
	
	// The field display value is always given by the WP List Table column_default function
	$i = (object) $item->data;
  $display_value = $table->column_default($i, $id); 
  
  // Check and set up nonce
  if (isset($field['nonce'])) {
  	$nonce = " data-nonce='" . $field['nonce'] . "' ";
  } else {
  	$nonce = '';
  }
	
	// Do the job
	switch ($field['type']) {
		case 'hidden':
			echo "<input type='hidden' value='" . $value . "' id='" . $id . "' name='" . $id ."'>";
			break;
		case 'not editable':
			echo "<input type='hidden' value='" . $value . "' id='" . $id . "' name='" . $id . "'>";
			echo $row_start . $display_value . $row_end;
			break;
		case 'textarea':
			echo $row_start;
			echo "<textarea cols='40' rows='5' name='" . $id . "' id='" . $id . "'>" . $display_value . "</textarea>";
			echo $row_end;
			break;
		case 'select':
			echo $row_start;
			echo "<select id='". $id . "' name='" . $id . "' " . $nonce . ">";
  			foreach ($field['value'] as $v) { 
  		 		echo "<option value='" . $v['value'] . "' " . $v['snippet'] . " " . $v['selected'] .  " >" . $v['title'] . "</option>"; 
  		 	}
  		echo "</select>";
			echo $row_end;
			break;
		default:
			echo $row_start;
			echo "<input type='text' class='regular-text' value='" . $value . "' id='" . $id . "' name='" . $id . "'>";
			echo $row_end;
	}
  
}



// Decide if this is an edit or add new action
// - request: the item id
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
  }

	// Covert data into an array
	$item->data = (array) $item->data;
  return $item;
}
