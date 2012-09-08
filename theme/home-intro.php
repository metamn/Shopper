<?

// A brief intorduction to Smuff 
// ----------------------------

?>


<section id="<?php echo $klass ?>"> 
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
						echo "<li data-link='" . $c->link . "' data-count='" . $c->count . "' data-description='" . $c->description . "' data-img='" . $c->image . "'>" . $c->title . "</li>";
					}
				?>
			</ul>
			
			<div id="image">
				<!--<a title='' href=''><img src='' title='' /></a>-->
			</div>
			
			
			<div id="description">
				<p id="text"></p>
				<p id="action"><a title="" href="">Vezi cele <strong></strong> cadouri din <em></em> &#8674;</a></p>
			</div>
			
		</dl>
	</article>
	
	<article id="premium">
		<ul>
			<li><span>
				<?php 
					$years = (int)date('Y') - 2006; 
					echo $years;
				?>
				ani</span> de existenta.
			</li>
			<li><span>10 zile</span> returnare produse.</li>
			<li><span>365 zile</span> garantie tehnica.</li>
			<li><span>450+ de cadouri</span> in magazin.</li>
			<li><span>Peste 8,000 clienti</span> fericiti.</li>
		</ul>
	</article>
  
</section>


				