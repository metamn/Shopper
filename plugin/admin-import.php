<?php

// Import old smuff data in Admin
// --------------------------------------------------------------------------------
//


// Steps
//
// 1. Export the smuff db as sql.gz, from slicehost/backup
// 2. Import the db into ujsmuff
// 3. run this script


// Imports
// 1. posts, products, variations, images, comments and pingbacks
// 3. orders and customers



if ($_POST) {
  if ($_POST['import'] == 'posts') { shopper_import_posts(); }
  if ($_POST['import'] == 'orders') { shopper_import_orders(); }
  if ($_POST['import'] == 'convert') { shopper_import_convert_url(); }
}


// Convert post GUID from smuff to localhost
// ----------------------------------------------------------------
function shopper_import_convert_url() {
	global $wpdb;
	$wpdb->show_errors();
	
	$attachments = $wpdb->get_results(
    "SELECT * FROM wp_posts"
  );
  
  foreach ($attachments as $a) {
  	$guid = str_replace("www.", "", $a->guid);
  	$guid = str_replace("smuff.ro", "localhost/shopper", $guid);
  	echo "$a->ID : $guid" . "<br/>";
		$ret = $wpdb->query( 
			"UPDATE wp_posts SET guid = '" . $guid . "' WHERE ID = '" . $a->ID . "'"
		);
		echo "Result: $ret";
		echo "<br/>";
  }
  
}




// Orders
// ----------------------------------------------------------------


// Notes:
// - There are plenty incomplete orders like no shipping cost or zero total costs
// - There are products which changed their name during years


// Do the import
function shopper_import_orders() {
  global $old;
  $old = new wpdb('cs','cs','ujsmuff','localhost');
  $old->show_errors();
  
  // Drop existing data
  shopper_import_drop_old_profiles_and_orders();
  
  // Get orders
  $orders = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_purchase_logs ORDER BY id"
  );
  
  // Not saved items
  // - some items changed their name during years ....
  $not_saved = array();
  
  foreach ($orders as $order) {
    // Load and save buyer info
    $customer = shopper_import_order_customer($order->id);
    $c = shopper_import_save_customer($customer);
    echo "<br/>Customer saved ... $c";
    
    // Save order
    $sanitized_order = shopper_import_sanitize_order($order);
    $o = shopper_import_save_order($sanitized_order, $c);
    echo "<br/>Order saved ... $o";
    
    // Load order items
    $items = shopper_import_order_items($order->id);
    foreach ($items as $item) {
      // Match old product with new 
      $match = shopper_import_orders_get_product($item->name);
      if ($match) {
        // Get the product
        $product = shopper_product($match['post_id']);  
        $i = shopper_import_save_order_items($o, $product, $item, $match);         
        echo "<br/>Item saved ... $i";        
      } else {
        echo "<br/>Item NOT saved! ... $i";
        $not_saved[] = $product->name;
      }
    }
    echo "<br/>";
  }
  
  echo "<br/>Not saved items:";
  foreach ($not_saved as $n) {
    echo "<br/>" . $n;
  }
  
}


// Save order items
function shopper_import_save_order_items($orderid, $product, $wpec, $match) {
  global $wpdb;
  $wpdb->show_errors();
  
  $ret = $wpdb->query( 
    $wpdb->prepare( 
      "INSERT INTO wp_shopper_order_items
       (order_id, product_post_id, product_name, product_qty, product_variation_id, product_variation_name, product_price)
       VALUES (%s, %s, %s, %s, %s, %s, %s)
      ", 
      array($orderid, $product->post_id, $product->name, $wpec->quantity, 
        $product->variations[$match['variation_id']-1]['id'], 
        $product->variations[$match['variation_id']-1]['name'], $wpec->price
      )
    )
  );
  
  return $wpdb->insert_id; 
}

