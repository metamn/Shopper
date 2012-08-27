jQuery(document).ready(function() {

	// Populate Stock available and Price when variation is selected
	jQuery("#shopper-order_items #edit #product_variation_name").change(function() {
  	alert("price");
	});

	// Populate Variations when a Product is selected
	jQuery("#shopper-order_items #edit #product_name").change(function() {
  	var postID = jQuery("option:selected", this).val();
  	var nonce = jQuery(this).attr("data-nonce");
  	var fieldName = jQuery(this).attr("id");
  	
  	var variationField = jQuery("#shopper-order_items #edit #product_variation_name").parent();
  	
  	
  	// Do the ajax
    jQuery.post(
      shopper.ajaxurl, 
      {
        'action' : 'shopper_get_product_variations',
        'nonce' : nonce,
        'postid' : postID,
        'fieldid' : fieldName
      }, 
      function(response) {        
        variationField.html(response.variations);
      }
    );  
  	
	});

  // Reveal detail table in a master detail relationship
  jQuery("#wpbody-content .detail h2").click(function() {
  	jQuery(this).next().slideToggle();
  
  });

});