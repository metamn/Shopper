jQuery(document).ready(function() {

  // Reveal detail table in a mster detail relationship
  
  jQuery("#wpbody-content .detail h2").click(function() {
  	jQuery(this).next().slideToggle();
  
  });

});