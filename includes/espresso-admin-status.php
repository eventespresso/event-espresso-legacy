<?php
/**
 * Debug/Status page
 *
 * @credit		WooThemes
 * @author 		Event Espresso
 * @category 	Admin
 * @package 	Event Espresso/Admin/System Status
 * @version     1.0
 */

/**
 * Output the content of the debugging page.
 *
 * @access public
 * @return void
 */
function espresso_system_status() {
	global $org_options;
	/**
	 * let_to_num function. (copied from woocommerce-core-functions.php)
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @access public
	 * @param $size
	 * @return int
	 */
	function espresso_let_to_num( $size ) {
		$l 		= substr( $size, -1 );
		$ret 	= substr( $size, 0, -1 );
		switch( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		return $ret;
	}

    ?>
	<style type="text/css">
		#debug-report {
			display: none;
			font-family: monospace;
			height: 200px;
			margin-bottom: 20px;
			width: 100%;
		}
		table.ee_status_table th {
			font-weight: bold;
		}
		table.ee_status_table td {
			padding: 5px 7px;
		}
		table.ee_status_table td mark {
			background: none repeat scroll 0 0 transparent;
		}
		table.ee_status_table td mark.yes {
			color: green;
		}
		table.ee_status_table td mark.no {
			color: #999999;
		}
		table.ee_status_table td mark.error {
			color: red;
		}
		table.ee_status_table td ul {
			margin: 0;
		}
	</style>
	<div class="wrap event_espresso">
		<div class="icon32" id="icon-options-event"><br /></div>
		<h2><?php _e( 'System Status', 'event_espresso' ); ?> <a href="#" class="add-new-h2 debug-report"><?php _e('Generate report', 'event_espresso'); ?></a></h2>
		<br/>
		<textarea id="debug-report" readonly="readonly"></textarea>
		<table class="ee_status_table widefat" cellspacing="0">

			<thead>
				<tr>
					<th colspan="2"><?php _e( 'Versions', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<tbody>
                <tr>
                    <td><?php _e('Event Espresso version','event_espresso')?></td>
                    <td><?php echo espresso_version(); ?></td>
                </tr>
                <tr>
                    <td><?php _e('WordPress version','event_espresso')?></td>
                    <td><?php if ( is_multisite() ) echo 'WPMU'; else echo 'WP'; ?> <?php echo bloginfo('version'); ?></td>
                </tr>
             	<tr>
             		<td><?php _e('Installed plugins','event_espresso')?></td>
             		<td><?php
             			$active_plugins = (array) get_option( 'active_plugins', array() );

             			if ( is_multisite() )
							$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

						$active_plugins = array_map( 'strtolower', $active_plugins );

						$ee_plugins = array();

						foreach ( $active_plugins as $plugin ) {
							//if ( strstr( $plugin, 'event_espresso' ) ) {

								$plugin_data = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

	    						if ( ! empty( $plugin_data['Name'] ) ) {

	    							$ee_plugins[] = $plugin_data['Name'] . ' ' . __('by', 'event_espresso') . ' ' . $plugin_data['Author'] . ' ' . __('version', 'event_espresso') . ' ' . $plugin_data['Version'];

	    						}
    						//}
						}

						if ( sizeof( $ee_plugins ) == 0 ) echo '-'; else echo '<ul><li>' . implode( ', </li><li>', $ee_plugins ) . '</li></ul>';

             		?></td>
             	</tr>
			</tbody>

			<thead>
				<tr>
					<th colspan="2"><?php _e( 'Settings', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<tbody>
                <tr>
                    <td><?php _e('Home URL','event_espresso')?></td>
                    <td><?php echo home_url(); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Site URL','event_espresso')?></td>
                    <td><?php echo site_url(); ?></td>
                </tr>
			</tbody>

			<thead>
				<tr>
					<th colspan="2"><?php _e( 'Registration Pages', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php
					$check_pages = array(
						__('Event Page', 'event_espresso') => array(
								'option' => $org_options['event_page_id'],
								'shortcode' => '[ESPRESSO_EVENTS]'
							),
						__('Payment/Thank You Page', 'event_espresso') => array(
								'option' => $org_options['return_url'],
								'shortcode' => '[ESPRESSO_PAYMENTS]'
							),
						__('Transaction Notification Page', 'event_espresso') => array(
								'option' => $org_options['notify_url'],
								'shortcode' => '[ESPRESSO_TXN_PAGE]'
							),
						__('Cancel Return Page', 'event_espresso') => array(
								'option' => $org_options['cancel_return'],
								'shortcode' => '[ESPRESSO_CANCELLED]'
							)
					);

					$alt = 1;

					foreach ( $check_pages as $page_name => $values ) {

						if ( $alt == 1 ) echo '<tr>'; else echo '<tr>';

						echo '<td>' . $page_name . '</td><td>';

						$error = false;
					
						$page_id = $values['option'];

						// Page ID check
						if ( ! $page_id ) {
							echo '<mark class="error">' . __('Page not set', 'event_espresso') . '</mark>';
							$error = true;
						} else {

							// Shortcode check
							if ( $values['shortcode'] ) {
								$page = get_post( $page_id );

								if ( ! strstr( $page->post_content, $values['shortcode'] ) ) {

									echo '<mark class="error">' . sprintf(__('Page does not contain the shortcode: %s', 'event_espresso'), $values['shortcode'] ) . '</mark>';
									$error = true;

								}
							}

						}

						if ( ! $error ) echo '<mark class="yes">#' . $page_id . ' - ' . get_permalink( $page_id ) . '</mark>';

						echo '</td></tr>';

						$alt = $alt * -1;
					}
				?>
			</tbody>

			
			<thead>
				<tr>
					<th colspan="2"><?php _e( 'Server Environment', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<tbody>
                <tr>
                    <td><?php _e('PHP Version','event_espresso')?></td>
                    <td><?php
                    	if ( function_exists( 'phpversion' ) ) echo phpversion();
                    ?></td>
                </tr>
                <tr>
                    <td><?php _e('Server Software','event_espresso')?></td>
                    <td><?php
                    	echo $_SERVER['SERVER_SOFTWARE'];
                    ?></td>
                </tr>
				<tr>
                    <td><?php _e('WP Max Upload Size','event_espresso'); ?></td>
                    <td><?php
                    	echo size_format( wp_max_upload_size() );
                    ?></td>
                </tr>
                <tr>
                    <td><?php _e('Server upload_max_filesize','event_espresso')?></td>
                    <td><?php
                    	if(function_exists('phpversion'))
                    		echo size_format( espresso_let_to_num( ini_get('upload_max_filesize') ) );
                    ?></td>
                </tr>
                <tr>
                    <td><?php _e('Server post_max_size','event_espresso')?></td>
                    <td><?php
                    	if(function_exists('phpversion'))
                    		echo size_format( espresso_let_to_num( ini_get('post_max_size') ) );
                    ?></td>
                </tr>
                <tr>
                    <td><?php _e('WP Memory Limit','event_espresso')?></td>
                    <td><?php
                    	$memory = espresso_let_to_num( WP_MEMORY_LIMIT );

                    	if ( $memory < 67108864 ) {
                    		echo '<mark class="error">' . sprintf( __('%s - We recommend setting memory to at least 64MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'event_espresso'), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
                    	} else {
                    		echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
                    	}
                    ?></td>
                </tr>
                <tr>
                    <td><?php _e('WP Debug Mode','event_espresso')?></td>
                    <td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">' . __('Yes', 'event_espresso') . '</mark>'; else echo '<mark class="no">' . __('No', 'event_espresso') . '</mark>'; ?></td>
                </tr>
                <tr>
                    <td><?php _e('Espresso Logging','event_espresso')?></td>
                    <td><?php
                    	if ( @fopen( EVENT_ESPRESSO_UPLOAD_DIR . '/logs/espresso_log.txt', 'a' ) )
                    		echo '<mark class="yes">' . __('Log directory is writable.', 'event_espresso') . '</mark>';
                    	else
                    		echo '<mark class="error">' . __('Log directory (<code>espresso/logs/</code>) is not writable. Logging will not be possible.', 'event_espresso') . '</mark>';
                    ?></td>
                </tr>
            </tbody>

            <thead>
				<tr>
					<th colspan="2"><?php _e( 'PHP Sessions', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<tbody>
            	<tr>
                    <td><?php _e('Session save path','event_espresso')?></td>
					<td><?php
						$save_path = session_save_path();

						if ( ! is_dir( $save_path ) ) {
							echo '<mark class="error">' . sprintf( __('<code>%s</code> does not exist - contact your host to resolve the problem.', 'event_espresso'), $save_path ). '</mark>';
						} elseif ( ! is_writeable( $save_path ) ) {
							echo '<mark class="error">' . sprintf( __('<code>%s</code> is not writable - contact your host to resolve the problem.', 'event_espresso'), $save_path ). '</mark>';
						} else {
							echo '<mark class="yes">' . sprintf( __('<code>%s</code> is writable.', 'event_espresso'), $save_path ). '</mark>';
						}
                    ?></td>
                </tr>
                <tr>
                	<td><?php _e('Session name','event_espresso')?></td>
                	<td><?php echo session_name(); ?></td>
                </tr>
            </tbody>

            <thead>
				<tr>
					<th colspan="2"><?php _e( 'Remote Posting/IPN', 'event_espresso' ); ?></th>
				</tr>
			</thead>

			<?php
				$posting = array();

				// fsockopen/cURL
				$posting['fsockopen_curl']['name'] = __('fsockopen/cURL','event_espresso');
				if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
					if ( function_exists( 'fsockopen' ) && function_exists( 'curl_init' )) {
						$posting['fsockopen_curl']['note'] = __('Your server has fsockopen and cURL enabled.', 'event_espresso');
					} elseif ( function_exists( 'fsockopen' )) {
						$posting['fsockopen_curl']['note'] = __('Your server has fsockopen enabled, cURL is disabled.', 'event_espresso');
					} else {
						$posting['fsockopen_curl']['note'] = __('Your server has cURL enabled, fsockopen is disabled.', 'event_espresso');
					}
					$posting['fsockopen_curl']['success'] = true;
				} else {
            		$posting['fsockopen_curl']['note'] = __('Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'event_espresso'). '</mark>';
            		$posting['fsockopen_curl']['success'] = false;
            	}

            	// WP Remote Post Check
				/*$posting['wp_remote_post']['name'] = __('WP Remote Post Check','event_espresso');
				$request['cmd'] = '_notify-validate';
				$params = array(
					'sslverify' 	=> false,
		        	'timeout' 		=> 60,
		        	'user-agent'	=> 'Event Espresso/' . espresso_version(),
		        	'body'			=> $request
				);
				$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

				if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
            		$posting['wp_remote_post']['note'] = __('wp_remote_post() was successful - PayPal IPN is working.', 'event_espresso');
            		$posting['wp_remote_post']['success'] = true;
            	} elseif ( is_wp_error( $response ) ) {
            		$posting['wp_remote_post']['note'] = __('wp_remote_post() failed. PayPal IPN won\'t work with your server. Contact your hosting provider. Error:', 'event_espresso') . ' ' . $response->get_error_message();
            		$posting['wp_remote_post']['success'] = false;
            	} else {
	            	$posting['wp_remote_post']['note'] = __('wp_remote_post() failed. PayPal IPN may not work with your server.', 'event_espresso');
            		$posting['wp_remote_post']['success'] = false;
            	}*/

            	$posting = apply_filters( 'wc_debug_posting', $posting );
            ?>

			<tbody>
			<?php foreach($posting as $post) { $mark = ( isset( $post['success'] ) && $post['success'] == true ) ? 'yes' : 'error'; ?>
				<tr>
                    <td><?php echo $post['name']; ?></td>
                    <td>
                    	<mark class="<?php echo $mark; ?>">
	                    	<?php echo $post['note']; ?>
                    	</mark>
                    </td>
                </tr>
			<?php } ?>
            </tbody>
		</table>

	</div>
	<script type="text/javascript">

		jQuery('a.debug-report').click(function(){

			if ( ! jQuery('#debug-report').val() ) {

				// Generate report - user can paste into forum
				var report = '`';

				jQuery('thead:not(".tools"), tbody:not(".tools")', '.ee_status_table').each(function(){

					$this = jQuery( this );

					if ( $this.is('thead') ) {

						report = report + "\n=============================================================================================\n";
						report = report + " " + jQuery.trim( $this.text() ) + "\n";
						report = report + "=============================================================================================\n";

					} else {

						jQuery('tr', $this).each(function(){

							$this = jQuery( this );

							report = report + $this.find('td:eq(0)').text() + ": \t";
							report = report + $this.find('td:eq(1)').text() + "\n";

						});

					}

				});

				report = report + '`';

				jQuery('#debug-report').val( report );
			}

			jQuery('#debug-report').slideToggle('500', function() {
				jQuery(this).select();
			});

      		return false;

		});

	</script>
	<?php
}