<?php get_header(); ?>

<?php

// Displays a list of posts
// -----------------------------------------


// General variables
$this_page = get_current_page_properties();
$found_posts = $wp_query->found_posts;

?>

<section id="content"> 
  <header>
  	<h3 title="Click pentru toate categoriile"><?php echo $this_page->title ?> (<?php echo $found_posts ?>)</h3>
  </header>
  
	<aside>
		<div id="description"><p><?php echo $this_page->description ?></p></div>
		<div id="search"><?php include 'search.php' ?></div>
		<div id="filters">
			<h5>Filtrare rezultate</h5>
			<?php include '_product_filters.php' ?>
		</div>
		<div id="thumbs"></div>
	</aside>
	<div id="trigger">
		<span title="Click pentru a inchide imaginile mici si sectiunea de cautare" class='up'>&#8673;</span>
		<span title="Click pentru imaginile mici si seactiunea de cautare" class='down'>&#8675;</span>
	</div>
  
  <?php
  	if (have_posts()) {
    	// The Loop
    	$count = 1;
    	while ( have_posts() ) : the_post();		
    		$product = get_product($post->ID); 
    		
    		$article_view = 'list';
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
