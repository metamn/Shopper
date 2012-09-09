<?php get_header(); ?>


<section id="content"> 
  <header>
    <h3><?php echo get_content_title(); ?></h3>
  </header>
  <?php 
  	if ( have_posts() ) { 
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