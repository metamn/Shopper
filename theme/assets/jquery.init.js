jQuery(document).ready(function() {
	
	// Header 
	//
	
	// Menu
	// -- hide all
	jQuery("header nav ul li").removeClass('active');
 
 	// -- display the first
 	jQuery("header nav ul li").first().addClass('active');
 	
 	// -- hover
 	jQuery("header nav ul li").hover(
		function () {
			jQuery(this).addClass('hover');
		}, 
		function () {
			jQuery(this).removeClass('hover');
		}
	);
	// -- click
	jQuery("header nav ul li").click(function(){
		jQuery("header nav ul li").removeClass('active');
		jQuery(this).addClass('active');
	});
 
 	
	// Gifts
	// -- click
	jQuery("header #gifts ul li").click(function(){
		jQuery(this).addClass('active');
		
		//var img = jQuery(this).parent().next().children().children();
		//img.attr('src', jQuery(this).attr('data-img'));
		//jQuery("header aside").css('background-image', "url(" + jQuery(this).attr('data-img') + ")");
		jQuery("header #gifts div").css('background-image', "url(" + jQuery(this).attr('data-img') + ")");
	});
  
  
  
  
  // Draw the logo
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
