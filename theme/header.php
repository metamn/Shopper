<?php 
  // Cookies must be set here otherwise a warning will be throw
  // http://stackoverflow.com/questions/2658083/setcookie-cannot-modify-header-information-headers-already-sent
  
  //$session = manage_session(); 
?>  

<!DOCTYPE html>

<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>
    <?php
	    /*
	     * Print the <title> tag based on what is being viewed.
	     */
	    global $page, $paged;

	    wp_title( '|', true, 'right' );

	    // Add the blog name.
	    bloginfo( 'name' );

	    // Add the blog description for the home/front page.
	    $site_description = get_bloginfo( 'description', 'display' );
	    if ( $site_description && ( is_home() || is_front_page() ) )
		    echo " | $site_description";

	    // Add a page number if necessary:
	    if ( $paged >= 2 || $page >= 2 )
		    echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );

	    ?>
    </title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

    <!-- Compass -->
    <link href="<?php bloginfo('stylesheet_directory')?>/assets/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <link href="<?php bloginfo('stylesheet_directory')?>/assets/print.css" media="print" rel="stylesheet" type="text/css" />
    <!--[if IE]>
        <link href="<?php bloginfo('stylesheet_directory')?>/assets/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <![endif]-->
    
    <link href="http://fonts.googleapis.com/css?family=Old+Standard+TT:400,400italic,700" rel="stylesheet" type="text/css">
            
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="<?php bloginfo('stylesheet_directory')?>/assets/jquery.init.js"></script>
    
    <?php wp_head(); ?>
  </head>
  
  <body <?php body_class(); ?>>     
    
    <div class="container">
    
      <header>
        <hgroup>
          <h1>
            <?php bloginfo( 'name' ); ?>
          </h1>
          <h2>
            <?php bloginfo( 'description' ); ?>
          </h2> 
         
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
            <div id="logo"></div>       
          </a>
        </hgroup>   
        
        <nav>
    			<h3><?php bloginfo( 'description' ); ?></h3>
    			<ul>
    				<li>
    					Servicii
							<dl id="services">
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
    				</li>
    				<li>
    					<span>de</span> Cadouri
    					<?php 
								$cadouri = category_list('Cadouri'); 
							?>
							<dl id="gifts">
								<ul>
									<?php 
										foreach ($cadouri as $c) {
											echo "<li data-img='" . $c->image . "'>" . $c->title . "</li>";
										}
									?>
								</ul>
								<div>
									<a title='aaa' href='aaa'><img src='aaa' title='aaa' /></a>
								</div>
							</dl>
    				</li>
    				<li>
    					Premium
							<dl>
								<dt>Cadouri Unice</dt>
								<dd>her comes a div with lots of stuff my man</dd>
								<dt>Shopping si suport perfect</dt>
								<dd>her comes a div with lots of stuff my man</dd>
							</dl>
    				</li>
    			</ul>
    		</nav>
    		
    		<aside>
    		</aside>
      </header>
    
    	
  
