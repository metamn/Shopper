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
  	<h3><?php echo $this_page->title ?> (<?php echo $found_posts ?>)</h3>
  </header>
  
	<aside>
		<div id="description"><p><?php echo $this_page->description ?></p></div>
		<div id="search"><?php include 'search.php' ?></div>
		<div id="navigation">
			<h5>Filtrare rezultate</h5>
			<ul>
				<li>Cadouri noi</li>
				<li>Recomandari speciale pentru mine</li>
				<li>Bestsellers</li>
				<li>Reduceri</li>
			</ul>
		</div>
		<div id="thumbs"></div>
	</aside>
	<div id="trigger">
		<span class='up'>&#8673;</span>
		<span class='down'>&#8675;</span>
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