// Sanitize order
// - transform WPEC data into Shopper
function shopper_import_sanitize_order($order) {
  // Delivery
  $d = $order->shipping_option;
  
  // There are some errors in the WPEC, ie orders without delivery
  $order->delivery_id = 0;  
  $s = explode("Posta Romana", $d);
  if ($s[0]) {
    $order->delivery_id = 1;
  }
  
  $s = explode("Fan Courier, cu plata la livrare 24 ore", $d);
  if ($s[0]) {
    $order->delivery_id = 2;
  }
  
  $s = explode("Fan Courier, cu plata prin transfer bancar in avans 1-2 zile", $d);
  if ($s[0]) {
    $order->delivery_id = 3;
  }
  
  $s = explode("Ridicare din sediul Tg. Mures", $d);
  if ($s[0]) {
    $order->delivery_id = 4;
  }
  $order->delivery = $order->base_shipping;
  
  
  // Discount
  $order->discount_id = $order->discount_data;
  $order->discount = $order->discount_value;
  
  // Total
  $order->grand_total = $order->totalprice;
  $order->total = $order->grand_total - $order->discount;
  
  // Status
  $order->status_id = $order->processed;
  
  return $order;
}


// Add a new order
function shopper_import_save_order($order, $profile_id) {
  global $wpdb;
  $wpdb->show_errors();
  
  $ret = $wpdb->query( 
    $wpdb->prepare( 
      "INSERT INTO wp_shopper_orders
       (old_id, profile_id, delivery_id, delivery, status_id, discount_id, total, grand_total, date, type)
       VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
      ", 
      array($order->id, $profile_id, $order->delivery_id, $order->delivery, 
        $order->status_id, $order->discount_id, $order->total, $order->grand_total,
        date("Y-m-d H:i:s", $order->date), '1')
    )
  );
  
  return $wpdb->insert_id;  
}


// Add a new customer
// - if there is already a customer we add anyway the (new) address ...
function shopper_import_save_customer($customer) {
  global $wpdb;
  $wpdb->show_errors();
  
  $wpdb->query( 
    $wpdb->prepare( 
      "INSERT INTO wp_shopper_profiles
      (name, email, phone)
      VALUES (%s, %s, %s)", 
      array($customer['name'], $customer['email'], $customer['phone'])
    )
  );
  
  $id = $wpdb->insert_id;
  
  // Save addresses
  $wpdb->query( 
    $wpdb->prepare( 
      "INSERT INTO wp_shopper_addresses
      (profile_id, address, city)
      VALUES (%s, %s, %s)", 
      array($id, $customer['address'], $customer['city'])
    )
  );
  
  return $id;
}


// Drop old customer dat
function shopper_import_drop_old_profiles_and_orders() {
  global $wpdb;
  $wpdb->show_errors();
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_shopper_profiles"
    )
  );
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_shopper_addresses"
    )
  );
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_shopper_orders"
    )
  );
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_shopper_order_items"
    )
  );
}


// Displaying order, to check before the real import
function shopper_import_display_orders() {
  global $old;
  $old = new wpdb('cs','cs','ujsmuff','localhost');
  
  // Get orders
  $orders = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_purchase_logs ORDER BY id"
  );
  
  // Check whicg items could not be saved
  $no_match = array();
  
  foreach ($orders as $order) {
    echo "<br/><br/>Order # : " . $order->id;
    echo "<br/>Date: " . date("Y M d", $order->date);
    echo "<br/>Total: " . $order->totalprice;
    echo "<br/>Status: " . $order->processed;
    echo "<br/>Shipping: " . $order->shipping_option . ", " . $order->base_shipping . " RON";
    echo "<br/>Discount: " . $order->discount_value . ", " . $order->discount_data;
    
    // Load order items    
    $items = shopper_import_order_items($order->id);    
    foreach ($items as $item) {
      // Match old product with new 
      $match = shopper_import_orders_get_product($item->name);
      if ($match) {
        $product = shopper_product($match['post_id']);
        //print_r($product);
        echo "<br/>&nbsp;&nbsp;Product: " . $product->name;
        echo "<br/>&nbsp;&nbsp;Variation: " . $product->variations[$match['variation_id']-1]['name'];
        //echo "<br/>&nbsp;&nbsp;Price: " . $product->variations[$match['variation_id']-1]['price'];
        echo "<br/>&nbsp;&nbsp;Price: " . $item->price;        
        echo "<br/>&nbsp;&nbsp;Quantity: " . $item->quantity;        
        echo "<br/>";
      } else {
        $no_match[] = $item->name;
        echo "<br>NO PRODUCT MATCH: " . $item->name . " <br/>";        
      }
    } 
    
    
    // Load buyer info
    $customer = shopper_import_order_customer($order->id);
    if ($customer) {
      foreach ($customer as $k => $v) {
        echo "<br/>&nbsp;&nbsp$k: $v";
      }
      echo "<br/>";
    }
  }
  
  //print_r($no_match);
  echo "<br/>No match: ";
  foreach ($no_match as $n) {
    echo  "<br/>" . $n;
  }

}


