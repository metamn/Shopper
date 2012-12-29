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
    
    <link href='http://fonts.googleapis.com/css?family=Lekton:400italic,700,400|Old+Standard+TT:400,400italic,700' rel='stylesheet' type='text/css'>
            
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="<?php bloginfo('stylesheet_directory')?>/assets/jquery.init.js"></script>
    
    <?php wp_head(); ?>
    
    <?php 
      // Global variables
      
      global $site_name, $site_description, $site_title, $site_url;
      
      $site_name = get_bloginfo('name', 'display');
      $site_description = get_bloginfo('description', 'display');
      $site_title = $site_name . ' | ' . $site_description;
      $site_url = home_url('/');
    ?>
  </head>
  
  
  
  
  <body <?php body_class(); ?>>     
    
    <div class="container">
    
      <header id="header">
        <hgroup>
          <h1>
            <?php echo $site_name; ?>
          </h1>
          <h2>
            <?php echo $site_description; ?>
          </h2> 
         
          <a href="<?php echo $site_url; ?>" title="<?php echo $site_title; ?>" rel="home">
            <div id="logo"></div>       
          </a>
        </hgroup>  
        
        <?php 
        	$klass = 'menu';
        	include '_menu.php' 
        ?>
        
        <nav id="cart-and-info">
          <h3>Cos cumparaturi si informatii</h3>
        	<ul>
        		<li>Cos cumparaturi
        			<ul>
        				<li>10 cadouri</li>
        				<li>85320.00 RON</li>
        			</ul>
        		</li>
        		<li>Contul meu</li>
        		<li>Informatii</li>
        	</ul>
        </nav>
       </header>
    
    	
  
