jQuery(document).ready(function() {

	// Mark variations when Product is changed in Supplier Needs
	jQuery("#shopper-supplier_needs #edit #product_id").bind('change', function() {
	
		var item = jQuery("option:selected", this);
		
		var value = item.attr('data-variation_id');
		jQuery(this).parent().parent().parent().append("<input type='hidden' id='variation_id' name='variation_id' value='" + value + "' />");
		
	});


	// Populate Variations when a Product is selected in Order Items
	// - #BUG::: jquery cannot chnage the value of a hidden field ...
	// - workaround: input type hidden will be inserted again ...
	jQuery("#shopper-order_items #edit #product_name").bind('change', function() {
	
		var item = jQuery("option:selected", this);
		
		var value = item.attr('data-postid');
		jQuery(this).parent().parent().parent().append("<input type='hidden' id='product_post_id' name='product_post_id' value='" + value + "' />");
		
		var value = item.attr('data-variationname');
		jQuery(this).parent().parent().parent().append("<input type='hidden' id='product_variation_name' name='product_variation_name' value='" + value + "' />");
		
		var value = item.attr('data-variationid');
		jQuery(this).parent().parent().parent().append("<input type='hidden' id='product_variation_id' name='product_variation_id' value='" + value + "' />");
		
		var value = item.attr('data-price');
		jQuery(this).parent().parent().parent().append("<input type='hidden' id='product_price' name='product_price' value='" + value + "' />");
		
		var value = item.attr('data-stock');
		jQuery("#shopper-order_items #edit .stock").html("Stoc: " + value);
		
		var importLink = "?page=shopper-supplier_needs&action=edit&post_id=" + item.attr('data-postid') + "&variation_id=" + item.attr('data-variationid');
		jQuery("#shopper-order_items #edit a#import").attr("href", importLink);
		
	});

  // Reveal detail table in a master detail relationship
  jQuery("#wpbody-content .detail h2").click(function() {
  	jQuery(this).next().slideToggle();
  
  });

});