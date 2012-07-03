

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
            event_name : jQuery(this).attr('title'),
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
        /*
         * Copies info from one section of the form to another.  Will help the user so
         * they don't have to enter info again.
         */

        var val = jQuery(this).val().split('|');


        var to_event_id = val[0];
        var to_price_id = val[1];
        var to_attendee_no = val[2];


        if (jQuery('.event_espresso_copy_info option:selected').text() == "CLEAR FIELDS"){
         
            jQuery('#multi_regis_form_fields-' + to_event_id + "-" + to_price_id + " :input[name*='[" + to_event_id + "][" + to_price_id + "][" + to_attendee_no + "]']").each(function(){


                var type = jQuery(this).attr('type');

                if (type == 'radio' || type == 'checkbox' ){

                    jQuery(this).attr("checked", false);
                } else jQuery(this).val('');

            });
            jQuery(this).val('');
            return false;
        }

        var from_event_id = val[3];
        var from_price_id = val[4];
        var from_attendee_no = val[5];

        jQuery('#multi_regis_form_fields-' + to_event_id + "-" + to_price_id + " :input[name*='[" + to_event_id + "][" + to_price_id + "][" + to_attendee_no + "]']").each(function(){
            //console.log(jQuery(this).attr('name') + ' > ' + jQuery(this).val());
            var val = jQuery(this).val();
            var name = jQuery(this).attr('name');
            var input_type = jQuery(this).attr('type');


            var copy_field_name = name.replace(/(\[\d+\])(\[\d+\])(\[\d+\])/,"[" + from_event_id + "][" + from_price_id + "][" + from_attendee_no + "]");

            // alert(copy_field_name);

            var copy_from = jQuery(":input[name='" + copy_field_name + "']");

            // console.log(jQuery(this).attr('name') + ' > ' + copy_field_name + ' > ' + copy_from.val());
            switch (input_type)
            {
                case 'text':
                case 'textarea':
                    jQuery(this).val(copy_from.val());
                    break;
                case 'radio':
                case 'checkbox':

                    if (copy_from.attr('checked') && val == copy_from.val())
                        jQuery(this).attr("checked", "checked");

                    break;
                default:
                    jQuery(this).val(copy_from.val());
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
