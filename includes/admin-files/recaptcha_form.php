<?php
//Displays reCAPTCHA form
?>
          <div class="metabox-holder">
			<div class="postbox">
			<div title="Click to toggle" class="handlediv"><br /></div>
  			<h3 class="hndle">
              <?php _e('reCAPTCHA Settings','event_espresso'); ?>
            </h3>
          	<div class="inside">
            <div class="padding">
              <ul>
                <li>
                  <label for="use_captcha">
                   <strong><?php echo sprintf(__('Use %s to block spam registrations', 'event_espresso'), '<a href="https://www.google.com/recaptcha/admin#whyrecaptcha" title="reCAPTCHA: Stop Spam, Read Books" target="_blank">reCAPTCHA</a>'); ?></strong>:
                    <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=recaptcha_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/question-frame.png" width="16" height="16" /></a> </label>
                  <?php
						$values=array(					
							array('id'=>'N','text'=> __('No','event_espresso')),
							array('id'=>'Y','text'=> __('Yes','event_espresso'))
						);				
							echo select_input('use_captcha', $values, isset($org_options['use_captcha']) ? $org_options['use_captcha'] : '');
					?>
                </li>
                <li>
                  <label for="recaptcha_publickey">
                    <?php _e('Public/Site Key:','event_espresso'); ?>
                  </label>
                  <input type="text" name="recaptcha_publickey" size="45" value="<?php if(isset($org_options['recaptcha_publickey'])) echo $org_options['recaptcha_publickey'];?>" />
                </li>
                <li>
                  <label for="recaptcha_privatekey">
                    <?php _e('Private Key:','event_espresso'); ?>
                  </label>
                  <input type="text" name="recaptcha_privatekey" size="45" value="<?php if(isset($org_options['recaptcha_privatekey'])) echo $org_options['recaptcha_privatekey'];?>" />
                </li>
                <li>
                  <h4>
                    <?php _e('Look &amp; Feel Customization','event_espresso'); ?>
                  </h4>
                </li>
                <li>
                  <label for="recaptcha_width">
                    <?php _e('Width:','event_espresso'); ?>
                  </label>
                  <input name="recaptcha_width" type="text" value="<?php echo !isset($org_options['recaptcha_width']) || $org_options['recaptcha_width'] == '' ? '500': $org_options['recaptcha_width'];?>" size="5" maxlength="6" />
                </li>
                <li>
                  <label for="recaptcha_theme">
                    <?php _e('Theme:','event_espresso'); ?>
                  </label>
                  <?php
						$values=array(					
							array('id'=>'light','text'=> __('Light (Default)','event_espresso')),
							array('id'=>'dark','text'=> __('Dark','event_espresso'))
            );
							echo select_input('recaptcha_theme', $values, isset($org_options['recaptcha_theme']) ? $org_options['recaptcha_theme'] : '');
					?>
                </li>
                <li>
                  <label for="recaptcha_language">
                    <?php _e('Language:','event_espresso'); ?>
                  </label>
                  <?php
						$values=array(					
							array('id'=>'en','text'=> __('English','event_espresso')),
							array('id'=>'es','text'=> __('Spanish','event_espresso')),
							array('id'=>'nl','text'=> __('Dutch','event_espresso')),
							array('id'=>'fr','text'=> __('French','event_espresso')),
							array('id'=>'de','text'=> __('German','event_espresso')),
							array('id'=>'pt','text'=> __('Portuguese','event_espresso')),
							array('id'=>'ru','text'=> __('Russian','event_espresso')),				
							array('id'=>'tr','text'=> __('Turkish','event_espresso'))
							);				
							echo select_input('recaptcha_language', $values, isset($org_options['recaptcha_language']) ? $org_options['recaptcha_language'] : '');
					?>
                </li>
              </ul>
              <div id="recaptcha_info" style="display:none">
               <h2><?php _e('reCAPTCHA Information', 'event_espresso'); ?></h2>
						<p><?php echo sprintf(__('reCAPTCHA helps prevent automated abuse of your site (such as comment spam or bogus registrations) by using a %s to ensure that only humans perform certain actions.', 'event_espresso'), '<a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">CAPTCHA</a>'); ?></p>
			<p><?php echo sprintf(__('You must sign up for a free %s account to use it with this plugin. If you already have a reCAPTCHA account enter your "Public" and "Private" keys on this page.', 'event_espresso'), '<a href="https://www.google.com/recaptcha/admin#createsite" target="_blank">CAPTCHA</a>'); ?></p>
              </div>

               <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'event_espresso'); ?>" id="save_organization_saetting_3" />
      </p>
            </div>
							</div>
          </div>
          </div>