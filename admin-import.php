<?php

// Import old smuff data in Admin
// --------------------------------------------------------------------------------
//


// Steps
//
// 1. Export the smuff db as sql
// 2. Import the db into ujsmuff
// 3. run this script


// Imports
// 1. posts, products, variations, images
// 2. comments and pingbacks
// 3. orders and customers



if ($_POST) {
  if ($_POST['import'] == 'posts') { shopper_import_posts(); }
  if ($_POST['import'] == 'orders') { shopper_import_orders(); }
}



// Orders
// ----------------------------------------------------------------


// Does the import, really
function shopper_import_orders() {
  global $old;
  $old = new wpdb('ujsmuff','5FJFuy6Ff6bHNCcs','ujsmuff','localhost');
  
  // Get orders
  $orders = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_purchase_logs"
  );
  
  foreach ($orders as $order) {
    // Load order items
    $items = shopper_import_order_items($order->id);
    foreach ($items as $item) {
      // Match old product with new 
      $match = shopper_import_orders_get_product($item->name);
      if ($match) {
        $product = shopper_product($match['post_id']);
        //print_r($product);
        echo "<br/>Product: " . $product->name;
        echo "<br/>Variation: " . $product->variations[$match['variation_id']-1]['name'];        
        echo "<br/>";
      }
    }  
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
// - returns the post_id from Shopper and the variation_id of the product associated
function shopper_import_orders_get_product($name) {
  // Try to separate variation from name
  // - ex.: Lounge book (red)
  $variation = '';
  $s = explode('(', $name);
  if (isset($s[0])) {
    $name = $s[0];
    if (isset($s[1])) {
      $s1 = explode(')', $s[1]);
      $variation = $s1[0];
    }    
  }
  //echo "<br/>product: $name";
  //echo "<br/>variation: $variation";
  
  
  // Search post meta to get post id
  global $wpdb;
  $postmeta = $wpdb->get_results(
    "SELECT * FROM wp_postmeta WHERE meta_key = 'product_name' AND " .
    "meta_value = '" . $name . "'"
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




// Posts
// ----------------------------------------------------------------


// Does the import, really
function shopper_import_posts() {
  global $old;
  $old = new wpdb('ujsmuff','5FJFuy6Ff6bHNCcs','ujsmuff','localhost');
  
  // Drop existing data
  shopper_import_drop_old_posts();

  // Get all published posts
  $posts = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_type = 'post' AND post_status = 'publish'"
  );
  
  foreach ($posts as $post) {
    
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
      if ($vars) {
        foreach ($vars as $v) {        
          if ($v->name != '') {
            
          }        
        }
      }
      
      // Content
      $content = shopper_import_get_content($post->post_content);
      
      // Images
      $attach = shopper_import_get_attachments($post->ID);
      
      // Comments
      $comms = shopper_import_get_comments2($post->ID);
      
      // Save
      $id = shopper_import_save_post($post, $product, $vars, $content, $attach, $comms); 
      echo "<br/>... post saved, id=$id";
      
      
    }
  }
}




// Save the post / product
function shopper_import_save_post($post, $product, $vars, $content, $attach, $comms){
  require_once(WP_CONTENT_DIR . '/../wp-config.php');
  
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
    
    $variations[] = $variation;  
  }
  
  add_post_meta($id, 'product_variations', $variations);
  
  // Attachments
  // - slicehost ip must be replaced with smuff: http://173.203.94.129/wp-content/uploads/2006/11/ceas-binar-samui-moon7.jpg
  foreach ($attach as $a) {
    $a->ID = '';
    $a->post_parent = $id;
    $a->guid = str_replace("173.203.94.129", "www.smuff.ro", $a->guid);
    $aid = wp_insert_post($a);
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
    echo "<br/>... Comment: $cid";
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
function shopper_import_display_import_posts() {
  global $old;
  $old = new wpdb('ujsmuff','5FJFuy6Ff6bHNCcs','ujsmuff','localhost');

  
  $posts = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_type = 'post' LIMIT 100"
  );

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
      echo "<li>&nbsp;Price: " . $product->price . "</li>";
      echo "<li>&nbsp;Sale Price: " . $product->special_price . "</li>";
      
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
        }
      }
      
      // Content
      echo "<li>" . shopper_import_get_content($post->post_content) . "</li>";
      
      
      // Comments
      $comms = shopper_import_get_comments2($post->ID);
      if ($comms) {
        foreach ($comms as $c) {        
          echo "<li>&nbsp;Comment: " . $c->comment_content . "</li>";        
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
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_variation_values_assoc AS a, ".
    "wp_cp53mf_wpsc_variation_values AS v, ".
    "wp_cp53mf_wpsc_variation_properties AS p ".
    "WHERE a.product_id = " . $id . " AND " .
    "p.id = a.value_id AND " .
    "v.id = a.value_id"
  );
  
  //print_r($ret);
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
