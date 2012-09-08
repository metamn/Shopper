<?php get_header(); 

 
// A brief description of Smuff 
// ----------------------------

?>


<section id="intro"> 
  <header>
    <?php echo get_content_title(); ?>
  </header>
  
  <nav>
		<ul>
			<li data-content="services">Servicii</li>
			<li data-content="gifts"><span>de</span> Cadouri</li>
			<li data-content="premium">Premium</li>
		</ul>
	</nav>
	
	<!-- the background for articles -->
	<aside></aside>
	
	<article id="services">
		<dl>
			<dt><a href="<?php bloginfo('home')?>/servicii/giftshopper" title="Giftshopper | Te ajutam sa alegi cadoul perfect"><em>gift</em> Shopper</a></dt>
			<dd>
				<div id="c1">
					<a href="<?php bloginfo('home')?>/servicii/giftshopper" title="Giftshopper | Te ajutam sa alegi cadoul perfect">
						<h5>Te ajutam sa alegi cadoul perfect</h5>
						<h6>Ce cadouri vrei, pentru cine, la ce pret?</h6>
					</a>
				</div>
				<div id="c2">
					<a href="<?php bloginfo('home')?>/servicii/giftshopper" title="Giftshopper | Te ajutam sa alegi cadoul perfect">
						<p>&#9794; &nbsp; &#9792;</p>
					</a>
				</div>
			</dd>
			<dt><a href="<?php bloginfo('home')?>/servicii/giftplanner" title="Giftplanner | Planifici cadouri pe tot anul"><em>gift</em> Planner</a></dt>
			<dd>
				<div id="c1">
					<a href="<?php bloginfo('home')?>/servicii/giftplanner" title="Giftplanner | Planifici cadouri pe tot anul">
						<h5>Planifici cadouri pe tot anul</h5>
						<h6>Pentru zi de nasteri, botezuri, craciun si alte evenimente.</h6>
					</a>
				</div>
				<div id="c2">
					<a href="<?php bloginfo('home')?>/servicii/giftplanner" title="Giftplanner | Planifici cadouri pe tot anul">
						<p>&#10050; &nbsp; &#10052;</p>
					</a>
				</div>
			</dd>
		</dl>
	</article>
	
	<article id="gifts">
		<?php 
			$cadouri = category_list('Cadouri'); 
		?>
		<dl>
			<ul>
				<?php 
					foreach ($cadouri as $c) {
						echo "<li data-img='" . $c->image . "'>" . $c->title . "</li>";
					}
				?>
			</ul>
			<div>
				<!--<a title='' href=''><img src='' title='' /></a>-->
			</div>
		</dl>
	</article>
	
	<article id="premium">
	</article>
  
</section>


<?php get_footer(); ?>

				