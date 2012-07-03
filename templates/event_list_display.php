<?php 
//This is the event list template page.
//This is a template file for displaying an event lsit on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
/*
* use the following shortcodes in a page or post:
* [EVENT_LIST]
* [EVENT_LIST limit=1]
* [EVENT_LIST show_expired=true]
* [EVENT_LIST show_deleted=true]
* [EVENT_LIST show_secondary=true]
* [EVENT_LIST show_recurrence=true]
* [EVENT_LIST category_identifier=your_category_identifier]
*
* Example:
* [EVENT_LIST limit=5 show_recurrence=true category_identifier=your_category_identifier]
*
*/
	
?>
<div id="event_data-<?php echo $event_id?>" class="event_data">
				<h3 id="event_title-<?php echo $event_id?>" class="event_title"><a title="<?php echo stripslashes_deep($event_name)?>" class="a_event_title" id="a_event_title-<?php echo $event_id?>" href="<?php echo $registration_url; ?>"><?php echo stripslashes_deep($event_name)?></a></h3>                
                             
				<p id="p_event_price-<?php echo $event_id?>">
				<?php echo __('Price: ','event_espresso') . event_espresso_get_price($event_id);?>
				</p>
				 
                <p id="event_date-<?php echo $event_id?>"><?php _e('Start Date:','event_espresso'); ?>  <?php echo event_date_display($start_date, 'M d, Y')?> <br /> <?php _e('End Date:','event_espresso'); ?> <?php echo event_date_display($end_date, 'M d, Y')?></p>

                <p class="event_address" id="event_address-<?php echo $event_id?>"><?php echo __('Address:','event_espresso'); ?> <br />
						<?php echo stripslashes_deep($location); ?><br />
                        <?php echo $google_map_link; ?>
					</p>

				<p><?php echo espresso_show_social_media($event_id, 'twitter');?> <?php echo espresso_show_social_media($event_id, 'facebook');?></p>
                
                <?php 

	$num_attendees = get_number_of_attendees_reg_limit($event_id, 'num_attendees');//Get the number of attendees. Please visit http://eventespresso.com/forums/?p=247 for available parameters for the get_number_of_attendees_reg_limit() function.
	if ($num_attendees >= $reg_limit  ){?>
				<p id="available_spaces-<?php echo $event_id?>"><?php _e('Available Spaces:','event_espresso')?> <?php echo get_number_of_attendees_reg_limit($event_id, 'available_spaces', 'All Seats Reserved')?></p>
							   
				<p id="register_link-<?php echo $overflow_event_id?>"><a class="a_register_link" id="a_register_link-<?php echo $overflow_event_id?>" href="<?php echo get_option('siteurl')?>/?page_id=<?php echo $event_page_id?>&regevent_action=register&event_id=<?php echo $overflow_event_id?>&name_of_event=<?php echo stripslashes_deep($event_name)?>" title="<?php echo stripslashes_deep($event_name)?>"><?php _e('Join Waiting List','event_espresso'); ?></a></p> 
<?php
	}else{
?>
		<p id="available_spaces-<?php echo $event_id?>"><?php _e('Available Spaces:','event_espresso')?> <?php echo get_number_of_attendees_reg_limit($event_id, 'available_spaces')?></p>
							   
				<p id="register_link-<?php echo $event_id?>"><a class="a_register_link" id="a_register_link-<?php echo $event_id?>" href="<?php echo get_option('siteurl')?>/?page_id=<?php echo $event_page_id?>&regevent_action=register&event_id=<?php echo $event_id?>&name_of_event=<?php echo stripslashes_deep($event_name)?>" title="<?php echo stripslashes_deep($event_name)?>"><?php _e('Register Online','event_espresso'); ?></a></p> 
<?php
	}
	
	?>
</div>