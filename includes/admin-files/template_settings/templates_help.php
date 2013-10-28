<div style="display: none;">

    <?php ########## help box ########## ?>
    <div id="enable_styles_info" class="pop-help" >
        <div class="TB-ee-frame">
    			<h2><?php _e('Enable Built In Styles', 'event_espresso'); ?></h2>
				<h3><em class="important"><?php _e('Please do not use these styles. Use the Themeroller styles instead!!', 'event_espresso'); ?></em></h3>
        	<p>
       		 <?php _e('Enabling Styles tells Event Espresso to use it\'s built in stylesheets for styling the template files.', 'event_espresso'); ?>
          </p>
    				<p><?php _e('If this option is enabled Event Espresso first looks in the /wp-content/uploads/espresso/templates folder for a copy of the stylesheet, if not found it then uses the copy found in the main templates folder.', 'event_espresso'); ?></p>
    				<p><?php _e('If event espresso uses the original copy of the stylesheet you will have the option to select from a range of alternative sheets, these additional stylesheets however are not available if you have copied the default stylesheet to the uploads folder to customize yourself.', 'event_espresso'); ?></p>
          <p>
    					<em class="important">
             <b><?php _e('ATTENTION:', 'event_espresso'); ?></b><br />

    					<?php _e('If you want to modify and customize the styles of your template pages you must move a copy of the 
    									event_espresso_style.css to the /wp-content/uploads/espresso/templates folder (if /espresso/templates does 
    									not exist you may need to create it manually. Keeping your modification in this folder ensures that your styles are not overwritten when upgrading EE.) ', 'event_espresso'); ?>
    					</em>
    				</p>
					</div>
    </div>
			<?php ########## help box ########## ?>
			<div id="alternative_styles_info" class="pop-help" >
       <div class="TB-ee-frame">
    		<h2><?php _e('Legacy Espresso Stylesheets', 'event_espresso'); ?></h2>
			<h3><em class="important"><?php _e('Please do not use these styles. Use the Themeroller styles instead!!', 'event_espresso'); ?></em></h3>
						<?php if($disabled) : ?>
    			<em class="important">
							<?php _e('You currently either have <i>Enable Built in style sheets</i> disabled or have a copy of the default espresso stylesheet copied to your uploads folder, and the alternative stylesheets selections are therefore disabled.', 'event_espresso'); ?>
    			</em>
						<?php endif; ?>
        	<p><?php _e('These two drop down options provide a choice of stylesheets with various stylings.', 'event_espresso'); ?></p>
    			<p><?php _e('These options are available as long as you have not copied the main stylesheet <i>event_espresso_style.css</i> to the uploads folder to customize yourself.', 'event_espresso'); ?></p>
    			<p><?php _e('By default the general espresso stylesheet will be loaded if you have selected <i>Yes</i> in the <i>Enable built in style sheets</i> option, You may then choose to select from one of the alternative base stylesheets along with an optional sheet to select a color scheme.', 'event_espresso'); ?></p>
						<p><?php _e('These selections replace the default espresso stylesheet and offer enhanced styling for your event pages details.', 'events_espreso') ?></p>

					</div>
    </div>
	
	 <?php ########## help box ########## ?>
    <div id="disable_legacy_styles_info" class="pop-help" >
        <div class="TB-ee-frame">
    			<h2><?php _e('Disable Legacy Styles', 'event_espresso'); ?></h2>
        	<p>
       		 <?php _e('This will completely remove the legacy style sheet options', 'event_espresso'); ?>
          </p>
    	
					</div>
    </div>
	<?php
		/**
		 * Enable Themeroller Styles Help Box
		 */
	?>
	<div id="enable_themeroller_styles_info" class="pop-help" >
		<div class="TB-ee-frame">
			<h2>
				<?php _e('Enable ThemeRoller Styles', 'event_espresso'); ?>
			</h2>
			<p>
				<?php _e('Enabling ThemeRoller Styles tells Event Espresso to use it\'s built in ThemeRoller stylesheets for styling the template files.', 'event_espresso'); ?>
			</p>
			<p>
				<?php _e('If this option is enabled Event Espresso first looks in the "wp-content/uploads/espresso/themeroller/" directory for a copy of the stylesheet, if not found it then uses the copy found in the main "wp-content/event-espresso/templates/css/themeroller/" directory.', 'event_espresso'); ?>
			</p>
			<p>
				<?php _e('If Event Espresso uses the original copy of the stylesheet you will have the option to select from a range of alternative sheets, these additional stylesheets however are not available if you have copied the default stylesheet to the uploads directory to customize yourself.', 'event_espresso'); ?>
			</p>
			<p> <em class="important"> <strong>
				<?php _e('ATTENTION:', 'event_espresso'); ?>
				</strong><br />
				<?php _e('If you want to modify and customize the default (non-Themeroller) styles of your template pages, then you must move a copy of the event_espresso_style.css in the "wp-content/uploads/espresso/templates" directory (if this directory does not exist, you may need to create it manually). Keeping your modifications in this folder ensures that your styles are not overwritten when upgrading EE.', 'event_espresso'); ?>
				</em> </p>
		</div>
	</div>
	<div id="themeroller_info" class="pop-help" >
		<div class="TB-ee-frame">
			<h2>
				<?php _e('ThemeRoller Style Settings ', 'event_espresso'); ?>
			</h2>
			<p>
			<?php _e('Event Espresso uses the <a href="http://themeroller.com/" target="_blank">jQuery UI ThemeRoller</a> CSS library to style the event listings and registration pages. The ThemeRoller CSS library makes it easy to get the desired look and feel for your events and calendar. If the built-in styles are activated, Event Espresso will automatically load the "wp-content/event-espresso/templates/css/themeroller/themeroller-base.css" (or the &quot;wp-content/uploads/espresso/themeroller/themeroller-base.css&quot;) style sheet which applies the necessary padding and margins to the Event Espresso generated content.', 'event_espresso'); ?>
			</p>
			<p><strong><?php _e('Adding Custom ThemeRoller Styles', 'event_espresso'); ?></strong><br />
			<?php _e('To add your own custom ThemeRoller styles: ', 'event_espresso'); ?>
			</p>
			<ol>
			<li><?php _e('Go to the <a href="http://themeroller.com/" target="_blank">jQuery UI ThemeRoller</a> site and design and download your custom style.', 'event_espresso'); ?></li>
			<li><?php _e('Unzip the downloaded file, then navigate to the "/css/" folder. Inside you will see a directory named "custom-theme" (or the name of the theme if you selected a pre-made style). If you like, rename the "my-custom-theme" directory to something memorable.', 'event_espresso'); ?></li>
			<li><?php _e('This step is very important. Inside the "my-custom-theme" folder, find a file named "jquery-ui-*.*.**.custom.css". Rename that file to "style.css". If you do not rename this file, you will not see your custom theme in the selections.', 'event_espresso'); ?></li>
			<li><?php _e('Upload your desired ThemeRoller styles to the "wp-content/uploads/espresso/themeroller/" directory on your server.', 'event_espresso'); ?></li>
			<li><?php _e('This step is very important. Make sure there is an empty "index.php" file in the "wp-content/uploads/espresso/themeroller/" directory. This will tell Event Espresso that you want to use custom ThemeRoller CSS files.', 'event_espresso'); ?></li>
			<li><strong><?php _e('IMPORTANT:', 'event_espresso'); ?></strong> <?php _e('Verify you have an "wp-content/uploads/espresso/themeroller/index.php" file. Also make sure your custom ThemeRoller CSS files look something like this "wp-content/uploads/espresso/themeroller/my-custom-theme/style.css".', 'event_espresso'); ?> </li>
			</ol>

			<p>
				<strong><?php _e('About ThemeRoller', 'event_espresso'); ?></strong>
				<br />
				<?php _e('ThemeRoller is a web app that offers a fun and intuitive interface for designing and downloading custom themes for jQuery UI. You can find ThemeRoller in the "Themes" section of the jQuery UI site, or by following this link: <a href="http://themeroller.com/" target="_blank">jQuery UI ThemeRoller</a>', 'event_espresso'); ?>
			</p>
		</div>
	</div>

</div>