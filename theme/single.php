<?php get_header(); ?>

<?php

// Displaying a single post / product
// ----------------------------------
//
// - it is composed by a standard 'article' and some other sections

?>

<section id="content">
<?php 
	if (have_posts()) { 
		while ( have_posts() ) : the_post();    
    	include 'article.php';
    	// include .....
  	endwhile; 
	} else { 
		include 'not_found.php';
	} 
?>
</section>

<?php get_footer(); ?>