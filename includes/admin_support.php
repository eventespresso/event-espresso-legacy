<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

function event_espresso_support() {
	?>

	<div class="wrap">
	  <div id="icon-options-event" class="icon32"></div>
	  <h2>
		<?php _e('Help and Support', 'event_espresso'); ?>
	  </h2>
		<?php
		if (isset($_REQUEST['action'])) {
			switch ($_REQUEST['action']) {
				case "update_event_dates":
					update_event_data();
					break;
				case "event_espresso_update_attendee_data":
					event_espresso_update_attendee_data();
					break;
			}
		}
		?>
	<?php ob_start(); ?>
		<div class="meta-box-sortables ui-sortable">
			<ul id="event_espresso-sortables" class="help-support">
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Quick Links', 'event_espresso'); ?>
							</h3>
							<div class="inside">
								<div class="padding">
									<ul id="quick-links">
										<li><a href="#install">
												<?php _e('Installation', 'event_espresso'); ?>
											</a></li>
										<li><a href="#plugins">
												<?php _e('Recommended Plugins', 'event_espresso'); ?>
											</a></li>
										<li><a href="#hire_devs">
												<?php _e('Hire a Developer', 'event_espresso'); ?>
											</a></li>
										<li><a href="#theme_devs">
												<?php _e('Favorite Theme Developers', 'event_espresso'); ?>
											</a></li>
										<li><a href="#themes">
												<?php _e('Highly Recommended Themes', 'event_espresso'); ?>
											</a></li>
										<li><a href="#resources">
												<?php _e('Other Resources', 'event_espresso'); ?>
											</a></li>
										<li><a href="#shortcodes">
												<?php _e('Shortcodes', 'event_espresso'); ?>
											</a></li>
										<li><a href="#details">
												<?php _e('Important Information', 'event_espresso'); ?>
											</a></li>
										<li><a href="#support">
												<?php _e('Contact Support', 'event_espresso'); ?>
											</a></li>
										<li><a href="#faq">
												<?php _e('Frequently Asked Questions', 'event_espresso'); ?>
											</a></li>
										<li><a href="#additonal">
	<?php _e('Additional Information', 'event_espresso'); ?>
											</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li><a name="install" id="install"></a>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Installation', 'event_espresso'); ?>
							</h3>
							<div class="inside">
								<div class="padding">
									<p>
	<?php _e('For the latest installation instructions please visit:', 'event_espresso'); ?>
										<a href="http://eventespresso.com/wiki/installing-event-espresso/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=latest+installation+instructions<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">http://eventespresso.com/wiki/installing-event-espresso/</a></p>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Event Marketing Resources', 'event_espresso'); ?>
								<a name="hire_promo" id="hire_promo"></a></h3>
							<div class="inside">
								<div class="padding">
						
									<dl id="hire_promo">
									
										
										
										<dt> <a href="http://www.stickergiant.com/?referral=eventespresso.com" target="_blank">
	<?php _e('StickerGiant.com', 'event_espresso'); ?>
										</a> </dt>
										<dd><a href="http://www.stickergiant.com/?referral=eventespresso.com" target="_blank">StickerGiant</a> is two businesses in one. We are a printing company that prints stickers and labels for thousands of customers all over the United States and Canada. We also have a fun online sticker store that has over 26,000 designs from bands to sports and everything in between. Our selection of Embroidered Patches is vast. </dd>
										<dt> <a href="http://eventespresso.com/contact/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Add+a+Marketing+Resource<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">
	<?php _e('Add a Marketing Resource', 'event_espresso'); ?>
										</a> </dt>
										<dd>Have a marketing resource you would like to see listed here? Please let us know!</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Partners', 'event_espresso'); ?>
								<a name="partners" id="partners"></a></h3>
							<div class="inside">
								<div class="padding">
								<p>The following developers have experience with Event Espresso for their clients and have requested to be listed in this directory. Event Espresso does not make any guarantees about their services. This directory is offered as a community benefit to Event Espresso users. We offer our recommendations on <a href="http://eventespresso.com/support/how-to-select-a-developer/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=how+to+select+a+developer<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab">how to select a developer</a>.</p>
									<dl id="partners">
									
									<dt><a href="http://www.ivycat.com/?referral=eventespresso.com" target="_blank">Ivy Cat</a></dt>
<dd><a href="http://www.ivycat.com/?referral=eventespresso.com"></a>We're a small, agile team of web designers, developers, and server geeks that love to help businesses strategize, create, market, and maintain strong, effective and profitable websites and web applications.</dd>
<dd>Our staff has well over a decade of experience engineering successful websites in many industries. </dd>
<dd>Check our <a href="http://www.ivycat.com/web-design/portfolio/?referral=eventespresso.com" target="_blank">web design portfolio</a> for examples of our work. </dd>
										
										<dt> <a href="http://pixeljar.net/?referral=eventespresso.com" target="_blank">Pixel Jar</a></dt>
										<dd><a href="http://pixeljar.net/?referral=eventespresso.com" target="_blank"></a>Pixel Jar creates custom themes, plugins and sites as well as provides custom coding and modifications for existing projects. The co-creators of Pixel Jar, Brandon and Jeff, met working at another web development firm in 2001. Pixel Jar started in 2004 with the goal to provide solid web solutions for small to medium businesses. In 2007 we worked on our first WordPress project and loved it so much that by 2009, our business model was solely WordPress projects. We are very active in the WordPress community, regularly attending and presenting at our local WordPress Meetup, attending regional WordCamps and hosting the annual WordCamp Orange County.</dd>
										
										
										<dt> <a href="http://bigimprint.com/espresso/" target="_blank">Big Imprint Design</a></dt>
										<dd>We create affordable websites for small businesses and non-profits.</dd>
										
										
										<dt> <a href="http://eventespresso.com/contact/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Become+a+Partner<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">
	<?php _e('Become a Partner', 'event_espresso'); ?>
										</a> </dt>
										<dd>Have experience developing websites around Event Espresso? Become a partner!</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Hire a Developer', 'event_espresso'); ?>
								<a name="hire_devs" id="hire_devs"></a></h3>
							<div class="inside">
								<div class="padding">

									<dl id="hire_devs">
										<dt> <a href="http://jobs.eventespresso.com/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Event+Espresso+Job+Board<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">
	<?php _e('Event Espresso Job Board', 'event_espresso'); ?>
										</a> </dt>
										<dd>A dedicated job board that lists the opportunities to work with Event Espresso or our clients.</dd>
										<dt> <a href="http://wpcandy.com/pros/?referral=eventespresso.com" target="_blank">
	<?php _e('WP Candy Pros', 'event_espresso'); ?>
										</a> </dt>
										<dd>WordPress Professionals and theme developers.</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Favorite Theme Developers', 'event_espresso'); ?>
								<a name="developers" id="theme_devs"></a></h3>
							<div class="inside">
								<div class="padding">
									<dl id="developers">
										<dt><a href="http://www.mojo-themes.com/?referral=eventespresso.com" target="_blank">MOJO Themes</a></dt>
										<dd>It's simple really‚ MOJO themes is a marketplace for everyone to <strong><em>buy</em></strong> or <strong><em>sell</em></strong> themes and templates.</dd>
										<dt><a href="http://www.appthemes.com/cp/go.php?r=12413&i=l0" target="_blank">AppThemes</a></dt>
										<dd>AppThemes are built for businesses of all sizes and run on WordPress so  you don't have to worry about the headache of setting up a complex  server environment.</dd>
										<dt><a href="http://www.woothemes.com/amember/go.php?r=28039&amp;i=b16" target="_blank">WooThemes</a></dt>
										
										<dd>Build websites faster and better using drag and drop, layout, typography, design-control and more... </dd>
										<dt><a href="http://www.studiopress.com/" target="_blank">StudioPress</a></dt>
										<dd>When you buy a StudioPress theme, you are getting state of the art code,  smart design architecture as well as an array of beautiful frames for  your content.</dd>
										<dt><a href="http://www.elegantthemes.com/?referral=eventespresso.com" target="_blank">ElegantThemes</a></dt>
										<dd>Each premium WordPress theme comes expertly coded in valid XHTML and  CSS, and all are made compatible with the latest version of WordPress.</dd>
										<dt><a href="http://allurethemes.com/?referral=eventespresso.com" target="_blank">AllureThemes</a></dt>
										<dd>We create beautiful, top quality WordPress themes for you at amazing prices with exceptional support.</dd>
										<dt><a href="http://museumthemes.com/?referral=eventespresso.com" target="_blank">Museum Themes</a></dt>
										<dd>Fine art WordPress themes.</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>

				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Recommended Plugins', 'event_espresso'); ?>
								<a name="plugins" id="plugins"></a></h3>
							<div class="inside">
								<div class="padding">
									<dl id="plugins">
										<dt><a href="http://wordpress.org/extend/plugins/exclude-pages/" target="_blank">Exclude Pages from Navigation</a></dt>
										<dd> Provides a checkbox on the editing page which you can check to exclude  pages from the primary navigation. IMPORTANT NOTE: This will remove the  pages from any "consumer" side page listings, which may not be limited  to your page navigation listings.</dd>
										<dt><a href="http://wordpress.org/extend/plugins/post-types-order/" target="_blank">Post Types Order</a></dt>
										<dd> Order Post Types Objects using a Drag and Drop Sortable javascript capability
											</dt>

										<dt><a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&amp;c=ib&amp;aff=113214" target="_blank">Gravity Forms</a>
										</dd>
										<dd>
											Build Complex, Powerful Contact Forms in Just Minutes. No Programming Knowledge Required! Yeah, It's Really That Easy.
										</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>

				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Highly Recommended Themes', 'event_espresso'); ?>
								<a name="themes" id="themes"></a></h3>
							<div class="inside">
								<div class="padding">
									<dl id="themes">
										<dt><a href="http://www.pagelines.com/?referral=eventespresso.com" target="_blank">PageLines Framework</a> by Pagelines</dt>
										<dt><a href="http://www.woothemes.com/?referral=eventespresso.com" target="_blank">Diarise</a> by WooThemes</dt>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Other Resources', 'event_espresso'); ?>
								<a name="resources" id="resources"></a></h3>
							<div class="inside">
								<div class="padding">
									<dl id="resources">
										<dt><a href="http://wordpress.stackexchange.com/" target="_blank">WordPress Answers</a></dt>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>

				<li>
					<div class="metabox-holder">
						<div class="postbox"><a name="shortcodes" id="shortcodes"></a>
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Shortcodes', 'event_espresso'); ?>
							</h3>
							<div class="inside">
								<div class="padding">
									<p>
	<?php _e('For more information, please visit:', 'event_espresso'); ?>
										<br />
										<a href="http://eventespresso.com/wiki/shortcodes-template-variables/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Shortcodes+Help<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">http://eventespresso.com/wiki/shortcodes-template-variables/</a></p>
									<hr />
									<div class="shortcode-box">
										<h4>
											<?php _e('Single Events', 'event_espresso'); ?>
										</h4>
										<p>
	<?php _e('Displays a single event on a page or post', 'event_espresso'); ?>
										</p>
										<p ><span class="highlight">[SINGLEEVENT single_event_id="your_event_identifier"]</span></p>
									</div>
									<div class="shortcode-box">
										<h4>
											<?php _e('Add Events to Cart', 'event_espresso'); ?>
										</h4>
										<p>
	<?php _e('Displays an "Add Event to Cart" link that can be added to the event details, page, or post. Requires the <a href="http://eventespresso.com/product/espresso-multiple/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Multiple+Event+Registration+ee_version_'.EVENT_ESPRESSO_VERSION.'&utm_campaign=help_support_tab" target="_blank">Multiple Event Registration addon</a>.', 'event_espresso'); ?>
										</p>
										<p><span class="highlight">[ESPRESSO_CART_LINK]</span></p>
										<h5>
											<?php _e('Additonal Examples:', 'event_espresso'); ?>
										</h5>
										<p><span class="highlight">[ESPRESSO_CART_LINK direct_to_cart=1 moving_to_cart="Redirecting to cart..."]</span><br />
											<?php _e('(Used to redirect to the shopping cart page. Must be added to an event description.)', 'event_espresso'); ?>
										</p>
										<p><span class="highlight">[ESPRESSO_CART_LINK event_id="add_event_id_here" direct_to_cart=1 moving_to_cart="Redirecting to cart..."]</span><br />
	<?php _e('(Same as above, but uses the event_id parameter and can be added to a page or post.)', 'event_espresso'); ?>
										</p>
									</div>
									<div class="shortcode-box">
										<h4>
											<?php _e('Event List', 'event_espresso'); ?>
										</h4>
										<p>
	<?php _e('Returns a list of events', 'event_espresso'); ?>
										</p>
										<ul>
											<li><span class="highlight">[EVENT_LIST]</span></li>
											<li><span class="highlight">[EVENT_LIST limit=1]</span></li>
											<li><span class="highlight">[EVENT_LIST show_expired=true]</span></li>
											<li><span class="highlight">[EVENT_LIST show_deleted=true]</span></li>
											<li><span class="highlight">[EVENT_LIST show_secondary=true]</span></li>
											<li><span class="highlight">[EVENT_LIST show_recurrence=true]</span></li>
											<li><span class="highlight">[EVENT_LIST category_identifier=your_category_identifier]</span></li>
											<li><span class="highlight">[EVENT_LIST staff_id=staff_id_number]</span></li>
											<li><span class="highlight">[EVENT_LIST order_by=date(start_date),id]</span></li>
										</ul>
										<h5>
											<?php _e('Order by parameters:', 'event_espresso'); ?>
										</h5>
										<p>
	<?php _e('(comma separated)', 'event_espresso'); ?>
										</p>
										<p>id<br />
											date(start_date)<br />
											date(end_date)<br />
											event_name<br />
											date(registration_start)<br />
											date(registration_end)<br />
											city<br />
											state<br />
											category_id<br />
											venue_title </p>
										<p class="yellow_alert"><strong>
											<?php _e('Attention:', 'event_espresso'); ?>
											</strong><br />
	<?php _e('The [EVENT_LIST] shortcode should not be used as a replacement for the [ESPRESSO_EVENTS] shortcode. Replacing the [ESPRESSO_EVENTS] shortcode will break your registration pages.', 'event_espresso'); ?>
										</p>
									</div>
									<div class="shortcode-box">
										<h4>
	<?php _e('Attendee Listings', 'event_espresso'); ?>
										</h4>
										<ul>
											<li><span class="highlight">[LISTATTENDEES]</span></li>
											<li><span class="highlight">[LISTATTENDEES limit="30"]</span> //Number of events to show on the page</li>
											<li><span class="highlight">[LISTATTENDEES show_expired="true"]</span> //Show expired events</li>
											<li><span class="highlight">[LISTATTENDEES show_deleted="true"]</span> //Show deleted events</li>
											<li><span class="highlight">[LISTATTENDEES show_secondary="true"]</span> //Show secondary/backup events</li>
											<li><span class="highlight">[LISTATTENDEES show_gravatar="true"]</span> //Show a Gravatar of the attendee</li>
											<li><span class="highlight">[LISTATTENDEES show_recurrence="false"]</span> //Exclude recurring events</li>
											<li><span class="highlight">[LISTATTENDEES event_identifier="your_event_identifier"]</span> //Show a single event using the event identifier</li>
											<li><span class="highlight">[LISTATTENDEES category_identifier="your_category_identifier"]</span> //Show a group of events in a category using the category identifier</li>
											<li><span class="highlight">[LISTATTENDEES staff_id="staff_id_number"]</span> //Show a list of events that are assigned to a staff member</li>
										</ul>
										<p>
	<?php _e('For more information about the attendee listing shortcodes and customizations. Please view the <a href="http://eventespresso.com/wiki/shortcodes-template-variables/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Attendee+Listing+Shortcodes+ee_version_'.EVENT_ESPRESSO_VERSION.'&utm_campaign=help_support_tab">Attendee Listing Shortcodes</a> page.', 'event_espresso'); ?>
										</p>
									</div>
									<div class="shortcode-box">
										<h4>
											<?php _e('Venue Shortcodes', 'event_espresso'); ?>
										</h4>
										<h5>
	<?php _e('As of Event Espresso version 3.1', 'event_espresso'); ?>
										</h5>
										<a name="venue_shortcode" id="venue_shortcode"></a>
										<dl>
											<dt>
												<?php _e('Event Description Example:', 'event_espresso'); ?>
											</dt>
											<dd>
											<?php _e('If you want to display venue details within an event, the venue id is not needed. Just add <span class="highlight">[ESPRESSO_VENUE]</span> to your event description.', 'event_espresso'); ?>
											</dd>
											<dt>
	<?php _e('Example with Optional Parameters:', 'event_espresso'); ?>
											</dt>
											<dd><span class="highlight">[ESPRESSO_VENUE outside_wrapper="div" outside_wrapper_class="event_venue"]</span></dd>
											<dt>
												<?php _e('Page/Post Example:', 'event_espresso'); ?>
											</dt>
											<dd>
	<?php _e('You can display the details of any venue to a page, post or event by adding the id of the venue to the shortcode.', 'event_espresso'); ?>
												<br />
												<span class="highlight">[ESPRESSO_VENUE id="3"]</span></dd>
											<dt>
												<?php _e('Page/Post Example #2:', 'event_espresso'); ?>
											</dt>
											<dd>
	<?php _e('If you want to display all available venues on a page, post, or event:', 'event_espresso'); ?>
												<br />
												<span class="highlight">[ESPRESSO_VENUE]</span>
											</dd>
											<dd>
	<?php _e('Add the event id to the shortcode to display all the venues for an event:', 'event_espresso'); ?>
												<br />
												<span class="highlight">[ESPRESSO_VENUE event_id="8"]</span></dd>

										</dl>
										<h5>
	<?php _e('Available parameters:', 'event_espresso'); ?>
										</h5>
										<ul>
											<li>outside_wrapper_class = class name for the outside wrapper. Eg. event_venue</li>
											<li>outside_wrapper = outside wrapper element. Eg. div</li>
											<li>inside_wrapper_class = class name for the outside wrapper. Eg. venue_details</li>
											<li>inside_wrapper = inside wrapper element. Eg. p</li>
											<li>title_class = class name for the title Eg. venue_name</li>
											<li>title_wrapper = title wrapper element. Eg. h3</li>
											<li>show_title = show the venue name? (true|false default true)</li>
											<li>image_class = class name for the image. Eg. venue_image</li>
											<li>show_image = show the image? (true|false default true)</li>
											<li>show_description = show the description? (true|false default true)</li>
											<li>show_address = show the address of the venue? (true|false default true)</li>
											<li>show_additional_details = show the additional details? (true|false default true)</li>
											<li>show_google_map_link = show the Google map link? (true|false default true)</li>
											<li>map_link_text = text to display in the link. Eg. Map and Directions</li>
										</ul>

										<dl>
											<dt>
	<?php _e('Show All Events in a Venue:', 'event_espresso'); ?>
											</dt>
											<dd>
												<span class="highlight">[ESPRESSO_VENUE_EVENTS id="21"]</span></dd>
											<dd>
												<span class="highlight">[ESPRESSO_VENUE_EVENTS id="21" limit="5"]</span></dd>
										</dl> 

									</div>
									<div class="shortcode-box">
										<h4>
											<?php _e('Staff Shortcodes', 'event_espresso'); ?>
										</h4>
										<h5>
	<?php _e('As of Event Espresso version 3.1', 'event_espresso'); ?>
										</h5>
										<a name="staff_shortcode" id="staff_shortcode"></a>
										<dl>
											<dt>
												<?php _e('Event Description Example:', 'event_espresso'); ?>
											</dt>
											<dd>
											<?php _e('If you want to display a list of staff members within an event, the staff id is not needed. Just add <span class="highlight">[ESPRESSO_STAFF]</span> to your event description.', 'event_espresso'); ?>
											</dd>
											<dt>
	<?php _e('Example with Optional Parameters:', 'event_espresso'); ?>
											</dt>
											<dd><span class="highlight">[ESPRESSO_STAFF outside_wrapper="div" outside_wrapper_class="event_staff" inside_wrapper="p" inside_wrapper_class="event_person"]</span></dd>
											<dt>
												<?php _e('Page/Post Example:', 'event_espresso'); ?>
											</dt>
											<dd>
											<?php _e('You can display the details of any staff member to a page, post or event by adding the id of the staff member to the shortcode.', 'event_espresso'); ?>
												<span class="highlight">[ESPRESSO_STAFF id="3"]</span></dd>
											<dt>
												<?php _e('Page/Post Example #2:', 'event_espresso'); ?>
											</dt>
											<dd>
	<?php _e('If you want to display a list of staff members assigned to an event, to a page, post or event add the event id to the  <span class="highlight">[ESPRESSO_STAFF]</span> shortcode.', 'event_espresso'); ?>
												<br />
												<span class="highlight">[ESPRESSO_STAFF event_id="8"]</span></dd>
										</dl>
										<h5><?php _e('Available parameters:', 'event_espresso'); ?></h5>
										<ul>
											<li>outside_wrapper_class = class name for the outside wrapper. Eg. event_staff</li>
											<li>outside_wrapper = outside wrapper element. Eg. div</li>
											<li>inside_wrapper_class = class name for the outside wrapper. Eg. event_person</li>
											<li>inside_wrapper = inside wrapper element. Eg. p</li>
											<li>name_class = class name for the persons name</li>
											<li>name_wrapper = name wrapper element. Eg. strong</li>
											<li>image_class = class name for the image. Eg. venue_image</li>
											<li>show_image = show the persons image? (true|false default true)</li>
											<li>show_staff_titles = show the role/title? (true|false default true)</li>
											<li>show_staff_details = show the details? (true|false default true)</li>
											<li>show_image = show the image? (true|false default true)</li>
											<li>show_description = show the description? (true|false default true)</li>
										</ul>
									</div>
									<div class="shortcode-box">
										<h4><?php _e('Calendar Shortcodes', 'event_espresso'); ?></h4>
										<ul>
											<li><span class="highlight">[ESPRESSO_CALENDAR]</span></li>
											<li><span class="highlight"> [ESPRESSO_CALENDAR show_expired="true"]</span></li>
											<li><span class="highlight">[ESPRESSO_CALENDAR event_category_id="your_category_identifier"]</span></li>
											<li><span class="highlight">[ESPRESSO_CALENDAR event_category_id="your_category_identifier" show_expired="true"]</span></li>
											<li><span class="highlight">[ESPRESSO_CALENDAR cal_view="month"] (Available parameters: month, basicWeek, basicDay, agendaWeek, agendaDay)</span></li>
										</ul>
									</div>
									<div class="shortcode-box">
										<h4><?php _e('Category Shortcodes', 'event_espresso'); ?></h4>
										<p><span class="highlight">[EVENT_ESPRESSO_CATEGORY event_category_id="your_category_indentifier"]</span></p>
									</div>
								</div>
								<!-- / .padding --> 
							</div>
							<!-- / .inside --> 
						</div>
						<!-- / .postbox --> 
					</div>
					<!-- / .metabox-holder --> 
				</li>

				<li><a name="details" id="details"></a>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Important Information', 'event_espresso'); ?>
							</h3>
							<div class="inside">
								<div class="padding">
									<?php
									global $wpdb, $wp_version;
									$wp_req_version = '3.1';
									$php_req_version = '5.2';
									$mysql_req_version = '5.0';
									$is_php_valid = version_compare(phpversion(), $php_req_version, '>');
									$is_mysql_valid = version_compare($wpdb->db_version(), $mysql_req_version, '>');

									if (!version_compare($wp_version, $wp_req_version, '>=')) {
										echo '<p class="red_alert">' . __('This version of Event Espresso requires WordPress version', 'event_espresso') . ' ' . $wp_req_version . '+. ' . __('Please upgrade to the latest version of WordPress.', 'event_espresso') . '</p>';
									}
									if (!$is_php_valid) {
										echo '<p class="red_alert">' . __('Your version of PHP is out of date, please update to the latest version of PHP. <br>Required version of PHP:', 'event_espresso') . ' ' . $php_req_version . '</p>';
									}
									if (!$is_mysql_valid) {
										echo '<p class="red_alert">' . __('Your version of MySQL is out of date, please update to the latest version of MySQL. <br>Required version of MySQL:', 'event_espresso') . ' ' . $mysql_req_version . '</p>';
									}
									
									
									if (event_espresso_verify_attendee_data() == true) {
														?>
									  <a name="attendee_data" id="attendee_data"></a>
									  <p class="red_text"><strong>
										<?php _e('Attendee information is outdated', 'event_espresso'); ?>
										</strong></p>
									  <p>
										<?php _e('Due to recent changes in the way attendee information is handled, attendee data may appear to be missing from some events. In order to reassign attendees to events, please run the attendee update script by pressing the button below.', 'event_espresso'); ?>
									  </p>
									  <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="post" name="form" id="form">
										<p>
										  <input type="hidden" name="action" value="event_espresso_update_attendee_data" />
										  <input class="button-primary" type="submit" name="event_espresso_update_attendee_data_button" value="<?php _e('Run Attendee Update Script', 'event_espresso'); ?>" id="event_espresso_update_attendee_data_button"/>
										</p>
									  </form>
								  <?php
									}
                                  ?>
									<div class="localhost-information">
										<dl>
											<dt>
	<?php _e('WordPress Version:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo $wp_version; ?></dd>
											<dt>
	<?php _e('PHP Version:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo phpversion(); ?></dd>
											<dt>
	<?php _e('MySQL Version:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo $wpdb->db_version(); ?></dd>
											<dt>Event Espresso Version:</dt>
											<dd><?php echo EVENT_ESPRESSO_VERSION ?></dd>
											<dt>
	<?php _e('WordPress Address (URL):', 'event_espresso'); ?>
											</dt>
											<dd><?php echo site_url(); ?></dd>
											<dt>
	<?php _e('WordPress Content Directory:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo WP_CONTENT_DIR; ?></dd>
											<dt>
	<?php _e('Site address (URL):', 'event_espresso'); ?>
											</dt>
											<dd><?php echo home_url(); ?></dd>
											<dt>
	<?php _e('Event Espresso Plugin URL:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_PLUGINFULLURL ?></dd>
											<dt>
	<?php _e('Event Espresso Plugin Path:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_PLUGINFULLPATH; ?></dd>
											<dt>
	<?php _e('Event Espresso Upload URL:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_UPLOAD_URL; ?></dd>
											<dt>
	<?php _e('Event Espresso Upload Path:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_UPLOAD_DIR; ?></dd>
											<dt>
	<?php _e('Event Espresso Template Path:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_TEMPLATE_DIR; ?></dd>
											<dt>
	<?php _e('Event Espresso Gateway Path:', 'event_espresso'); ?>
											</dt>
											<dd><?php echo EVENT_ESPRESSO_GATEWAY_DIR; ?></dd>
										</dl>
									</div>
								</div>
							</div>
						</div>
					</div>
				</li>

				<li><a name="support" id="support"></a>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Contact Support', 'event_espresso'); ?>
							</h3>
							<div class="inside">
								<div class="padding">
									<h4>Before Contacting Support</h4>
									<p>Please understand that our primary goal is to offer Event Espresso as a very low cost solution compared to building your own system or using a 3rd party service to handle your registrations.</p><p> As with most open source programs (and closed licensed programs), chances are you will find the occasional bug, glitch, white screen of death, and/or general failure. Please don't panic!</p>
									<p>If your problems are not urgent, please post in our <a href="http://eventespresso.com/support/forums/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Before+Contacting+Support<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">support forums</a>. If you need immediate help. Please purchase a support token below, at which time you can schedule time with a dedicated support tech or core developer.</p>
									<p class="attention-block"><strong class="red_text">
										<?php _e('Attention:', 'event_espresso'); ?>
										</strong><br />
	<?php _e('When requesting support. Please copy and paste the details displayed of the <a href="admin.php?page=support#details">Important Information</a> section above. This will help us determine potential problems with your server, WordPress installation, and/or the Event Espresso plugin. Please also include a list (or screenshot) of all <a href="plugins.php?plugin_status=active">active plugins</a>.', 'event_espresso'); ?>
									</p>

									<h4>
										<?php _e('Premium Support Options', 'event_espresso'); ?>
									</h4>
									<p>
										<?php _e('We offer premium support to customers who desire or require a level of support beyond the complimentary support included with all Event Espresso products.', 'event_espresso'); ?>
									</p>
									<h5>
										<?php _e('Support Tokens', 'event_espresso'); ?>
									</h5>
									<p>
										<?php _e('A support token can be used to get priority support for a single  incident. It can be used to schedule support via phone or IM for a  single incident (up to 30 minutes), or to receive priority e-mail  support. A support token can be used for &ldquo;how to&rdquo; questions, technical  issues, &ldquo;best practice&rdquo; questions or for custom development consulting. A  support token consists of the PayPal Transaction ID you received from  PayPal at the time of your purchase.', 'event_espresso'); ?>
									</p>
									<p>
	<?php _e('<strong>You can purchase support tokens</strong> on the <a href="http://eventespresso.com/product/priority-support-tokens/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=support+tokens+ee_version_'.EVENT_ESPRESSO_VERSION.'&utm_campaign=help_support_tab">Premium Support page</a>. Tokens can be purchased one at a time, or in blocks of three at a discount.', 'event_espresso'); ?>
									</p>
									<p>Support tokens can be used to schedule live support (phone or IM) or for priority e-mail support. See <a href="http://eventespresso.com/support/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=details+on+premium+support<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab">details on premium support</a>.</p>
									<div class="support-tokens">
										<h6><a href="http://eventespresso.com/product/priority-support-tokens/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=1+Premium+Support+Token<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">1 Premium Support Token</a></h6>
										<p>Single incident, up to 30 minutes of live support or priority e-mail support.</p>
										<p class="support-prices"><span class="price">Price: $70.00 </span></p>
									</div>
									<div class="support-tokens">
										<h6><a href="http://eventespresso.com/product/3-premium-support-tokens/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=3+Premium+Support+Tokens<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">3 Premium Support Tokens</a></h6>
										<p>Up to 90 minutes of live support or priority e-mail support.</p>
										<p class="support-prices"><span class="price">Price: $185.00 </span></p>
									</div>
									<h5>
	<?php _e('Installation Services', 'event_espresso'); ?>
									</h5>
									<div class="install-options">
										<h6><a href="http://eventespresso.com/product/basic-install/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Basic+Install<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">Basic Install</a></h6>
										<p>Includes plugin installation and setting up basic pages for the plugin.</p>
										<p class="support-prices"><span class="price">Price: $35.00 </span></p>
									</div>
									<div class="install-options">
										<h6><a href="http://eventespresso.com/product/basic-install-with-configuration/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Basic+Install+with+Configuration<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">Basic Install with Configuration</a></h6>
										<p>Includes configuration and testing Payment Gateway's .</p>
										<p class="support-prices"><span class="price">Price: $70.00 </span></p>
									</div>
									<h5>* Prices subject to change on live website.</h5>
									<h5>** Requirements for installation service:</h5>
									<ul>
										<li> The server must be accessible over the internet.</li>
										<li> The server must meet the server requirements (for Event Espresso).</li>
										<li> You must be able to provide a FTP/SFTP username and password. A MySQL database name, username and password is needed for a Basic Install. A WordPress admin user name, password, and login URL.</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="metabox-holder">
						<div class="postbox">
							<div title="Click to toggle" class="handlediv"><br />
							</div>
							<h3 class="hndle">
	<?php _e('Frequently Asked Questions', 'event_espresso'); ?>
								<a name="faq" id="faq"></a></h3>
							<div class="inside">
								<div class="padding">
									<dl id="faqs">
										<dt>
											<?php _e('Registration page just refreshes?', 'event_espresso'); ?>
										</dt>
										<dd>
											<?php _e('Usually its because you need to point the &quot;Main registration page:&quot; (in the Organization Settings page) to whatever page you have the shortcode', 'event_espresso'); ?>
											[ESPRESSO_EVENTS]
										<?php _e('on', 'event_espresso'); ?>
											. </dd>
										<dt>
											<?php _e('Paypal IPN Problem?', 'event_espresso'); ?>
										</dt>
										<dd>
	<?php _e('Four things to check with PayPal when payments notifications are not being sent to Event Espresso.', 'event_espresso'); ?>
											</p>
											<ol>
												<li>
													<?php _e('Make sure you have a standard or a business PayPal account, personal accounts don\'t work.', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Turn on your IPN.', 'event_espresso'); ?>
												</li>
												<li>
													<?php _e('Make sure your PayPal account is verified.', 'event_espresso'); ?>
												</li>
												<li>
	<?php _e('Make sure your Event Espresso pages are not protected or private.', 'event_espresso'); ?>
												</li>
											</ol>
											<p class="more-info">
	<?php _e('More information can be found here:', 'event_espresso'); ?>
												<br />
												<a href="http://eventespresso.com/wiki/how-to-set-up-paypal-ipn/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=how-to-set-up-paypal-ipn<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=help_support_tab" target="_blank">http://eventespresso.com/wiki/how-to-set-up-paypal-ipn/</a></p>
										</dd>
										<dt>
											<?php _e('Why are mails are not being sent when someone registers?', 'event_espresso'); ?>
										</dt>
										<dd>
	<?php _e('Check your email settings on the', 'event_espresso'); ?>
											<a href="admin.php?page=event_espresso#email-settings">Event Espresso > General Settings > Email Settings</a> page<br />
											</p>
											<p>
	<?php _e('If you\'re using WP SMTP with Gmail, also check your spam box to make sure Gmail isn\'t filtering the confirmation emails as spam.', 'event_espresso'); ?>
											</p>
										</dd>
										<dt>
	<?php _e('My events are not importing correctly when I use the CSV upload tool.', 'event_espresso'); ?>
										</dt>
										<dd>
											<p>
	<?php _e('Check your CSV for any apostrophes in the title or description. Using Excel (or some other spreadsheet application) find and replace all apostrophes with <tt>\&amp;#039;</tt>.  This is the HTML entity for \' and is how the titles are entered into the database. For more information, see <a href="http://eventespresso.com/wiki/how-to-import-events/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=CSV+upload+tool+ee_version_'.EVENT_ESPRESSO_VERSION.'&utm_campaign=help_support_tab" target="_blank">this forum post</a>.', 'event_espresso'); ?>
											</p>
										</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li>
				<?php /*?><li>
					<div class="metabox-holder">
						<div class="postbox">
	            <div title="Click to toggle" class="handlediv"><br />
	            </div>
	            <h3 class="hndle">
	<?php _e('Additional Information', 'event_espresso'); ?>
	              <a name="additonal" id="additonal"></a></h3>
	            <div class="inside">
								<div class="padding">
									<dl id="additonal">
										<dt><a href="http://eventespresso.com/support/documentation/">User Guide Forum</a>
										</dt>
										<dt><a href="http://eventespresso.com/forums/category/premium-plugin-support/tutorials/template-customization/">Tutorials</a> </dt><dd>(calendar, css override, payment gateway system, etc.)</dd>
										<dt><a href="http://eventespresso.com/forums/premium-plugin-support/tutorials/template-customization/">Template Customization</a></dt>
										<dt><a href="http://eventespresso.com/forums/2010/12/my-first-event/">Adding Your First Event</a> (video)</dt>
										<dt><a href="http://eventespresso.com/forums/2010/12/video-initial-setp-general-settings-pages/">General Setup &amp; Using Shortcodes</a> (video)</dt>
										<dt><a href="http://eventespresso.com/forums/2010/12/customizing-the-registration-form/%5C">Customizing the Registration Form</a> (video)</dt>
										<dt><a href="http://eventespresso.com/forums/2010/07/account-optional-setting/">Optional PayPal Account Settings</a> (video)</dt>
										<dt><a href="http://eventespresso.com/forums/2011/01/templates-for-the-recurring-events-manager/">Recurring Events Manager</a> (video)</dt>
										<dt><a href="http://eventespresso.com/forums/2010/10/post-type-variables-and-shortcodes/">Variables and Shortcodes</a></dt>
										<dt><a href="http://eventespresso.com/forums/category/general/compatiblity-issues/">Compatibility Issues</a></dt>
										<dt><a href="http://eventespresso.com/forums/category/premium-plugin-support/bug-reports/">Bug Submission Form</a></dt>
										<dt><a href="http://eventespresso.com/forums/category/premium-plugin-support/news-and-updates/">Change log</a></dt>
										<dt><a href="http://eventespresso.com/update-request-form/">Update Request Form</a></dt>               <dd>Please use this form if a newer version of Event Espresso or an Addon  has been released and you are unable to download it from the specified  page or the email notification.</dd>
									</dl>
								</div>
							</div>
						</div>
					</div>
				</li><?php */?>


				<?php /* ?>

				  <li>
				  <div class="metabox-holder">
				  <div class="postbox">
				  <div title="Click to toggle" class="handlediv"><br />
				  </div>
				  <h3 class="hndle">
				  <?php _e('Additional Information', 'event_espresso'); ?>
				  <a name="additonal" id="additonal"></a></h3>
				  <div class="inside">
				  <div class="padding">
				  <dl id="additonal">
				  <dt>
				  <?php _e('Registration page just refreshes?', 'event_espresso'); ?>
				  </dt>
				  <dd>
				  <?php _e('Usually its because you need to point the &quot;Main registration page:&quot; (in the Organization Settings page) to whatever page you have the shortcode', 'event_espresso'); ?>
				  [ESPRESSO_EVENTS]
				  <?php _e('on', 'event_espresso'); ?>
				  . </dd>
				  </dl>
				  </div>
				  </div>
				  </div>
				  </div>
				  </li>

				  <?php */ ?>
			</ul>
		</div>
		<!-- / .meta-box-sortables -->
		<?php
		$main_post_content = ob_get_clean();
		espresso_choose_layout($main_post_content, event_espresso_display_right_column());
		?>
	</div>
	<!-- / #wrap --> 
	<script type="text/javascript" charset="utf-8">
		//<![CDATA[
		jQuery(document).ready(function() {
			postboxes.add_postbox_toggles('support');
		}); 
		//]]>
	</script>
	<?php
}