// Load order items
function shopper_import_order_items($id){
  global $old;
  
  $items = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_cart_contents WHERE purchaseid = " . $id
  );

  return $items;
}

// Match old order items (form WPSC) with new products (to Shopper)
// - returns the post_id, variation_id and quantity 
function shopper_import_orders_get_product($name) {
  // Try to separate variation from name
  // - ex.: Lounge book (red)
  
  if ($name == "Lumini Spa (6 buc.)") {
    $name = "Lumini Spa (6 buc.)";
    $variation = '';
  } else {
    $variation = '';
    $s = explode('(', $name);
    if (isset($s[0])) {
      $name = $s[0];
      if (isset($s[1])) {
        $s1 = explode(')', $s[1]);
        $variation = $s1[0];
      }    
    }
  }
  
  
  
  //echo "<br/>product: $name";
  //echo "<br/>variation: $variation";
  
  // Some products changed their names through years
  // - this is how to fix it:
  
  switch ($name) {
    case 'Racoritor de vinuri':
      $name = 'Racitor inteligent de vinuri';
      break;
    case 'Vinturi aerator profesional pentru vin':
      $name = 'Decantus aerator profesional pentru vin';
      break;
    case 'Ceas Binar Slider SD227R':
      $name = 'Ceas Binar Slider SD227R1 si SD102B1';
      break;
    case 'Sabia Laser Speciala FX ':
      $name = 'Sabia Laser Ultimate FX ';
      break;
    case 'Robot Solar':
      $name = 'Robot Solar T3';
      break;
    case 'Frolicat':
      $name = 'Frolicat Dart 360';
      break;  
    case 'Borat bikini - Borat mankini ROZ':
      $name = 'Borat bikini - Borat mankini';
      break;
    case 'Proiector iPhone':
      $name = 'Proiector iPhone 2';
      break;
    case 'Scatecycle':
      $name = 'SkateCycle';
      break;
    case 'Manusi Touchscreen':
      $name = 'Manusi Sport Touchscreen';
      break;
  }
  
  
  // Search post meta to get post id
  global $wpdb;
  $postmeta = $wpdb->get_results(
    'SELECT * FROM wp_postmeta WHERE meta_key = "product_name" AND ' .
    'meta_value = "' . $name . '"'
  );
  
  if ($postmeta) {
    $post_id = $postmeta[0]->post_id;
    // Search post meta for variation
    $variation = $wpdb->get_results(
      "SELECT meta_value FROM wp_postmeta WHERE post_id = '" . $post_id . "' " .
      "AND meta_key = 'product_variations'"
    );
    
    $variation_id = '1';
    if ($variation) {
      foreach ($variation as $var) {
        // var is a database row, we need just the meta value
        $va = maybe_unserialize($var->meta_value);
        // after unserialization we need the values of the first array
        $v = $va[0];
        //print_r($v);
        if ($v['name'] == $variation) {
          $variation_id = $v['id'];
          break;
        }
      }      
    }
    
    return array(
      'post_id' => $post_id,
      'variation_id' => $variation_id
    );
  }
}


