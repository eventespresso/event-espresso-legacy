<?php

function event_espresso_manage_templates() {
	global $wpdb, $org_options, $espresso_premium;
	//print_r($org_options);
	if (isset($_POST['update_org'])) {
		$org_options['display_description_on_multi_reg_page'] = !empty($_POST['display_description_on_multi_reg_page']) ? $_POST['display_description_on_multi_reg_page'] : 'N';
		$org_options['display_short_description_in_event_list'] = !empty($_POST['display_short_description_in_event_list']) ? $_POST['display_short_description_in_event_list'] : 'N';
		$org_options['display_address_in_event_list'] = !empty($_POST['display_address_in_event_list']) ? $_POST['display_address_in_event_list'] : 'N';
		$org_options['display_address_in_regform'] = !empty($_POST['display_address_in_regform']) ? $_POST['display_address_in_regform'] : 'N';
		$org_options['use_custom_post_types'] = !empty($_POST['use_custom_post_types']) ? $_POST['use_custom_post_types'] : 'N';
		$org_options['display_ical_download'] = !empty($_POST['display_ical_download']) ? $_POST['display_ical_download'] : 'Y';
$org_options['display_featured_image'] = !empty($_POST['display_featured_image']) ? $_POST['display_featured_image'] : 'N';
		$org_options['enable_default_style'] = !empty($_POST['enable_default_style']) ? $_POST['enable_default_style'] : 'N';
		$org_options['selected_style'] = !empty($_POST['selected_style']) ? $_POST['selected_style'] : '';
		$org_options['style_color'] = !empty($_POST['style_color']) ? $_POST['style_color'] : '';
		$org_options['style_settings']['enable_default_style'] = !empty($_POST['enable_themeroller_style']) ? $_POST['enable_themeroller_style'] : 'N';
		$org_options['style_settings']['use_grid_layout'] = !empty($_POST['use_grid_layout']) ? $_POST['use_grid_layout'] : 'N';
		$org_options['themeroller']['themeroller_style'] = empty($_POST['themeroller_style']) ? 'N' : $_POST['themeroller_style'];
		
		//FEM Settings
		$org_options['fem_settings']['enable_fem_category_select'] = empty($_POST['enable_fem_category_select']) ? 'N' : $_POST['enable_fem_category_select'];
		$org_options['fem_settings']['enable_fem_pricing_section'] = empty($_POST['enable_fem_pricing_section']) ? 'N' : $_POST['enable_fem_pricing_section'];
		$org_options['fem_settings']['enable_fem_venue_section'] = empty($_POST['enable_fem_venue_section']) ? 'N' : $_POST['enable_fem_venue_section'];
		
		//Legacy styles
		$org_options['style_settings']['disable_legacy_styles'] = !empty($_POST['disable_legacy_styles']) ? $_POST['disable_legacy_styles'] : 'Y';

		if (isset($_POST['remove_css']) && $_POST['remove_css'] == 'true') {
			$org_options['style_settings']['css_name'] = '';
		}

		if (isset($_FILES['css']) && is_uploaded_file($_FILES['css']['tmp_name'])) {
			if (copy($_FILES['css']['tmp_name'], EVENT_ESPRESSO_UPLOAD_DIR . 'css/' . $_FILES['css']['name'])) {
				$org_options['style_settings']['css_name'] = $_FILES['css']['name'];
			}
		}

		update_option('events_organization_settings', $org_options);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Template details saved.', 'event_espresso') . '</strong></p></div>';
	}

	$org_options = get_option('events_organization_settings');

	$values = array(
			array('id' => 'Y', 'text' => __('Yes', 'event_espresso')),
			array('id' => 'N', 'text' => __('No', 'event_espresso'))
	);

	function espresso_themeroller_style_is_selected($name) {
		global $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$input_item = $name;
		$option_selections = isset($org_options['themeroller']) && !empty($org_options['themeroller']) ? array($org_options['themeroller']['themeroller_style']) : array();
		if (!in_array($input_item, $option_selections)) {
			return false;
		} else {
			echo 'selected="selected"';
			return;
		}
	}

	//Stylesheet functions
	// read our style dir and build an array of files
	// themeroller style directory
	if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/themeroller/index.php")) {
		$dhandle = opendir(EVENT_ESPRESSO_UPLOAD_DIR . '/themeroller/');
	} else {
		$dhandle = opendir(EVENT_ESPRESSO_PLUGINFULLPATH . 'templates/css/themeroller/');
	}

	$files_themeroller = array();

	$exclude = array('.', '..', 'index.htm', 'index.html', 'index.php', '.svn', 'themeroller-.css', '.DS_Store', basename($_SERVER['PHP_SELF']));

	if ($dhandle) { //if we managed to open the directory
		// loop through all of the files
		while (false !== ($fname_themeroller = readdir($dhandle))) {

			if (!in_array($fname_themeroller, $exclude) && !is_dir($fname_themeroller)) {
				// store the filename
				$files_themeroller[] = $fname_themeroller;
			}
		}
		// close the directory
		closedir($dhandle);
	}
	?>

	<div class="wrap">
		<div id="icon-options-event" class="icon32"> </div>
		<h2>
			<?php _e('Event Template Settings', 'event_espresso'); ?>
		</h2>
		<?php ob_start(); ?>
		
			<div class="meta-box-sortables ui-sortables">
			<form class="espresso_form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<?php #### metaboxes #### ?>
				<div class="metabox-holder">
					<div class="postbox">
						<div title="Click to toggle" class="handlediv"><br />
						</div>
						<h3 class="hndle">
							<?php _e('Template Options', 'event_espresso'); ?>
						</h3>
						<div class="inside">
							<div class="padding">
								<table class="form-table">
									<tbody>
										<tr>
											<th> <label for="display_short_description_in_event_list">
													<?php _e('Display short descriptions in the event listings?', 'event_espresso'); ?>
												</label>
											</th>
											<td><?php echo select_input('display_short_description_in_event_list', $values, isset($org_options['display_short_description_in_event_list']) ? $org_options['display_short_description_in_event_list'] : 'N'); ?><br />
												<span class="description"><?php _e('Be sure to use the "More..." tag in your event description', 'event_espresso'); ?></span></td>
										</tr>
										<?php if (function_exists('event_espresso_multi_reg_init') && $espresso_premium == true) { ?>
											<tr>
												<th><label for="display_description_on_multi_reg_page">
														<?php _e('Display event descriptions in the multiple event registration pages?', 'event_espresso'); ?>
													</label></th>
												<td><?php echo select_input('display_description_on_multi_reg_page', $values, isset($org_options['display_description_on_multi_reg_page']) ? $org_options['display_description_on_multi_reg_page'] : 'N'); ?></td>
											</tr>
										<?php } ?>
										<tr>
											<th><label for="display_address_in_event_list">
													<?php _e('Display addresses in the event listings?', 'event_espresso'); ?>
												</label></th>
											<td><?php echo select_input('display_address_in_event_list', $values, isset($org_options['display_address_in_event_list']) ? $org_options['display_address_in_event_list'] : 'N'); ?></td>
										</tr>
										<tr>
											<th><label for="display_address_in_regform">
													<?php _e('Display the address in the registration form? ', 'event_espresso'); ?>
												</label></th>
											<td><?php echo select_input('display_address_in_regform', $values, isset($org_options['display_address_in_regform']) ? $org_options['display_address_in_regform'] : 'Y'); ?><br />
												<span class="description"><?php _e('Disable the address if you are using the venue manager shortcodes in your event description.', 'event_espresso'); ?></span></td>
										</tr>
										<?php if ($espresso_premium == true) { ?>
										<tr>
											<th><label for="use_custom_post_types">
													<?php _e('Use the custom post types feature?', 'event_espresso'); ?>
												</label></th>
											<td><?php echo select_input('use_custom_post_types', $values, isset($org_options['use_custom_post_types']) ? $org_options['use_custom_post_types'] : 'N'); ?></td>
										</tr>
										<tr>
											<th><label for="display_ical_download">
													<?php _e('Display an "Add to my Calendar" icon/link in the event templates?', 'event_espresso'); ?>
												</label></th>
											<td><?php echo select_input('display_ical_download', $values, isset($org_options['display_ical_download']) ? $org_options['display_ical_download'] : 'Y'); ?><br />
												<span class="description"><?php _e('This is an ics/ical downloadable file. Can also be modified in the event template files.', 'event_espresso'); ?></span></td>
										</tr>
<tr>
											<th><label for="display_featured_image">
													<?php _e('Display featured images in the event list and registration pages?', 'event_espresso'); ?>
												</label></th>
											<td><?php echo select_input('display_featured_image', $values, isset($org_options['display_featured_image']) ? $org_options['display_featured_image'] : 'N'); ?><br />
												<span class="description"><?php _e('This setting offers an simple solution to display featured images in your event templates. Height and width attributes are set within the featured image upload tool. Some customization may be required to produce the desired results within your WordPress theme.', 'event_espresso'); ?></span></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>

								<p>
									<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_setting_1" />
								</p>
							</div>
							<!-- / .padding --> 
						</div>
						<!-- / .inside --> 
					</div>
					<!-- / .postbox --> 
				</div>
				<!-- / .metabox-holder -->

				<div class="metabox-holder">
					<div class="postbox">
						<div title="Click to toggle" class="handlediv"><br />
						</div>
						<h3 class="hndle">
							<?php _e('Stylesheet Options', 'event_espresso'); ?>
						</h3>
						<div class="inside">
							<div class="padding">
								<?php
								if (isset($org_options['enable_default_style']) && $espresso_premium == true) {
									include('style_settings.php');
								}
								?>
								<h2>
									<?php _e('Themeroller Styles', 'event_espresso'); ?>
								</h2>
								<!-- Themeroller Style Settings -->
								<table class="form-table">
									<tbody>
										<tr>
											<th> <label>
													<?php _e('Use Themeroller Style Sheets', 'event_espresso'); ?>
													<?php //echo apply_filters('filter_hook_espresso_help', 'enable_styles_info'); ?>
												</label>
											</th>
											<td><?php echo select_input('enable_themeroller_style', $values, $org_options['style_settings']['enable_default_style'], 'id="use_built_in_style_sheets"'); ?> <a class="thickbox"  href="#TB_inline?height=400&width=500&inlineId=enable_themeroller_styles_info" target="_blank"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/question-frame.png" width="16" height="16" /></a><br />
												<span class="description">
													<?php _e('This option enables the style settings below.', 'event_espresso'); ?>
												</span></td>
										</tr>
										<tr>
											<th> <label>
													<?php _e('ThemeRoller Style ', 'event_espresso'); ?>
												</label>
												<?php //echo apply_filters('filter_hook_espresso_help', 'themeroller_info'); ?>
											</th>
											<td><select id="style-themeroller" class="wide" name="themeroller_style">
													<option <?php espresso_themeroller_style_is_selected($fname_themeroller) ?> value="smoothness"> -
														<?php _e('Default', 'event_espresso'); ?>
													</option>
													<?php foreach ($files_themeroller as $fname_themeroller) { ?>
														<option <?php espresso_themeroller_style_is_selected($fname_themeroller) ?> value="<?php echo $fname_themeroller ?>"><?php echo $fname_themeroller; ?></option>
													<?php } ?>
												</select>
												<a class="thickbox"  href="#TB_inline?height=400&width=500&inlineId=themeroller_info" target="_blank"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/question-frame.png" width="16" height="16" /></a> <br />
												<span class="description">
													<?php _e('Default style sheet is Smoothness.', 'event_espresso'); ?>
												</span></td>
										</tr>
										<?php if (!empty($org_options['style_settings']['css_name'])) { ?>
											<tr>
												<th> <label>
														<?php _e('Current Custom Style Sheet', 'event_espresso'); ?>
													</label>
												</th>
												<td><a href="<?php echo EVENT_ESPRESSO_UPLOAD_URL . 'css/' . $org_options['style_settings']['css_name']; ?>" target="_blank"><?php echo $org_options['style_settings']['css_name']; ?></a>
													<input style="width:20px; margin-left:20px" name="remove_css" type="checkbox" value="true" />
													<?php _e('Remove style sheet?', 'event_espresso'); ?></td>
											</tr>
										<?php } ?>
										<?php if ($espresso_premium == true) { ?>
										<tr>
											<th> <label>
													<?php _e('Add a custom style sheet?', 'event_espresso'); ?>
												</label>
											</th>
											<td><input type="file" name="css" id="css" /></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
								<p>
									<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_setting_2" />
								</p>
							</div>
							<!-- / .padding --> 
						</div>
						<!-- / .inside --> 
					</div>
					<!-- / .postbox --> 
				</div>
				<!-- / .metabox-holder -->
				
				<?php echo do_action('action_hook_espresso_fem_template_settings'); //FEM Form ?>
				
				<input type="hidden" name="update_org" value="update" />
		</form>
		<?php if ($espresso_premium == true) { ?>
		<h2>
					<?php _e('Developers Only', 'event_espresso') ?>
				</h2>
				<hr />
				<div class="metabox-holder">
					<div class="postbox">
						<div title="Click to toggle" class="handlediv"><br />
						</div>
						<h3 class="hndle">
							<?php _e('Developer templates', 'event_espresso'); ?>
						</h3>
						<div class="inside">
							<div class="padding">
								<?php require_once('template_files.php'); ?>
							</div>
							<!-- / .padding --> 
						</div>
						<!-- / .inside --> 
					</div>
					<!-- / .postbox --> 
				</div>
				<?php }?>
				<!-- / .metabox-holder -->
				<?php #### finish metaboxes #### ?>
				
				
			</div>
			<!-- / .meta-box-sortables -->

			
		<?php
		include_once('templates_help.php');
		$main_post_content = ob_get_clean();
		espresso_choose_layout($main_post_content, event_espresso_display_right_column());
		?>

	</div>
	<!-- / #wrap --> 
	<script type="text/javascript" charset="utf-8">
		//<![CDATA[
		jQuery(document).ready(function() {
			postboxes.add_postbox_toggles('template_conf');
		
		}); 
		//]]>
	</script>
	<?php
	return;
}

