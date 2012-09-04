<?php get_header(); ?>


<section id="content"> 
  <header>
    <?php echo get_content_title(); ?>
  </header>
  <?php 
  	if ( have_posts() ) { 
  		$count = 1;          
    	while ( have_posts() ) : the_post();		    
      	include 'article.php';                
	  		$count++; 
	  	endwhile; 
	  } else {
    	include 'not_found.php';
	  } 
	?>
</section>

<nav id="sidebar">
  <h3>Alte categorii</h3>  
  <ul>
    <li>Cadouri noi</li>
    <li>Reduceri</li>
    <li>Cele mai vandute</li>
    <li>Livrare imediata</li>
    <li>Recomandari speciale pentru tine</li>
  </ul>
</nav>

<aside id="info">
  <h3>Product info</h3>
</aside>

<aside id="shopping-info">
  <h3>Informatii shopping</h3>
</aside>


<?php get_footer(); ?>