// Get customer info from order
function shopper_import_order_customer($id) {  
  global $old;
  $info = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_submited_form_data WHERE log_id = " . $id
  );
  
  $ret = array(
    'name' => '',
    'address' => '',
    'city' => '',
    'email' => '',
    'phone' => ''
  );

  if ($info) {
    foreach ($info as $i) {
      switch ($i->form_id) {
        case '2':
          $ret['name'] .= $i->value . " ";
          break;
        case '3':
          $ret['name'] .= $i->value . " ";
          break;
        case '4':
          $ret['address'] = $i->value;
          break;
        case '5':
          $ret['city'] = $i->value;
          break;
        case '8':
          $ret['email'] = $i->value;
          break;
        case '16':
          $ret['phone'] = $i->value;
          break;
        case '17':
          $ret['phone'] = $i->value;
          break;
      }
    }
  }
  
  return $ret;
}




// Posts
// ----------------------------------------------------------------


// Does the import, really
function shopper_import_posts() {
  global $old;
  $old = new wpdb('cs','cs','ujsmuff','localhost');
  $old->show_errors();
  
  // Drop existing data
  shopper_import_drop_old_posts();
  
  for ($i=1; $i<=10; $i++) {
  
  	$limit = $i*100;
  	$offset = ($i-1)*100;

  // Get all published posts
  $posts = array();
  $posts = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT " . $limit . " OFFSET " . $offset
  );
  
  echo "Importing " . count($posts) . " posts<br/>";
  
  $cccc = 0;
  foreach ($posts as $post) {
    
    $cccc += 1;
    echo "<br>CCCC: $cccc</br>";
    
    // Check if it is a product
    $meta = $old->get_results(
      "SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $post->ID . 
      " AND meta_key = 'product_id'"
    );
    
    $product_id = '';
    if (isset($meta[0])) {
      $product_id = $meta[0]->meta_value;
    }
    
    if ($product_id != '') {
      echo "<li>" . $post->post_title . " ( " . $product_id . ")" . "</li>";
      
      // Product
      $product = shopper_import_get_product($product_id);      
      
      // Variations
      $vars = shopper_import_get_variations($product_id);
            
      // Content
      $content = shopper_import_get_content($post->post_content);
      
      // Images
      $attach = shopper_import_get_attachments($post->ID);
      //$attach = array();
      
      // Comments
      $comms = shopper_import_get_comments2($post->ID);
      
      // Save
      $id = shopper_import_save_post($post, $product, $vars, $content, $attach, $comms); 
      echo "<br/>... post saved, id=$id";
    } else {
    	echo "<br>No product id ... $post->ID<br/>";
    }
  }
  
  }
  
  
}




