jQuery(document).ready(function() {

	
	// Product
	// -----------------------------------------------
	
	// Display the firs pane in Product Description
	jQuery(".single #content .entry .body .pane").removeClass('active');
	jQuery(".single #content .entry .body .pane").first().addClass('active');
	
	
	// Click on Product Description content headers
	jQuery(".single #content .entry .body h3").click(function() {
		jQuery(this).next().slideToggle('slow');
	});
	
	
	// Click on close comments
	jQuery(".single #content #comments .close").click(function() {
		jQuery(this).html('Vizualizare comentarii');
		jQuery(".single #content #comments").slideToggle('slow');
	});
	
	
	
	// Archive
	// -----------------------------------------------
	
	// Display the title
	// - take it from the #content
	jQuery(".archive header #menu #archive-title").html(jQuery("section#content header").html());
	
	// Display the Main Menu
	jQuery(".archive header #menu #archive-title").click(function() {
		jQuery(".archive header #menu article").slideToggle('slow');
	});
	
	
	// Home
	// -----------------------------------------------
	
	// Intro
	//
	
	// Nav
	// -- hide all
	jQuery("#intro nav ul li").removeClass('active');
	jQuery("#intro article").removeClass('active');
 
 	// -- display the first
 	jQuery("#intro nav ul li").first().addClass('active');
 	jQuery("#intro #services").addClass('active');
 	
 	// -- hover
 	jQuery("#intro nav ul li").hover(
		function () {
			jQuery(this).addClass('hover');
		}, 
		function () {
			jQuery(this).removeClass('hover');
		}
	);
	// -- click
	jQuery("#intro nav ul li").click(function(){
		jQuery("#intro nav ul li").removeClass('active');
		jQuery("#intro article").removeClass('active');
		
		jQuery(this).addClass('active');
		
		var contentID = jQuery(this).attr('data-content');
		jQuery("#intro #" + contentID).addClass('active');
	});
 
 	
	// Gifts
	// -- hide all
	jQuery("#intro #gifts ul li").removeClass('active');
 
 	// -- display the first
 	jQuery("#intro #gifts ul li").first().addClass('active');
 	intro_gifts_update(jQuery("#intro #gifts ul li").first());
 	
 	// -- hover
 	jQuery("#intro #gifts ul li").hover(
		function () {
			jQuery(this).addClass('hover');
		}, 
		function () {
			jQuery(this).removeClass('hover');
		}
	);
	// -- click
	jQuery("#intro #gifts ul li").click(function(event){
		jQuery("#intro #gifts ul li").removeClass('active');
		jQuery(this).addClass('active');
		
		intro_gifts_update(jQuery(this));
		event.preventDefault();
	});
	
	// Updates image, description, links etc when browsing gift categories
	function intro_gifts_update(item) {
		jQuery("#intro #gifts #image").css('background-image', "url(" + item.attr('data-img') + ")");
		jQuery("#intro #gifts #description #text").html(item.attr('data-description'));
		jQuery("#intro #gifts #description #action strong").html(item.attr('data-count'));
		jQuery("#intro #gifts #description #action em").html(item.html());
		
		jQuery("#intro #gifts #description #action a").attr('href', item.attr('data-link'));
		jQuery("#intro #gifts #description #action a").attr('title', jQuery("#intro #gifts #description #action a").html());
	}
	
	
  
	// Header
	// -----------------------------------------------
	
	// Cart & Info
	//
	jQuery("header #cart-and-info ul li").click(function() {
		jQuery(this).children('ul').slideToggle('slow');
	});
	

	// Menu
	//
	
	jQuery("header #menu nav ul li").click(function() {
		jQuery("header #menu article").slideToggle('slow');
		jQuery("header #menu nav ul li").toggleClass('active');
	});
	
	
	// Menu > Categories
	//
	
 	// -- display the first
 	jQuery("header #menu #gifts ul li").first().addClass('hover');
 	menu_gifts_update(jQuery("#intro #gifts ul li").first());
 	
 	// -- hover
 	jQuery("header #menu  #gifts ul li").hover(
		function () {
			jQuery(this).addClass('hover');
			menu_gifts_update(jQuery(this));
		}, 
		function () {
			jQuery(this).removeClass('hover');
		}
	);
	
	// Updates image, description, links etc when browsing gift categories
	function menu_gifts_update(item) {
		jQuery("header #menu  #gifts #description #text").html(item.attr('data-description'));
		jQuery("header #menu  #gifts #description #action strong").html(item.attr('data-count'));
		jQuery("header #menu  #gifts #description #action em").html(item.html());
		
		jQuery("header #menu  #gifts #description #action a").attr('href', item.attr('data-link'));
		jQuery("header #menu  #gifts #description #action a").attr('title', jQuery("#intro #gifts #description #action a").html());
	}
	
	
  
  // Logo
  //
  
  function logo() {
    var matrix = new Array(7);
    for (y = 0; y < 7; y++) {
      matrix[y] = new Array(24);
      for (x = 0; x < 24; x++) {
        matrix[y][x] = '';
      }
    }
    
    matrix[6][0] = 'set';
    
    matrix[6][1] = 'set';
    
    matrix[6][2] = 'set';
    
    matrix[6][3] = 'set';
    
    matrix[6][4] = 'set';
    
    matrix[0][5] = 'set';
    matrix[1][5] = 'set';
    matrix[2][5] = 'set';
    matrix[3][5] = 'set';
    matrix[6][5] = 'set';
    
    matrix[0][6] = 'set';
    matrix[3][6] = 'set';
    matrix[6][6] = 'set';

    matrix[0][7] = 'set';
    matrix[3][7] = 'set';
    matrix[4][7] = 'set';
    matrix[5][7] = 'set';
    matrix[6][7] = 'set';
    
    matrix[0][8] = 'set';
    
    matrix[0][9] = 'set';
    
    matrix[0][10] = 'set';
    
    matrix[0][11] = 'set';
    matrix[1][11] = 'set';
    matrix[2][11] = 'set';
    matrix[3][11] = 'set';
    
    matrix[0][12] = 'set';
    
    matrix[0][13] = 'set';
    matrix[1][13] = 'set';
    matrix[2][13] = 'set';
    matrix[3][13] = 'set';
    
    matrix[0][14] = 'set';
    
    matrix[0][15] = 'set';
    matrix[1][15] = 'set';
    matrix[2][15] = 'set';
    matrix[3][15] = 'set';
    
    matrix[3][16] = 'set';
    
    matrix[1][17] = 'set';
    matrix[2][17] = 'set';
    matrix[3][17] = 'set';
        
    matrix[0][19] = 'set';
    matrix[1][19] = 'set';
    matrix[2][19] = 'set';
    matrix[3][19] = 'set';
    matrix[4][19] = 'set';
    matrix[5][19] = 'set';
    matrix[6][19] = 'set';
    
    matrix[0][20] = 'set';
    matrix[4][20] = 'set';    
    
    matrix[0][22] = 'set';
    matrix[1][22] = 'set';
    matrix[2][22] = 'set';
    matrix[3][22] = 'set';
    matrix[4][22] = 'set';
    matrix[5][22] = 'set';
    matrix[6][22] = 'set';
    
    matrix[0][23] = 'set';
    matrix[4][23] = 'set';
        
    var ret = "";
    var size = "";
    for (y = 0; y < 7; y++) {
      for (x = 0; x < 24; x++) {        
        switch(x) {
          case 18:
            size = 'small';
            break;          
          case 21:
            size = 'small';
            break;
          default:
            size = '';
        }      
        ret += "<div id='cell-" + x + "-" + y + "' class='cell " + size + matrix[y][x] + "'></div>";
      }
    }
    
    return ret;  
  }  
  jQuery("#logo").html(logo());
  
  
  
  // General functions
  //
  
  // Display the responsive image
  jQuery('noscript[data-large][data-small]').each(function(){
    var src = screen.width >= 500 ? jQuery(this).data('large') : jQuery(this).data('small');
    jQuery('<img src="' + src + '" alt="' + $(this).data('alt') + '" />').insertAfter($(this));
  });
  
});
