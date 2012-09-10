<?php get_header(); ?>

<?php

// Display a single post or a list of posts
// - usually a list of posts has all the same data as a single post (images, add to cart, etc)
// - so there is no need for 'archive.php'

?>

<section id="content"> 
  <header>
    <h3><?php echo get_content_title(); ?></h3>
  </header>
  <?php 
  	if ( have_posts() ) {
  		// When is a list of posts this helps to organize them into columns
  		$count = 1;          
    	while ( have_posts() ) : the_post();		    
      	include 'article.php';                
	  		$count++; 
	  	endwhile; 
	  } else {
    	include 'not_found.php';
	  } 
	?>
</section>


<?php get_footer(); ?>