<?php get_header(); ?>

<?php

// Display a list of posts, or a single post
// -----------------------------------------


// General variables
$this_page = get_current_page_properties();
$count = 1;

// Check if this is an archive page
$found_posts = $wp_query->found_posts;
if ($found_posts > 1) {
  $view = 'list';
} else {
	$view = '';
}

?>

<section id="content"> 
  <header>
  	<h3><?php echo $this_page->title ?> (<?php echo $found_posts ?>)</h3>
  </header>
  
	<aside>
		<div id="description"><?php echo $this_page->description ?></div>
		<div id="search"><?php include 'search.php' ?></div>
	</aside>
  	
  <?php
  	if (have_posts()) {
    	// The Loop
    	while ( have_posts() ) : the_post();		    
      	include 'article.php';                
	  		$count++; 
	  	endwhile; 
	  } else {
	  
	  	// Not Found
    	include 'not_found.php';
	  } 
	?>
</section>


<?php get_footer(); ?>