<?php

// Displaying an article
// - either it will be wrapped into a single post, or into a list of posts (archives)

// Decide if it is single page or index page
  if (is_single()) {
    $klass = '';
    // $title = get_the_title();
  } else {
    $klass = $view;
    // $title = $product->title;
  } 
  
  // See if this is the first product in a list or not
  if (isset($count)) {
  	if ($count > 1) {
			$klass .= ' not-first';
			if ($count % 2 == 1 ) {
				$klass .= ' odd';
			}
		}
		// Identify posts with a number / counter
  	$klass .= " count-$count"; 	
 }  

?>


<?php 
  $product = get_product($post->ID);
?>


<article <?php post_class($klass); ?>>
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
	    <?php include '_add_to_cart.php' ?>
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
	</footer>
</article>

