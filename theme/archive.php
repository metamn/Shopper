<?php get_header(); ?>

<?php

// Display a list of posts
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
		<div id="description"><?php echo $this_page->description ?></div>
		<div id="search"><?php include 'search.php' ?></div>
	</aside>
  	
  <?php
  	if (have_posts()) {
    	// The Loop
    	$count = 1;
    	while ( have_posts() ) : the_post();		
    		$product = get_product($post->ID); ?>
    	
				<article <?php post_class('view')?>>
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
	  		$count++; 
	  	endwhile; 
	  } else {
	  
	  	// Not Found
    	include 'not_found.php';
	  } 
	?>
</section>


<?php get_footer(); ?>