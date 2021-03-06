<?php


// Product
// --------------------------------------------------------------------------------
//
// - inserts a 'Product info' box in Posts and Pages editor
// - stores Product Name, Product Description and Product Variations in meta fields
// - retrieves complete Product and Products information
//
// The metadata structure describing a product is:
//
//	product_name
//	product_description
//	product_variations, which is a serialized array with the following structure
//		variation->name
//		variation->price
//		variation->saleprice
//		variation->delivery - how many days to deliver the product
//		variation->stock - how much we have in stock
//		variation->image


// insert Product box into Posts and Page
add_action( 'add_meta_boxes', 'shopper_add_custom_box' );
// save Product when saving a Post
add_action( 'save_post', 'shopper_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function shopper_add_custom_box() {
  add_meta_box( 
      'shopper_sectionid',
      __( 'Product info', 'shopper_textdomain' ),
      'shopper_inner_custom_box',
      'post' 
  );
  add_meta_box(
      'shopper_sectionid',
      __( 'Product info', 'shopper_textdomain' ), 
      'shopper_inner_custom_box',
      'page'
  );
}

// Prints the box content
function shopper_inner_custom_box($post) {

  // Use nonce for verification
  wp_nonce_field(plugin_basename( __FILE__ ), 'shopper_noncename');
  
  // If this is an Edit action then read data from post meta
  $product = shopper_product($post->ID);
  
 
  // The actual fields for data entry
  echo '<label for="shopper_product_name">';
       _e("Product Name", 'shopper_textdomain' );
  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
  echo '</label> ';
  echo '<input type="text" id="shopper_product_name" name="shopper_product_name" value="' . $product->name . '" size="25" />';
  echo '<br/>';
  
  echo '<label for="shopper_product_description">';
       _e("Short description", 'shopper_textdomain' );
  echo '</label> ';
  echo '<input type="text" id="shopper_product_description" name="shopper_product_description" value="' . $product->description . '" size="25" />';
  echo '<br/>';
  echo '<br/>';
  
  for ($i = 1; $i < 10; $i++) {
    
    // If this is an Edit action then read data from post meta
    if (isset($product->variations[$i-1])) {
      $variation_name = $product->variations[$i-1]['name'];
      $variation_price = $product->variations[$i-1]['price'];
      $variation_saleprice = $product->variations[$i-1]['saleprice'];
      $variation_delivery = $product->variations[$i-1]['delivery'];
      $variation_stock = $product->variations[$i-1]['stock'];
      $variation_image = $product->variations[$i-1]['image'];
    } else {
      $variation_name = '';
      $variation_price = '';
      $variation_saleprice = '';
      $variation_delivery = '';
      $variation_stock = '';
      $variation_image = '';      
    }   
    
    if (($i == 1) && ($variation_name == '')) {
      $variation_name = "default";
    } 
  
    echo '<label for="shopper_product_variation_name">';
    echo 'Variation #' . $i;    
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_name-'. $i .'" name="shopper_product_variation_name-'. $i .'" value="' . $variation_name . '" size="25" />';
    
    echo '<label for="shopper_product_variation_price">';
    echo "&nbsp;&nbsp;";
    _e("Price", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_price-'. $i .'" name="shopper_product_variation_price-'. $i .'" value="' . $variation_price . '" size="5" />';
    
    echo '<label for="shopper_product_variation_saleprice">';
    echo "&nbsp;&nbsp;";
    _e("Sale", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_saleprice-'. $i .'" name="shopper_product_variation_saleprice-'. $i .'" value="' . $variation_saleprice . '" size="5" />';
    
    echo '<label for="shopper_product_variation_delivery">';
    echo "&nbsp;&nbsp;";
    _e("Delivery", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_delivery-'. $i .'" name="shopper_product_variation_delivery-'. $i .'" value="' . $variation_delivery . '" size="2" />';

		echo '<label for="shopper_product_variation_stock">';
    echo "&nbsp;&nbsp;";
    _e("Stock", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_stock-'. $i .'" name="shopper_product_variation_stock-'. $i .'" value="' . $variation_stock . '" size="2" />';

    echo '<label for="shopper_product_variation_image">';
    echo "&nbsp;&nbsp;";
    _e("Image #", 'shopper_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="shopper_product_variation_image-'. $i .'" name="shopper_product_variation_image-'. $i .'" value="' . $variation_image . '" size="2" />';

    echo '<br/>';
  }
  
  echo '<br/><br/>';
  echo 'Delivery:';
  echo '<br/><br/>';
  echo '  1: 1-2 zile';
  echo '<br/>';
  echo '  2: 2-4 zile';
  echo '<br/>';
  echo '  not set: 5-7 zile';
}


// When the post is saved, saves our custom data
function shopper_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['shopper_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $name = sanitize_text_field($_POST['shopper_product_name']);
  $description = sanitize_text_field($_POST['shopper_product_description']);
    
  $variations = array();
  for ($i = 1; $i < 10; $i++ ) {
    $variation_name = sanitize_text_field($_POST['shopper_product_variation_name-' . $i]);
    if (isset($variation_name) && ($variation_name != '')) {      
      $v = array();
      $v['id'] = $i;
      $v['name'] = $variation_name;
      $v['price'] = sanitize_text_field($_POST['shopper_product_variation_price-' . $i]);
      $v['saleprice'] = sanitize_text_field($_POST['shopper_product_variation_saleprice-' . $i]);
      $v['delivery'] = sanitize_text_field($_POST['shopper_product_variation_delivery-' . $i]);  
      $v['stock'] = sanitize_text_field($_POST['shopper_product_variation_stock-' . $i]);
      $v['image'] = sanitize_text_field($_POST['shopper_product_variation_image-' . $i]);
      
      $variations[] = $v;
    }    
  }  
  
  // Save the data into post meta
  // TODO there is no flash message in WP so errors are invisible
  update_post_meta($post_id, 'product_name', $name);
  update_post_meta($post_id, 'product_description', $description);
  update_post_meta($post_id, 'product_variations', $variations);
}


// Get the product
// - this function is available in the theme
function shopper_product($post_id) {
  $product = new stdClass();
  
  $product->post_id = $post_id;
  $product->name = '';
  $product->description = '';
  $product->variations = '';
  
  $product->name = get_post_meta($post_id, 'product_name', true);
  $product->description = get_post_meta($post_id, 'product_description', true);
  $product->variations = get_post_meta($post_id, 'product_variations', true);
  
  return $product;
}


// Get all products
// - returns a collection of posts
function shopper_products() {
	$q = new WP_Query(array(
		'posts_per_page' => '-1',
		'meta_query' => array( array(
			'key' => 'product_name',
			'value' => '',
			'compare' => '!='
		))
	));
  return $q;
}

?>
