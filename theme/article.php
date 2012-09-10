<?php

// Displaying an article
// - either if will be wrapped into a single post, or into a list of posts (archives)

?>


<?php 
  $product = get_product($post->ID);
?>


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
    <div class="thumbs">
      <?php if (is_single()) { echo display_product_thumbs($product->images); } ?>
    </div>	  
	  <div class="shopping">
	    Add To cart
	  </div>
	  <div class="excerpt">
	    <?php the_excerpt(); ?>					
	  </div>
	  <div class="body">
	    <?php if (is_single()) { the_content(); } ?>
	  </div>
	</div>
</article>

