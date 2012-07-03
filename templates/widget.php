<?php 
if (!class_exists('Event_Espresso_Widget')) {
	class Event_Espresso_Widget extends WP_Widget {
		function Event_Espresso_Widget() {

			/* Widget settings. */

			$widget_options = array( 'classname' => 'events', 'description' => __('A widget to display your upcoming events.', 'events') );
			/* Widget control settings. */

			$control_options = array( 'width' => 300, 'height' => 350, 'id_base' => 'events-widget' );
			/* Create the widget. */

			$this->WP_Widget( 'events-widget', __('Event Espresso Widget', 'events'), $widget_options, $control_options );

		}
		function widget($args, $instance ) {

			extract( $args );

			global $wpdb, $org_options;
			/* Our variables from the widget settings. */

			$title = apply_filters('widget_title', $instance['title'] );
							
			/* Before widget (defined by themes). */
			echo $before_widget;

			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo $before_title . $title . $after_title;
				
				if ($instance['category_name'] != ''){
					$type = 'category';
				}

					$event_page_id =$org_options['event_page_id'];
					
					$show_expired = $instance['show_expired'] == 'false' ? " AND e.start_date >= '".date ( 'Y-m-d' )."' " : '';
					$show_secondary = $instance['show_secondary'] == 'false' ? " AND e.event_status != 'S' " : '';
					$show_deleted = $instance['show_deleted'] == 'false' ? " AND e.event_status != 'D' " : '';
					$show_recurrence = $instance['show_recurrence'] == 'false' ? " AND e.recurrence_id = '0' " : '';
					$limit = $instance['limit'] > 0 ? " LIMIT 0," . $instance['limit'] . " " : ' LIMIT 0,5 ';
					//$order_by = $order_by != 'NULL'? " ORDER BY ". $order_by ." ASC " : " ORDER BY date(start_date), id ASC ";
					$order_by = " ORDER BY date(start_date), id ASC ";
			
					if ($type == 'category'){
						$sql = "SELECT e.* FROM " . EVENTS_CATEGORY_TABLE . " c ";
						$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.cat_id = c.id ";
						$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id = r.event_id ";
						$sql .= " WHERE c.id = '" . $instance['category_name'] . "' ";
						$sql .= " AND e.is_active = 'Y' ";
					}else{
						$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
						$sql .= " WHERE e.is_active = 'Y' ";
					}
					$sql .= $show_expired;
					$sql .= $show_secondary;
					$sql .= $show_deleted;
					$sql .= $show_recurrence;
					$sql .= $order_by;
					$sql .= $limit;
	

					$events = $wpdb->get_results($sql);

					//print_r($events);
					//event_espresso_get_event_details($sql);
						foreach ($events as $event){
							$event_id = $event->id;
							$event_name = $event->event_name;
							$start_date = $event->start_date;
							$externalURL = $event->externalURL;
							$registration_url = $externalURL != '' ? $externalURL : get_option('siteurl') . '/?page_id='.$event_page_id.'&regevent_action=register&event_id='. $event_id . '&name_of_event=' . stripslashes_deep($event_name);
							?>
							<p><a href="<?php echo $registration_url;?>"><?php echo stripslashes_deep($event_name)?> - <?php echo event_date_display($start_date)?></a></p>
							<?php

						}
			/* After widget (defined by themes). */
			echo $after_widget;

			}

		/* Update the widget settings. */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['category_name'] = $new_instance['category_name'];
			$instance['show_expired'] = $new_instance['show_expired'];
			$instance['show_secondary'] = $new_instance['show_secondary'];
			$instance['show_deleted'] = $new_instance['show_deleted'];
			$instance['show_recurrence'] = $new_instance['show_recurrence'];
			$instance['limit'] = $new_instance['limit'];

			return $instance;

		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function
		 * when creating your form elements. This handles the confusing stuff.
		 **/
		function form( $instance ) {

			/* Set up some default widget settings. */

			$defaults = array( 'title' => __('Upcoming Events', 'events'), 'category_name' => __('', 'events'), 'show_expired' => __('false', 'events'), 'show_secondary' => __('false', 'events'), 'show_deleted' => __('false', 'events'), 'show_recurrence' => __('false', 'events') );

			$instance = wp_parse_args( (array) $instance, $defaults ); 
			
			$values=array(					
				array('id'=>'false','text'=> __('No','event_espresso')),
				array('id'=>'true','text'=> __('Yes','event_espresso')));
				//select_input('allow_multiple', $values, $allow_multiple);
			?>

<!-- Widget Title: Text Input -->

<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e('Title:', 'Upcoming Events'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" size="20" type="text" />
</p>
<p> <label for="<?php echo $this->get_field_id( 'category_name' ); ?>">
    <?php _e('Event Category:', 'event_espresso'); ?>
  </label><br />
 <?php echo espresso_db_dropdown(id, category_name, EVENTS_CATEGORY_TABLE, id, $instance['category_name'], $strMethod="desc", $this->get_field_name( 'category_name' )) ?></p>
 <p>
  <label for="<?php echo $this->get_field_id( 'limit' ); ?>">
    <?php _e('Limit:', 'event_espresso'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>" size="3" type="text" />
</p>
<p><strong><?php _e('Optional Settings:', 'event_espresso'); ?></strong></p>
 <p><?php _e('Show Expired Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_expired' ), $values, $instance['show_expired']);?></p>
 <p><?php _e('Show Secondary Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_secondary' ), $values, $instance['show_secondary']);?></p>
 <p><?php _e('Show Deleted Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_deleted' ), $values, $instance['show_deleted']);?></p>
 <p><?php _e('Show Recurring Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_recurrence' ), $values, $instance['show_recurrence']);?></p>
 
<?php
		}

	}

}