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
  		$product = get_product($post->ID); ?>

			<article <?php post_class(); ?>>
				<header>
					<h1>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute() ?>" rel="bookmark">
							<?php the_title(); ?>
						</a>
					</h1>
				</header>
				<div class="entry">	    
					<div class="featured-image" data-id="<?php echo $post->ID ?>" data-nonce="<?php echo wp_create_nonce('load-post-details') ?>">
						<?php echo make_responsive_image($product->images[0], get_the_title()); ?>
					</div>
					<div class="shopping">
						<div id="add-to-cart">Nume variatie &mdash; 1250 RON &nbsp;&nbsp;&nbsp; [Adauga la cos]</div>
					</div>
					<div class="more">
						Detalii si imagini &#8675;
					</div>
					<div class="excerpt">
						<?php the_excerpt(); ?>					
					</div>
					<div class="thumbs">
						<?php echo display_product_thumbs($product->images); ?>
					</div>	 
					<div class="body">
						<?php if (is_single()) { 
							the_content(); ?>
							
							<h3>Comentarii</h3>
							<div class="pane">
								<?php comments_template(); ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<footer>
					<h1>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute() ?>" rel="bookmark">
							<?php the_title(); ?>
						</a>
					</h1>
					
					<div class="shopping">
						<div id="add-to-cart">Nume variatie &mdash; 1250 RON &nbsp;&nbsp;&nbsp; [Adauga la cos]</div>
					</div>
				</footer>
			</article>
    	
    	
    	<?php
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