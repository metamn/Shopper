<?php

// Import old smuff data in Admin
// --------------------------------------------------------------------------------
//


// Steps
//
// 1. Export the smuff db as sql
// 2. Import the db into ujsmuff
// 3. run this script


// Gotchas
//



global $old;
$old = new wpdb('ujsmuff','5FJFuy6Ff6bHNCcs','ujsmuff','localhost');

$posts = $old->get_results(
  "SELECT * FROM wp_cp53mf_posts WHERE post_type = 'post'"
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
    
    $product = get_product($product_id);
    echo "<li>&nbsp;Name: " . $product->name . "</li>";
    echo "<li>&nbsp;Price: " . $product->price . "</li>";
    echo "<li>&nbsp;Sale Price: " . $product->special_price . "</li>";
    
    $vars = get_variations($product_id);
    if ($vars) {
      foreach ($vars as $v) {        
        if ($v->name != '') {
          echo "<li>&nbsp;Variation: " . $v->name . ', ' . $v->price . "</li>";
        }        
      }
    }
    
    $attach = get_attachments($post->ID);
    if ($attach) {
      foreach ($attach as $a) {        
        echo "<li>&nbsp;Attachment: " . $a->guid . "</li>";        
      }
    }
    
    echo "<li>" . get_content($post->post_content) . "</li>";
    
    
    echo "<li>&nbsp;</li>";
  }
  
}
echo "</ul>";



// Get the product
function get_product($id) {  
  global $old;
  $product = $old->get_results(
    "SELECT * FROM wp_cp53mf_wpsc_product_list WHERE id = " . $id
  );
  
  return $product[0];  
}

// Get product variations
function get_variations($id) {  
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
function get_attachments($id) {
  global $old;
  $ret = $old->get_results(
    "SELECT * FROM wp_cp53mf_posts WHERE post_parent = " . $id . " AND " .
    "post_type = 'attachment'"
  );
  
  return $ret;  
}


// Get post content
function get_content($content) {
  $s = explode("<h3>Intrebari frecvente</h3>", $content);
  if ($s[0]) {
    $s2 = explode("<h3>Opiniile cumparatorilor</h3>", $s[0]);
    return $s2[0];
  } else {
    $s = explode("<h3>Opiniile cumparatorilor</h3>", $content);
    return $s[0];
  }
}

?>
