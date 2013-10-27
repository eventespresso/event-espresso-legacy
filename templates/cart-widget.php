<?php
if (!class_exists('Espresso_CartWidget')) {
	class Espresso_CartWidget extends WP_Widget {
	
		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
					'event-cart', // Base ID
					'Event Espresso Cart Widget', // Name
					array('classname' => 'widget_espresso_cart', 'description' => __('An event shopping cart widget.', 'text_domain'),) // Args
			);
		}
	
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {
			if (empty($_SESSION['espresso_session']['events_in_session'])){return;}
			extract($args);
			global $org_options;
			$grand_total = !empty($_SESSION['espresso_session']['grand_total']) ? $_SESSION['espresso_session']['grand_total'] : 0.00;
			$title = apply_filters('widget_title', $instance['title']);
			echo $before_widget;
			echo $before_title . $title . $after_title;
			//Debug
			//echo '<h4>$VARIABLE : <pre>' . print_r($_SESSION['espresso_session']) . '</pre> <span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				//echo '<p><strong>Events in cart:</strong> '.count($_SESSION['espresso_session']['events_in_session']).'</p><br />';
				echo '<ol>';
				foreach ($_SESSION['espresso_session']['events_in_session'] as $event){
					echo '<li>'.$event['event_name'].'</li>';
				}
				echo '</ol>';
				echo '<br /><p class="widget_cart_total"><strong>Total:</strong> '.$org_options['currency_symbol'].'<span id="event_total_price_widget">'.number_format( $grand_total, 2, '.', '' ).'</span></p>';
				echo '<p class="widget_cart_link"><a href="'.get_permalink($org_options['event_page_id']).'?regevent_action=show_shopping_cart">View Cart</a></p>';
				echo $after_widget;
		}
	
				/**
				 * Sanitize widget form values as they are saved.
				 *
				 * @see WP_Widget::update()
				 *
				 * @param array $new_instance Values just sent to be saved.
				 * @param array $old_instance Previously saved values from database.
				 *
				 * @return array Updated safe values to be saved.
				 */
				public function update($new_instance, $old_instance) {
					 $instance = $old_instance;
					 $instance['title'] = strip_tags($new_instance['title']);
	
					return $instance;
				}
	
				/**
				 * Back-end widget form.
				 *
				 * @see WP_Widget::form()
				 *
				 * @param array $instance Previously saved values from database.
				 */
				public function form($instance) {
					$defaults = array('title' => __('Event Cart', 'event_espresso'));

                	$instance = wp_parse_args((array) $instance, $defaults);
					?>
				 <p>
					<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:', 'event_espresso'); ?>
					</label>
					<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" size="20" type="text" />
				</p>
			<?php
				}
	
	}
}