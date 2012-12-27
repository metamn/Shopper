<section id="product-browser">
  <h3>Lista produse</h3>
	<aside>
	  <h3>Navigare categorii produse</h3>
		<nav id="filters">
		  <h3>Filtrare produse</h3>
			<ul>
				<li><input id="filter-products" type="checkbox" name="from_category" value="from_category" checked />Alte cadouri din <span><?php echo $category_name ?></span></li>
				<li><input id="filter-products" type="checkbox" name="latest" value="latest" />Cadouri similare</li>
			</ul>
			<?php include '_product_filters.php' ?>
		</nav>
		
		<div id='search'>
			<?php include 'search.php' ?>
		</div>
	</aside>
	
	<div id="products">
		<?php 
			$products = query_posts2('posts_per_page=2&order=DESC');
			if ($products) {
			
				$count = 1;
				while ($products->have_posts()) : $products->the_post(); update_post_caches($posts);
					$product = get_product($post->ID); 
					
					$article_view = 'image';
					include 'article.php';
					
					$count++; 	      	
      	endwhile;
      
      } else {
      	include 'not_found.php';
      }
		?>
	</div>
	<div id="scroller">
		<ul>
			<li>&#8672;</li>
			<li>&#8673;</li>
			<li>&#8674;</li>
		</ul>
	</div>
</section>
