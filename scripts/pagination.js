$jaer = jQuery.noConflict();
jQuery(document).ready(function($jaer) {
	//$jaer('#event_container_pagination').pajinate({
	//	num_page_links_to_display : $jaer('#event_container_pagination').attr('num_page_links_to_display'),
    //    item_container_id: '.event_content',
	//	items_per_page : $jaer('#event_container_pagination').attr('events_per_page'),
    //    nav_label_first : '<<',
	//	nav_label_last : '>>',
	//	nav_label_prev : '<',
	//	nav_label_next : '>'
	//});
    $jaer('.event_paginate').live('click',function(event){
        event.preventDefault();
        
        //var sql = $jaer('#event_search_code').html(); 
        var data = $jaer('#event_search_code').attr('data');
        var current_page = $jaer(this).attr('current_page');
        
        //var data = "action=events_pagination&sql="+sql+"&css_class="+css_class+"&allow_override="+allow_override+"&events_per_page="+events_per_page+"&num_page_links_to_display="+num_page_links_to_display+"&event_page_number="+current_page;
        data = "action=events_pagination&current_page="+current_page+"&"+data;
     
        $jaer('#event_content').html("<div class='ajx_loading'>&nbsp</div>");
        $jaer.ajax({
          type: "POST",
          url: ee_pagination.ajaxurl,
          data: data,
          dataType: 'html',
          success:function(response){
              $jaer('#event_wrapper').html(response);
          }
        });
    });	
 });