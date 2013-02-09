jQuery(document).ready(function() {

    // clear firefox and safari cache
    jQuery(window).unload( function() {}); 

    jQuery.ajaxSetup({
        cache: false,
        xhr: function()
        {
            if (jQuery.browser.msie)
            {
                return new ActiveXObject("Microsoft.XMLHTTP");
            }
            else
            {
                return new XMLHttpRequest();
            }
        },
        type: "POST",
        url:  EEGlobals.ajaxurl
    });


    function event_espresso_do_ajax(data, callback){

        jQuery.ajax({
            data: data,
            dataType: "json",
            success: function(response, textStatus){

                process_response(response, callback);

            },
            error: function(resp) {
                //alert("Error.");
            }
        });

    }

    function process_response(from_server, callback)
    {
        if (from_server == null){
            return false;
        }

        if (from_server.code == 1)
        {
            callback(from_server);
        }
        else
        {
            callback(null);
        }

        return;
    }


})
