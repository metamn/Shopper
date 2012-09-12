<?php

// Displays a product, whenever is single or achive
// -------------------------------------------------
//
// $article_view : 'list', 'icons' etc.


?>

<article <?php post_class($article_view); ?>>
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