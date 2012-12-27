<?

// A brief intorduction to Smuff 
// ----------------------------

?>


<section id="<?php echo $klass ?>"> 
  <h3>Sectiunea Meniul principal</h3>
  
  <nav>
    <h3>Meniul principal</h3>
		<ul>
			<li data-content="services">Servicii</li>
			<li data-content="gifts"><span>de</span> Cadouri</li>
			<li data-content="premium">Premium</li>
		</ul>
	</nav>
	
	<!-- the background for articles -->
	<div id="background"></div>
	
	<!-- On the archive page just the title is displayed, no menu at all. And this is the placeholder -->
	<div id="archive-title"></div>
	
	<article id="services">
	  <h3>Servicii</h3>
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
	  <h3>Cadouri</h3>
		<?php 
			$cadouri = get_category_list('Cadouri'); 
		?>
		<dl>
			<ul>
				<?php 
					foreach ($cadouri as $c) {
						// Marke the current category if it is an Archive page
        		$current = '';
        		$this_page = get_current_page_properties();
        		if ($c->title == $this_page->title) {
        			$current = ' selected';
        		}
						echo "<li class='" . $current . "' data-link='" . $c->link . "' data-count='" . $c->count . "' data-description='" . $c->description . "' data-img='" . $c->image->full . "'>";
						echo "<a href='" . $c->link . "' title='" . $c->title ." '>". $c->title . "</a>";
						echo "</li>";
					}
				?>
			</ul>
			
			<div id="image">
			</div>
		
			<div id="description">
				<p id="text"></p>
				<p id="action"><a title="" href="">Vezi cele <strong></strong> cadouri din <em></em> &#8674;</a></p>
			</div>
			
		</dl>
	</article>
	
	<article id="premium">
	  <h3>Premium</h3>
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


				
