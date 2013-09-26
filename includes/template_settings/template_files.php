<?php
//Template customization
	
	if (isset($_POST['ee_rename_folder'])) {
		$folder = EVENT_ESPRESSO_UPLOAD_DIR."templates-".date('Y-m-d-H-i-s')."/";
		rename(EVENT_ESPRESSO_TEMPLATE_DIR, $folder);
		echo sprintf(__('Your templates folder has been renamed to: %s %s', 'event_espresso'), '<br />', '<span class="green_alert">'.$folder.'</span>');
		return;
	}
	
	if (isset($_REQUEST['event_espresso_admin_action']) && $_REQUEST['event_espresso_admin_action'] == 'copy_templates') {
	    add_action('admin_init', 'event_espresso_smartCopy');
	}
	
	if (isset($_SESSION['event_espresso_themes_copied']) && $_SESSION['event_espresso_themes_copied'] == true) {
	    ?>
	    <div class="updated fade below-h2" id="message" style="background-color: rgb(255, 251, 204); border:#999 solid 1px; padding:2px;">
	        <p>
	    <?php _e("Your templates have been copied."); ?>
	        </p>
	    </div>
	    <?php
	    $_SESSION['event_espresso_themes_copied'] = false;
	}
	
	$files = array('attendee_list.php', 'event_list.php', 'event_list_display.php', 'event_post.php', 'payment_page.php', 'registration_page.php', 'registration_page_display.php', 'confirmation_display.php', 'return_payment.php', 'widget.php', 'shopping_cart.php');
	//echo EVENT_ESPRESSO_TEMPLATE_DIR . $files[3];
   	
	if ( file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[0]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[1]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[2]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[3]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[4]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[5]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[6]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[7]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[8]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[9]) || file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $files[10]) ) {
	   ?>
	   <div class="red_alert"><p>
    <?php _e('Remember: If updates are made or features are added to these templates in the future, you will need to make the updates to your customized templates.', 'event_espresso'); ?>
		        </p>
				<p>
				<?php _e('If you are having issues with these files, please use this button to rename the templates directory.', 'event_espresso'); ?><br />
				</p>
				<p><form method="post" name="ee_rename_form" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<input style="border:#333 1px solid" name="ee_rename_folder" type="submit" value="<?php _e('Rename Templates Directory', 'event_espresso'); ?>" />
	   </form></p></div>
	    <p>
    <?php _e("Modifying your event listings and registration pages is easy."); ?> <?php _e("You just need to edit the appropriate files in the following location.", 'event_espresso'); ?> <?php printf(__("For more information about customizing your template files, please follow <a href='%s' target=\"_blank\">this tutorial</a>.", 'event_espresso'), "http://eventespresso.com/wiki/put-custom-templates/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Modifying+your+event+listings+ee_version_".EVENT_ESPRESSO_VERSION ."+ee_install_url_".site_url()."&utm_campaign=template_settings_tab"); ?>
	    </p>
	    <p> <span class="green_alert">
	            <?php _e("Path:", 'event_espresso'); ?>
    <?php echo str_replace(ABSPATH, "", EVENT_ESPRESSO_TEMPLATE_DIR); ?></span> </p>
	    <div  style="border: 1px solid #999; background:#F0F0F0; padding:5px; width:90%;">
	        <p><strong>
    <?php _e('Current Template Files:', 'event_espresso'); ?>
		</strong> </p>
	        <ul>
	            <?php
	            foreach ($files as $file) {
		switch ($file) {
		    case 'attendee_list.php':
		        $info = __('(displays a list of attendees)', 'event_espresso');
		        break;
		    case 'event_list.php':
		        $info = __('(logic for displaying the list of events)', 'event_espresso');
		        break;
		    case 'event_list_display.php':
		        $info = __('(displays a list of events)', 'event_espresso');
		        break;
		    case 'event_post.php':
		        $info = __('(create-a-post template)', 'event_espresso');
		        break;
		    case 'payment_page.php':
		        $info = __('(displays your payment page text)', 'event_espresso');
		        break;
		    case 'registration_page.php':
		        $info = __('(logic for displaying the registration form)', 'event_espresso');
		        break;
		    case 'registration_page_display.php':
		        $info = __('(displays your registration form)', 'event_espresso');
		        break;
		    case 'confirmation_display.php':
		        $info = __('(displays a confirmation page for free events)', 'event_espresso');
		        break;
		    case 'return_payment.php':
		        $info = __('(page that is displayed when returning to pay)', 'event_espresso');
		        break;
		    case 'widget.php':
		        $info = __('(creates a widget for use in your theme)', 'event_espresso');
		        break;
									case 'shopping_cart.php':
		        $info = __('(this is the shopping cart page)', 'event_espresso');
		        break;
		    default:
		        $info = '';
		        break;
		}
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . $file)) {
		    ?>
		    <li><strong style="color:#090"><?php _e($file . ' - Moved', 'event_espresso'); ?></strong> - <?php echo $info; ?></li>
		<?php } else { ?>
		    <li><strong style="color:#F00">
		    <?php _e($file . ' - Not Moved', 'event_espresso'); ?></strong> - <?php echo $info; ?></li>

		    <?php
		}
				}
	            ?>
	        </ul>
			
	    </div>
	    <?php
	} else if (!is_writable(EVENT_ESPRESSO_TEMPLATE_DIR)) {
	    ?>
	    <p>
    <?php _e('In order to use this feature, you will need to move the files located in the', 'event_espresso'); ?> <span class="display-path"><strong><?php echo EVENT_ESPRESSO_PLUGINFULLPATH ?>templates/</strong></span> <?php _e('directory into the', 'event_espresso'); ?> <span class="display-path"><strong><?php echo EVENT_ESPRESSO_TEMPLATE_DIR ?></strong></span> <?php _e('directory', 'event_espresso'); ?>.
	    </p>
	    <p class="fugue f-error">
    <?php _e("The permissions on your templates directory are incorrect.", 'event_espresso'); ?>
	    </p>
	    <p class="fugue f-error">
    <?php _e("In order for the system to see your files, you need to set the permissions to 775 on the following directory.", 'event_espresso'); ?>
	        <br />
	        <br />
	        <span class='display-path'><strong>
	    <?php _e("Path:", 'event_espresso'); ?>
	            </strong> <?php echo EVENT_ESPRESSO_TEMPLATE_DIR; ?> </span></p>
	    <?php
	} else {
	    ?>
	    <p>
    <?php _e('If you plan on modifying the look of your event listings, registration page, or attendee list. Use the option below to move these templates to a safe place. Keep in mind, if updates are made or features are added to these templates in the future. You will need to make the updates to your customized templates.', 'event_espresso'); ?>
	    </p>
	    <p class="fugue f-warn">
    <?php printf(__("Your template files have not been moved. If you would like to customize your template files, please follow <a href='%s' target=\"_blank\">this tutorial</a>.", 'event_espresso'), "http://eventespresso.com/wiki/put-custom-templates/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Modifying+your+event+listings+ee_version_".EVENT_ESPRESSO_VERSION ."+ee_install_url_".site_url()."&utm_campaign=template_settings_tab"); ?>
	    </p>
	    <?php /*?><p class="updated"><?php printf(__("Click here to <a href='%s'>Move your files</a> to a safe place.", 'event_espresso'), wp_nonce_url("admin.php?event_espresso_admin_action=copy_templates", 'copy_templates')); ?> </p><?php */?>
	    <?php
	}