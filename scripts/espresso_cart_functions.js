

jQuery(document).ready(function() {

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

    var EECART = {


        progress: function(container){

            jQuery(container).html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');

        },
        add_item : function(params){
            var _eecart = this;
            _eecart.progress(params.container);
            var data = {
                action: 'event_espresso_add_item',
                regevent_action: "event_espresso_add_item",
                item_type : params.item_type,
                id: params.id,
                name: params.event_name,
                event_page_id: EEGlobals.event_page_id
            };

            event_espresso_do_ajax( data, function(r){

                params.container.html(r.html);

            }) ;

        },

        delete_item : function(params){

            var _eecart = this;
            _eecart.progress(params.loader_container);
            var data = {
                action: 'event_espresso_delete_item',
                item_type : params.item_type,
                id: params.id,
                name: params.event_name
            };

            event_espresso_do_ajax( data, function(r){

                params.main_container.slideUp('fast', function(){
                    params.main_container.remove();
                    
                }).delay(1500).queue(function() {
                        
                });
                _eecart.calculate_total();
            }) ;

        },
        
        calculate_total: function(grand_total){
            var _eecart = this;
            _eecart.progress(jQuery('#event_total_price'));

            if (grand_total){
                jQuery('#event_total_price').html(grand_total);
                return;
            }

            var data = "action=event_espresso_calculate_total&" + jQuery("#event_espresso_shopping_cart").serialize()

            
            event_espresso_do_ajax(data,function(r){

                jQuery('#event_total_price').html(r.grand_total);

            });
            
            
        }



    };

    jQuery('.ee_add_item_to_cart').click(function(){

        var data = {
            item_type: 'event',
            id : jQuery(this).attr('id'),
            name : jQuery(this).attr('title'),
            container : jQuery(this).parent()

        }

        EECART.add_item(data);
       
        return false;


    });

    jQuery('.ee_delete_item_from_cart').click(function(){

        var data = {
            item_type: 'event',
            id : jQuery(this).attr('id'),
            loader_container: jQuery(this).parent(),
            main_container : jQuery(this).parents('.multi_reg_cart_block')

        }

        EECART.delete_item(data);

        return false;


    });

    jQuery('#event_espresso_refresh_total').click(function(){

        EECART.calculate_total()

        return false;

    });


    jQuery('#event_espresso_checkout_form').submit(function(){

        var data = "action=event_espresso_update_item&" + jQuery(this).serialize()

        //progress(jQuery('#event_total_price'));
        /*event_espresso_do_ajax(data,function(r){

               //return true;

            });
    //return false;


    /*jQuery(this).validate({

                        submitHandler: function(form) {
                            form.submit();
                        }

                    });*/
    //return false;
    });


    jQuery('#event_espresso_shopping_cart').submit(function(e){


        //var data = "action=event_espresso_calculate_total&" + jQuery(this).serialize()

        //progress(jQuery('#event_total_price'));
        //event_espresso_do_ajax(data,jQuery('#event_total_price'),'');
        //return false;
        });




    jQuery('#event_espresso_shopping_cart :input[id^="price_option-"], .price_id, #event_espresso_coupon_code ').change(function(){

        EECART.calculate_total()

    });
    jQuery('#event_espresso_continue_registration').click(function(){

        /* var data = "action=event_espresso_load_regis_form&" + jQuery('#event_espresso_shopping_cart').serialize()

                    progress(jQuery('#event_espresso_multi_regis_form'));
                    event_espresso_do_ajax(data,jQuery('#event_espresso_multi_regis_form'),'');
                     */


        //return false;
        });

    jQuery('#event_espresso_confirm_pay').click(function(){

        var data = "action=event_espresso_confirm_and_pay&" + jQuery('#event_espresso_shopping_cart').serialize()


        progress(jQuery('#temp'));
        event_espresso_do_ajax(data,jQuery('#temp'),'');
        return false;
    });



    jQuery('.event_espresso_copy_info').live('change', function(){


        var val = jQuery(this).val().split('|');

        var from = val[0];
        var to = val[1];


        jQuery('#multi_regis_form_fields-' + from + " :input").each(function(){
            //console.log(jQuery(this).attr('name') + ' > ' + jQuery(this).val());
            var val = jQuery(this).val();
            var name = jQuery(this).attr('name');
            var input_type = jQuery(this).attr('type');

            var new_name = name.replace(/\[\d+\]/,"[" + to + "]");

            var new_field = jQuery('#multi_regis_form_fields-' + to + " :input[name='" + new_name + "']");



            switch (input_type)
            {
                case 'text':
                case 'textarea':
                    new_field.val(val);
                    break;
                case 'radio':
                case 'checkbox':
                    //console.log(jQuery(this).attr('name') + ' > ' + jQuery(this).val()+ ' > ' + new_name + ' > ' + input_type);
                    //alert(jQuery(this).attr('checked'));
                    //if (val == new_field.val())
                    // $(':input[name="SINGLE_16[128]"][value="' + val  + '"]').attr("checked", "checked");
                    if (jQuery(this).attr('checked'))
                        jQuery('#multi_regis_form_fields-' + to + " :input[name='" + new_name + "'][value='" + val  + "']").attr("checked", "checked");
                    //new_field.attr('checked','checked');


                    break;
                default:
                    new_field.val(val);
            }



        //console.log(jQuery('#multi_regis_form_fields-' + to + " input[name='" + new_name + "']").val());

        //

        });
        jQuery(this).val('');


    });

    function event_espresso_do_ajax(data, callback){
        
        jQuery.ajax({
            data: data,
            dataType: "json",
            success: function(response, textStatus){
                
                process_response(response, callback);

            },
            error: function(resp) {
                alert("Error.");
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
