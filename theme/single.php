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
  		$product = get_product($post->ID); 
			$article_view = '';
			
			include 'article.php';
			
    	include '_shopping_incentives.php';
    	
    	include '_add_to_cart.php';
    	include '_add_to_wishlist.php';
    	include '_subscribe_to_newsletter.php';
    	include '_share.php';
    	
    	include '_product_browser.php';
    	
    	$klass = 'intro';
    	include 'home-intro.php';
  	endwhile; 
	} else { 
		include 'not_found.php';
	} 
?>
</section>

<?php get_footer(); ?>