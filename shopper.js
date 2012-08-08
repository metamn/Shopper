jQuery(document).ready(function() {

  // Cart
  //
  
  // Add to cart
  jQuery("#add-to-cart #submit").click(function(event) {
    // Do not submit the form the classic way, only with AJAX
    event.preventDefault();
  
    // Display Ajax loading spinner
    // jQuery(this).html(ajaxspinner);
    
    // Save this !!!
    var _this = jQuery(this);
    
    // Get query parameters
    var nonce = jQuery(this).parent().attr("data-nonce"); 
    var id = jQuery(this).parent().attr("data-id"); 
    var title = jQuery(this).parent().attr("data-title");
    var variationId = jQuery(this).parent().children('#product-variations').val();
    var variationName = '';
    var price = 0;
    jQuery(this).parent().children("#variation").each(function() {
      if (jQuery(this).attr('data-id') == variationId) {
        variationName = jQuery(this).attr('data-name');
        price = jQuery(this).attr('data-price');
      }
    });
    
    var qty = 1;
    
    // Do the ajax
    jQuery.post(
      shopper.ajaxurl, 
      {
        'action' : 'shopper_add_to_cart_ajax',
        'nonce' : nonce,
        'id' : id,
        'title' : title,
        'variation-name' : variationName,
        'variation-id' : variationId,
        'qty' : qty,
        'price' : price
      }, 
      function(response) {        
        //alert(response.message);
        _this.parent().next().html(response.message);       
      }
    );
    
  });

});
