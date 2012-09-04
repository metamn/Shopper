<?php get_header(); ?>


<section id="content"> 
  <header>
    <?php echo get_content_title(); ?>
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