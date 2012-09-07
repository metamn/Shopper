<?php


// Category functions
//

// Get all subcategories with images
// - $cat_name : parent category name
function category_list($cat_name) {
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
				$img = post_thumbnails($latest_post[0]->ID, 'medium');
				if (isset($img)) {
					$r = new stdClass();
					
					$r->title = $c->cat_name;
					$r->image = $img;
					$r->link = get_category_link($c->term_id);
					
					$ret[] = $r;
				}
			}
		}
	}
	
	return $ret;
}



// Returns category ID from name
function get_category_id($cat_name){
	$term = get_term_by('name', $cat_name, 'category');
	return $term->term_id;
}




// Product functions
//


// Get product data
// - returns a $post like object
function product($post_id){
  $ret = new stdClass();
  
  return $ret;
}

// Get post thumbnail
// - returns an image URL
function post_thumbnails($post_id, $size) {
  $ret = "";
  
  $images = post_attachments($post_id);
  
  foreach ($images as $img) {
   	// - This is not working since import !!!
   	// $thumb = wp_get_attachment_image_src($img->ID, $size);
   	// $ret = $thumb[0];
   	$thumb = wp_get_attachment_image_src2($img->ID);
    $ret = $thumb[$size];
    break; 
  }
  
  return $ret;
}


// Get post thumbnails, hacked version
function wp_get_attachment_image_src2($id) {
	$ret = array();
	
	$a = wp_get_attachment_image_src($id, $size);
	$ret['full'] = $a[0];
	
	// Get image folder
	$x = explode("/", $ret['full']);
	$l = get_last_explode($x);
	$url = str_replace($l, "", $ret['full']);
	
	// Get medium snd thumbnail
	$m = unserialize(get_post_meta($id, '_wp_attachment_metadata', true));
	$ret['thumbnail'] = $url . $m['sizes']['thumbnail']['file'];
	$ret['medium'] = $url . $m['sizes']['medium']['file'];
	
	return $ret;
}

// Display post thumbnails
// - $col_id: an ID to aid displaying the product / item in columns
function display_post_thumbnails($post_id, $title, $link, $col_id, $only_first = false) {
  $ret = "";
  
  $images = post_attachments($post_id);
  //print_r($images);
  
  foreach ($images as $img) {
   	//print_r($img);
   	$thumb = wp_get_attachment_image_src($img->ID, 'full');
    //print_r($thumb);
    
    $ret .= '<div class="item col-' . $col_id . '">';
    $ret .= "<div class='title'><a href='" . $link . "' title='" . $title . "'>" . $title . "</a></div>";
    $ret .= "<div class='image'><a href='" . $link . "' title='" . $title . "'><img src='$thumb[0]' rev='$thumb[0]' title='$title' alt='$title'/></a></div>";
    $ret .= '</div>';

    if ($only_first) { break; }
  }
  
  return $ret;
}
// Adding featured image support for post
add_theme_support( 'post-thumbnails' ); 


// Get post attachments / images
function post_attachments($post_id) {  
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



// Other functions
//


// Determine what kind of content is displayed
// - like search, archive etc ...
// - if necessary the title is displayed, else will stay hidden
// - this is first of all for the HTML5 Outliner
function get_content_title() {  
  // By default content title is not displayed
  $hidden = "class='hidden'";
  
  // By default title is derived from body_class
  $body_class = get_body_class();
  if ($body_class) {
    $title = ucfirst($body_class[0]);
  } else {
    $title = "Content";
  }
  
  if (is_category()) {
    $hidden = '';
    $title = single_cat_title('', false);
  }
  
  if (is_search()) {
    $hidden = '';
    $title = "Search for " . get_search_query();
  }
  
  
  return "<h3 $hidden>$title</h3>";
}


// General functions
//

// Get the responsive image
function responsive_image($post_id) {
  $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'large');
  $medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'medium');
  $small_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'thumbnail');
  
  $ret = "<noscript data-large='$large_image_url[0]' data-medium='$medium_image_url[0]' data-small='$small_image_url[0]' data-alt='Koala'>";
  $ret .= "<img src='Koala.jpg' alt='Koala' />";
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