// Save the post / product
// - it is done via the WP API not direct SQL insert
function shopper_import_save_post($post, $product, $vars, $content, $attach, $comms){
  require_once(WP_CONTENT_DIR . '/../wp-config.php');
  
  
  global $old;
  global $wpdb;
  $old = new wpdb('cs','cs','ujsmuff','localhost');
  $old->show_errors();
  $wpdb->show_errors();
  
  
  // Remove old post id, otherwise it will not insert
  // - http://codex.wordpress.org/Function_Reference/wp_insert_post
  $post->ID = '';
  
  // Replace post content
  $post->post_content = $content;
  
  // Create post  
  $id = wp_insert_post($post);
  
  // Add product info as meta fields
  add_post_meta($id, 'product_name', $product->name);
  add_post_meta($id, 'product_description', $post->post_excerpt);
  
  // Add variations
  $variations = array();
  if ($vars) {
    $i = 1;
    foreach ($vars as $v) {
      if ($v->name != '') {
        $variation = array();
        $variation['id'] = $i;
        $variation['name'] = $v->name;
        $variation['price'] = $v->price;
        $variation['saleprice'] = '';
        $variation['delivery'] = '';
        $variation['image'] = '';
        $variation['stock'] = '';
        
        $variations[] = $variation;
        $i++; 
      }
    }
  } else {
    $variation = array();
    $variation['id'] = 1;
    $variation['name'] = 'default';
    $variation['price'] = $product->price;
    $variation['saleprice'] = '';
    $variation['delivery'] = '';
    $variation['image'] = '';
    $variation['stock'] = '';
    
    $variations[] = $variation;  
  }
  
  add_post_meta($id, 'product_variations', $variations);
  
  
  // Attachments
  // - slicehost ip must be replaced with smuff: http://173.203.94.129/wp-content/uploads/2006/11/ceas-binar-samui-moon7.jpg
  foreach ($attach as $a) {
  	$a_original = $a->ID;
  	// ID is made empty for wp_insert .....
    $a->ID = '';
    $a->post_parent = $id;
    $a->guid = str_replace("173.203.94.129", "www.smuff.ro", $a->guid);
    $a->guid = str_replace("www", "", $a->guid);
    $a->guid = str_replace("smuff.ro", "localhost/shopper", $a->guid);
    
    $aid = wp_insert_post($a);
    // Add Attachment info as meta fields
  	// GET POST META NOT WORKING !!!!!!!
  	//add_post_meta($aid, '_wp_attached_file', get_post_meta($a->ID, '_wp_attached_file', true));
  	//add_post_meta($aid, '_wp_attachment_metadata', get_post_meta($a->ID, '_wp_attachment_metadata', true));
    
    
    $meta = $old->get_results(
			"SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $a_original . 
			" AND meta_key = '_wp_attached_file'"
		);
		add_post_meta($aid, '_wp_attached_file', $meta[0]->meta_value);  
		
		
		$meta = $old->get_results(
			"SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $a_original . 
			" AND meta_key = '_wp_attachment_metadata'"
		);
		add_post_meta($aid, '_wp_attachment_metadata', $meta[0]->meta_value); 
    
    
    echo "<br/>... Attachment: $aid";
    
  }
  
  // Comments
  foreach ($comms as $c) {
    $data = array(
      'comment_post_ID' => $id,
      'comment_author' => $c->comment_author,
      'comment_author_email' => $c->comment_author_email,
      'comment_author_url' => $c->comment_author_url,
      'comment_content' => $c->comment_content,
      'comment_type' => $c->comment_type,
      'comment_parent' => $c->comment_parent,
      'user_id' => $c->user_id,
      'comment_author_IP' => $c->comment_author_IP,
      'comment_agent' => $c->comment_agent,
      'comment_date' => $c->comment_date,
      'comment_approved' => $c->comment_approved,
    );
    $cid = wp_insert_comment($data);
    //echo "<br/>... Comment: $cid";
  }
  
  return $id;
}



// Drop posts, postmeta and comments
// - to clean up the db
// - this was done manually before every import
function shopper_import_drop_old_posts() {
  global $wpdb;
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_comments"
    )
  );
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_posts"
    )
  );
  
  $wpdb->query( 
    $wpdb->prepare( 
      "TRUNCATE TABLE wp_postmeta"
    )
  );
}


