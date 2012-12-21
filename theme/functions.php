<?php


// Wordpress functions
//


// Query for multiple posts
// - the query string has the syntax of the query_posts WP function
function query_posts2($query_string) {
  $q = new WP_Query($query_string);
  return $q;
}


// Category functions
//

// Get all subcategories, each subcategory with an image
// - $cat_name : parent category name
// - returns an array of object describing each category
function get_category_list($cat_name) {
	$ret = array();
	
	$cat = get_categories("child_of=" . get_category_id($cat_name));
	if (isset($cat)) {
		foreach ($cat as $c) {
			$latest_post = get_posts(array(
				"numberposts" => 1, 
				"category" => $c->term_id, 
				"order" => 'ASC'
			));
			
			if (isset($latest_post[0])) {
				$img = get_product_images($latest_post[0]->ID,  true);
				if (isset($img)) {
					$r = new stdClass();
					
					$r->title = $c->cat_name;
					$r->image = $img;
					$r->link = get_category_link($c->term_id);
					$r->description = $c->description;
					$r->count = $c->category_count;
					
					$ret[] = $r;
				}
			}
		}
	}
	
	return $ret;
}


// Returns category ID from category name
function get_category_id($cat_name){
	$term = get_term_by('name', $cat_name, 'category');
	return $term->term_id;
}




// Product functions
//


// Get product data
// - returns am object holding all information about a product
function get_product($post_id){
  $ret = new stdClass();
  
  $ret->images = get_product_images($post_id);
  
  return $ret;
}

// Get product images
// - $is_main: if we need just the first, the representative image 
// - returns an array of objects holding all information about each image
// - if $is_main it will return an image object instead of an array
function get_product_images($post_id, $is_main = false) {
  $ret = array();
  
  $images = get_post_attachments($post_id);
  foreach ($images as $img) {
    $ret[] = get_product_image($img->ID);
    if ($is_main) {
    	break;
    }
  }
  
  if ($is_main) {
   	return $ret[0];
  } else {
  	return $ret;
  }
}


// Get product image
// - $id: the ID of the image
// - returns a class with all image sizes urls
function get_product_image($id) {
	$ret = new stdClass();
	
	$a = wp_get_attachment_image_src($id, 'full');
	$ret->full = $a[0];
	
	$a = wp_get_attachment_image_src($id, 'medium');
	$ret->medium = $a[0];
	
	$a = wp_get_attachment_image_src($id, 'thumbnail');
	$ret->thumbnail = $a[0];
	
	
	/*
	
	// Get image folder
	$x = explode("/", $ret->full);
	$l = get_last_explode($x);
	$url = str_replace($l, "", $ret->full);
	
	// Get medium and thumbnail
	$m = unserialize(get_post_meta($id, '_wp_attachment_metadata', true));
	print_r($m);
	echo "m: $m";
	$ret->thumbnail = $url . $m['sizes']['thumbnail']['file'];
	if (isset($m['sizes']['medium'])) { $ret->medium = $url . $m['sizes']['medium']['file']; }
	
	*/
	
	return $ret;
}


// Get post attachments
// - returns an array of posts which all are attachments of a parent post, usually a product
function get_post_attachments($post_id) {  
  $args = array(
	  'post_type' => 'attachment',
	  'numberposts' => -1,
	  'post_parent' => $post_id,
	  'orderby' => 'menu_order',
	  'order' => 'ASC'
  ); 
  $attachments = get_posts($args);
  return $attachments;
}


// Display product thumbs
// - returns HTML
function display_product_thumbs($images) {
	$ret = '';
	
	foreach ($images as $image) {
		$ret .= "<div class='thumb'>";
		$ret .= "<img src='" . $image->thumbnail . "' />";
		$ret .= "</div>";
	}
	
	return $ret;
}



// Other functions
//


// Determine what kind of content is displayed
// - like search, archive etc ...
// - returns page title and description (for taxonomy)
function get_current_page_properties() {  
	$ret = new stdClass();
	$ret->description = '';
	
  // By default title is derived from body_class
  $body_class = get_body_class();
  if ($body_class) {
    $ret->title = ucfirst($body_class[0]);
  } else {
    $ret->title = "Content";
  }
  
  if (is_category()) {
  	$cat = get_category(get_query_var('cat'), false);
  	
    $ret->title = single_cat_title('', false);
    $ret->description = $cat->description; 
  }
  
  if (is_tag()) {
  	$ret->title = 'tag';
    $ret->description = 'description'; 
  }
  
  if (is_search()) {
    $ret->title = get_search_query();
  }
  
  return $ret;
}


// General functions
//

// Create responsive image
// - $image: a class holding all image sizes
// - $title: the image title
// - returns a <noscript> tag which will he handled by jQuery
function make_responsive_image($image, $title) {
	$ret = "<noscript data-large='" . $image->full . "' data-medium='" . $image->medium . "' data-small='" . $image->thumbnail . "' data-alt='" . $title . "'>";
  $ret .= "<img src='' alt='" . $title . "' />";
  $ret .= "</noscript>";
  
  return $ret;
}

// Generate unique ID
function generateRandomString($length = 10) {
  $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

// Return the last item of an exploded string
function get_last_explode($explode) {
  $c = count($explode);
  return $explode[$c-1];  
}

?>
