<?php 



// Session
// --------------------------------------------------------------------------------
//
// - identifies the browser / visitor via a cookie called 'shopper'
// - sets up a database table 'sessions' to store:// 
//    - browsing history
//    - cart contents
//



// Manage session
//
// - called in header at every page load
// - returns a standard class:
//  - returning: boolean, if a visitor is returning or not
//  - visits: array, all the visits of the visitor
//  - clicks: array, all the clicks of the visitor
//  - new_visit: this is a new visit
//  - type: [contactable, shopper, ...] 


// Arguments
// - $cart: cart items to store 
function shopper_manage_session($cart = array()) {  
  $session = new stdClass();
  
  $id = $_COOKIE['shopper'];
  if (!($id)) {    
    // create new session id, if necessary
    $session->returning = false;
           
    setcookie('shopper', shopper_generateRandomString(), time()+60*60*24*500, '/');
    $id = $_COOKIE['shopper'];        
  } else {
    $session->returning = true;
  }    
  
  // Determine what action to save  
  if (empty($cart)) {
    $action = shopper_get_post_id();
    // load the cart contents
    $old = shopper_load_session();
    $cart = maybe_serialize($old->cart);
  } else {
    $action = 'cart-a-';
    $cart = maybe_serialize($cart);
  }
  $now = current_time('timestamp');
    
        
  // save to db
  shopper_db_save_session($id, $action, $cart, $now); 
    
  // load info from DB
  $s = shopper_db_get_session($id);
  if ($s) {
    $session->visits = $s->visits;
    $session->clicks = $s->clicks;
    $session->cart = maybe_unserialize($s->cart);
    $session->type = 'aaa';
  }   
  
  return $session;
}

// Load session
function shopper_load_session() {
  $session = new stdClass();
  
  $id = $_COOKIE['shopper'];
  
  $s = shopper_db_get_session($id);
  if ($s) {
    $session->visits = $s->visits;
    $session->clicks = $s->clicks;
    $session->cart = maybe_unserialize($s->cart);
    $session->type = CONTACTABLE;
  }    
  
  return $session;
}


// Save or create session to DB
function shopper_db_save_session($id, $post_id, $cart, $timestamp) {  
  global $wpdb;
  $wpdb->show_errors();
  
  if ($id) {
    $existing = shopper_db_get_session($id);
    if ($existing) {
      $clicks = $existing->clicks . $post_id . ',';
      
      // check if this is a new visit
      $visits = $existing->visits;
      if (shopper_is_new_visit($visits, $timestamp)) {
        $visits .= $timestamp . ',';  
      }
    } else {
      $clicks = $post_id . ',';
      $visits = $timestamp . ',';
    }
    
    return $wpdb->query( 
      $wpdb->prepare( 
      "
	      INSERT INTO wp_shopper_sessions
	      (cookie, visits, clicks, cart)
	      VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE visits=VALUES(visits), clicks=VALUES(clicks), cart=VALUES(cart)
      ", 
      array($id, $visits, $clicks, $cart)
      )
    );  
  } else {
    return false;
  }
}


// Load an entry from the session DB
function shopper_db_get_session($id) {
  if (($id) && ($id != '')) {
    global $wpdb;
    $wpdb->show_errors();
    
    $ret = $wpdb->get_results( 
	    "SELECT * FROM `wp_shopper_sessions` WHERE `cookie`='" . $id ."'"
    );
    
    return $ret[0];
  } else {
    return false;
  }
}


// Check if this is a new browsing session or not
function shopper_is_new_visit($visits, $now) {
  $old = shopper_get_last_explode(explode(',', $visits));
  
  if ($old) {  
    return ($now - $old > 60*60*NEW_SESSION_HRS); 
  } else {
    return true;
  }
}
 
 
 
// General functions
// -----------------------------------------------------------------------------
 
 

// Generate unique ID
function shopper_generateRandomString($length = 10) {
  $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

// Get the current page, post or category ID
// - $url is the full url of the post/cat/page as is in the browser
// - returns an integer:
//  - homepage: 0
//  - search: 1
//  - page: p-XXX
//  - category: c-XXX
//  - tag: c-XXX
//  - post: x-XXX
//  - not found: -1
function shopper_get_post_id() {
  // This is necessary as is, unless not working
  $url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  $url = shopper_remove_hostname($url);
    
  // search
  if (strpos($url,'?s=') !== false) {
    $ret = 1;
  } else {
    // homepage
    if ($url == "/") {
      $ret = 0;
    } else {  
      // page
      $page = get_page_by_path($url);
      if ($page) {
        $ret = "p-$page->ID";
      } else {
        // category
        $cat = shopper_remove_taxonomy($url, 'category');    
        $term = get_term_by('slug', $cat, 'category');
        if ($term) {
          $ret = "c-$term->term_id";
        } else {
          // tag
          $tag = shopper_remove_taxonomy($url, 'tag'); 
          $term = get_term_by('slug', $tag, 'post_tag');
          if ($term) {
            $ret = "t-$term->ID";
          } else {
            // post
            $slug = shopper_get_post_naked_slug($url);        
            $post = shopper_get_post_by_slug($slug);
            if ($post) {
              $ret = "x-$post->ID";
            } else {
              $ret = "-1";
            }
          }      
        }
      } 
    }
  }
  
  return $ret;
}


// Remove hostname from url
// - $url is the full Wordpress url (http://smuff.ro/article-111-11)
function shopper_remove_hostname($url) {
  $u = explode(get_home_url('/'), $url);
  return $u[1];
}

// Remove taxonomy from slug
// - $slug is the trimmed Wordpress url (/category/uncategorized)
// - $type is either 'category' or 'tag'
// - only one taxonomy must be returned: 'produse' or 'gadget'; 'produse/gadget' won't work
function shopper_remove_taxonomy($slug, $type) {
  $u = explode($type, $slug);
  $r = explode("/", $u[1]);
  return shopper_get_last_explode($r);
}

// Remove date from post slug
function shopper_get_post_naked_slug($url) {
  // remove date
  $r = explode("/", $url);
  return $r[4];
}

// Get post by name
// - $slug is the naked slug: 'ali-baba'
function shopper_get_post_by_slug($slug) {
  $args = array(
    'name' => $slug,
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 1
  );
  $post = get_posts($args);
  if ($post) {
    return $post[0];
  };
}


// Return the last item of an exploded string
function shopper_get_last_explode($explode) {
  $c = count($explode);
  return $explode[$c-2];  
}



?>