// Displays the imported posts
// - used to understand the real import
function shopper_import_display_posts() {
  global $old;
  $old = new wpdb('cs','cs','ujsmuff','localhost');
  
  
  $posts = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_type = 'post'"
  );
   
  if (!isset($posts)) {
  	die("No posts ... probably the password / the db connection is wrong");
  }
  

  echo "<ul>";
  foreach ($posts as $post) {
    // this does not works ....
    //$product_id = get_post_meta($post->ID, 'product_id', true);
    
    $meta = $old->get_results(
      "SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $post->ID . 
      " AND meta_key = 'product_id'"
    );
    //print_r($meta);
    
    $product_id = '';
    if (isset($meta[0])) {
      $product_id = $meta[0]->meta_value;
    }
    
    if ($product_id != '') {
      echo "<li><h1>" . $post->post_title . " ( " . $product_id . ")" . "</h1></li>";
      
      // Product
      $product = shopper_import_get_product($product_id);
      echo "<li>&nbsp;Name: " . $product->name . "</li>";
      //echo "<li>&nbsp;Price: " . $product->price . "</li>";
      //echo "<li>&nbsp;Sale Price: " . $product->special_price . "</li>";
      
      // Variations
      $vars = shopper_import_get_variations($product_id);
      if ($vars) {
        foreach ($vars as $v) {        
          if ($v->name != '') {
            echo "<li>&nbsp;Variation: " . $v->name . ', ' . $v->price . "</li>";
          }        
        }
      }
      
      // Attachments
      $attach = shopper_import_get_attachments($post->ID);
      if ($attach) {
        foreach ($attach as $a) {        
        	echo "<li>&nbsp;Attachment: " . $a->guid . "</li>";  
        	
        	$meta = $old->get_results(
						"SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $a->ID . 
						" AND meta_key = '_wp_attached_file'"
					);
        	echo "<li>&nbsp;Attachment meta 1: " . $a->ID . " : " . $meta[0]->meta_value . "</li>";  
        	
        	
        	$meta = $old->get_results(
						"SELECT * FROM wp_cp53mf_postmeta WHERE post_id = " . $a->ID . 
						" AND meta_key = '_wp_attachment_metadata'"
					);
        	echo "<li>&nbsp;Attachment meta 2: " . $a->ID . " : " . $meta[0]->meta_value . "</li>"; 
        }
      }
      
      
      
      // Content
      // echo "<li>" . shopper_import_get_content($post->post_content) . "</li>";
      
      
      // Comments
      $comms = shopper_import_get_comments2($post->ID);
      if ($comms) {
        foreach ($comms as $c) {        
          //echo "<li>&nbsp;Comment: " . $c->comment_content . "</li>";        
        }
      }
      
      echo "<li>&nbsp;</li>";
    }
    
  }
  echo "</ul>";
}



// Get the product
function shopper_import_get_product($id) {  
  global $old;
  $product = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_product_list WHERE id = " . $id
  );
  
  return $product[0];  
}

// Get product variations
function shopper_import_get_variations($id) {  
  global $old;
  /*
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_variation_values_assoc AS a, ".
    "wp_cp53mf_wpsc_variation_values AS v, ".
    "wp_cp53mf_wpsc_variation_properties AS p ".
    "WHERE a.product_id = " . $id . " AND " .
    "p.id = a.value_id AND " .
    "v.id = a.value_id"
  );
  */
  
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_variation_combinations " .
    "JOIN wp_cp53mf_wpsc_variation_properties ON wp_cp53mf_wpsc_variation_combinations.priceandstock_id = wp_cp53mf_wpsc_variation_properties.id " .
    "JOIN wp_cp53mf_wpsc_variation_values ON wp_cp53mf_wpsc_variation_combinations.value_id = wp_cp53mf_wpsc_variation_values.id " .
    "WHERE wp_cp53mf_wpsc_variation_combinations.product_id = " . $id
  );
  
  // print_r($ret);
  return $ret;  
}

// Get attachments
function shopper_import_get_attachments($id) {
  global $old;
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_parent = " . $id . " AND " .
    "post_type = 'attachment'"
  );
  
  return $ret;  
}


// Get post content
function shopper_import_get_content($content) {
  $s = explode("<h3>Intrebari frecvente</h3>", $content);
  if ($s[0]) {
    $s2 = explode("<h3>Opiniile cumparatorilor</h3>", $s[0]);
    return $s2[0];
  } else {
    $s = explode("<h3>Opiniile cumparatorilor</h3>", $content);
    return $s[0];
  }
}

// Get comments
function shopper_import_get_comments2($id) {
  global $old;
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_comments WHERE comment_post_id = " . $id . " AND " .
    "comment_approved = 1"
  );
  
  return $ret;  
}


?>
