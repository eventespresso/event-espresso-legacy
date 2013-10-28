<?php
/**
* This file should be bundled with the main plugin.  Any addons to your main plugin can include this file from the main plugin folder.  This contains the library for 
* handling all the automatic upgrade stuff on the clients end.
* 
* You also have to make sure you call this class in any addons/plugins you want to be added to the update checker.  Here's what you do:
* if ( file_exists(WP_PLUGIN_DIR . '/location_of_file/pue-client.php') ) { //include the file 
*	require( WP_PLUGIN_DIR . '/location_of_file/pue-client.php' );
*	$host_server_url = 'http://updateserver.com'; //this needs to be the host server where plugin update engine is installed.
*	$plugin_slug = 'plugin-slug'; //this needs to be the slug of the plugin/addon that you want updated (and that pue-client.php is included with).  This slug should match what you've set as the value for plugin-slug when adding the plugin to the plugin list via plugin-update-engine on your server.
*	//$options needs to be an array with the included keys as listed.
*	$options = array(
*		'optionName' => '', //(optional) - used as the reference for saving update information in the clients options table.  Will be automatically set if left blank.
*		'apikey' => $api_key, //(required), you will need to obtain the apikey that the client gets from your site and then saves in their sites options table (see 'getting an api-key' below)
*		'lang_domain' => '', //(optional) - put here whatever reference you are using for the localization of your plugin (if it's localized).  That way strings in this file will be included in the translation for your plugin.
*		'checkPeriod' => '', //(optional) - use this parameter to indicate how often you want the client's install to ping your server for update checks.  The integer indicates hours.  If you don't include this parameter it will default to 12 hours.
*	);
*	$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
* }
*/

/**
 * getting an api-key
 *
*/
//You'll need to put something like this here before initiating the PluginUpdateEngineChecker class to obtain the api-key the client has set for your plugin. Of course this means you will need to include a field in your plugin option page for the client to enter this key.  (modify to match your setup):
/*
 $settings = get_option('plugin_options'); //'plugin_options' should be replaced by whatever holds your plugin options and the api_key
 $api_key = $settings['plugin_api_key']; 
*/
if ( !class_exists('PluginUpdateEngineChecker') ):
/**
 * A custom plugin update checker. 
 * 
 * @original author (c) Janis Elsts
 * @heavily modified by Darren Ethier
 * @license GPL2 or greater. 
 * @version 1.1
 * @access public
 */
class PluginUpdateEngineChecker {
	
	public $metadataUrl = ''; //The URL of the plugin's metadata file.
	public $pluginFile = '';  //Plugin filename relative to the plugins directory.
	public $pluginName = ''; //variable used to hold the pluginName as set by the constructor.
	public $slug = '';        //Plugin slug. (with .php extension)
	public $checkPeriod = 12; //How often to check for updates (in hours).
	public $optionName = '';  //Where to store the update info.
	public $option_key = ''; //this is what is used to reference the api_key in your plugin options.  PUE uses this to trigger updating your information message whenever this option_key is modified.
	public $options_page_slug = ''; //this is the slug of the options page for your plugin where the site-licence(api) key is set by your user.  This is required in order to do an update check immediately when the options page is set so api messages update immediately.
	public $plugin_path = ''; //if included this gives the path for the main plugin file so that the generated one using the given SLUG is not used.
	public $json_error = ''; //for storing any json_error data that get's returned so we can display an admin notice.
	public $api_secret_key = ''; //used to hold the user API.  If not set then nothing will work!
	public $install_key = '';  //used to hold the install_key if set (included here for addons that will extend PUE to use install key checks)
	public $install_key_arr = array(); //holds the install key array from the database.
	public $download_query = array(); //used to hold the query variables for download checks;
	public $lang_domain = ''; //used to hold the localization domain for translations .
	public $dismiss_upgrade; //for setting the dismiss upgrade option (per plugin).
	public $pue_install_key; //we'll customize this later so each plugin can have it's own install key!
	public $extra_stats; //used to contain an array of key/value pairs that will be sent as extra stats.
		
	/**
	 * Class constructor.
	 * 
	 * @param string $metadataUrl The URL of the plugin's metadata file.
	 * @param string $pluginFile Fully qualified path to the main plugin file.
	 * @param string $slug The plugin's 'slug'. 
	 * @param array $options:  Will contain any options that need to be set in the class initialization for construct.  These are the keys:
	 * 	@key integer $checkPeriod How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
	 * 	@key string $optionName Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'. 
	 *  @key string $apikey used to authorize download updates from developer server
	 *	@key string $lang_domain If the plugin file pue-client.php is included with is localized you can put the domain reference string here so any strings in this file get included in the localization.
	 * @return void
	 */
	function __construct( $metadataUrl, $slug = '', $options = array() ){
		$this->metadataUrl = $metadataUrl;
		if ( is_array($slug ) ) {
			$premium = array_values($slug['premium']);
			$slug = $premium[0];
		}
		$this->slug = $slug;
		$tr_slug = str_replace('-','_',$this->slug);
		$this->pluginFile = get_option('pue_file_loc_'.$this->slug);
		$this->dismiss_upgrade = 'pu_dismissed_upgrade_'.$tr_slug;
		$this->pluginName = ucwords(str_replace('-', ' ', $this->slug));
		$this->pue_install_key = 'pue_install_key_'.$tr_slug;
		$this->current_domain = str_replace('http://','',site_url());
		$this->current_domain = urlencode(str_replace('https://','',$this->current_domain));
		
		$defaults = array(
			'optionName' => 'external_updates-' . $this->slug,
			'apikey' => '',
			'lang_domain' => '',
			'checkPeriod' => 12,
			'plugin_path' => '',
			'option_key' => 'pue_site_license_key',
			'options_page_slug' => null,
			'extra_stats' => array() //this is an array of key value pairs for extra stats being tracked.
		);
		
		$options = wp_parse_args( $options, $defaults );
		extract( $options, EXTR_SKIP );
		$this->optionName = $optionName;
		$this->checkPeriod = (int) $checkPeriod;
		$this->api_secret_key = trim($apikey);
		$this->lang_domain = $lang_domain;
		$this->plugin_path = $plugin_path;
		$this->option_key = $option_key;
		$this->options_page_slug = $options_page_slug;
		$this->extra_stats = $extra_stats;

		
		if ( !empty($this->plugin_path) ) {
			$this->pluginFile = $this->plugin_path;
		}
	
		$this->installHooks();
	}
	
	/**
	* gets the api from the options table if present
	**/
	function set_api($new_api = '') {
		//download query flag
		$this->download_query['pu_get_download'] = 1;
		//include current version 
		$this->download_query['pue_active_version'] = $this->getInstalledVersion();
		$this->download_query['site_domain'] = $this->current_domain;
		
		//the following is for install key inclusion (will apply later with PUE addons.)
		$this->install_key_arr = get_option($this->pue_install_key);
		if ( isset($this->install_key_arr['key'] ) ) {
			
			$this->install_key = $this->install_key_arr['key'];

			$this->download_query['pue_install_key'] = $this->install_key;
		} else {
			$this->download_query['pue_install_key'] = '';
		}
		
		if ( !empty($new_api) ) {
			$this->api_secret_key = $new_api;
			$this->download_query['pu_plugin_api'] = $this->api_secret_key;
			return;
		}
		
		if ( empty($new_api) ) {
			$this->download_query['pu_plugin_api'] = $this->api_secret_key;
			return;
		}
	}
	
	/**
	 * Install the hooks required to run periodic update checks and inject update info 
	 * into WP data structures. 
	 * Also other hooks related to the automatic updates (such as checking agains API and what not (@from Darren)
	 * @return void
	 */
	function installHooks(){
				
		//Set up the periodic update checks
		$cronHook = 'check_plugin_updates-' . $this->slug;
		if ( $this->checkPeriod > 0 ){
			
			//Trigger the check via Cron
			add_filter('cron_schedules', array(&$this, '_addCustomSchedule'));
			if ( !wp_next_scheduled($cronHook) && !defined('WP_INSTALLING') ) {
				$scheduleName = 'every' . $this->checkPeriod . 'hours';
				wp_schedule_event(time(), $scheduleName, $cronHook);
			}
			add_action($cronHook, array(&$this, 'checkForUpdates'));
			
			//In case Cron is disabled or unreliable, we also manually trigger 
			//the periodic checks while the user is browsing the Dashboard. 
			//$this->hook_into_wp_update_api();
			add_action( 'plugins_loaded', array(&$this, 'hook_into_wp_update_api') );
			//add_action( 'updated_option', array(&$this, 'trigger_update_check'), 10, 3);
			//$this->hook_into_wp_update_api();
		} else {
			//Periodic checks are disabled.
			wp_clear_scheduled_hook($cronHook);
		}
		//dashboard message "dismiss upgrade" link
		add_action( "wp_ajax_".$this->dismiss_upgrade, array(&$this, 'dashboard_dismiss_upgrade')); 
	}
	
	
	/**
	 * Add our custom schedule to the array of Cron schedules used by WP.
	 * 
	 * @param array $schedules
	 * @return array
	 */
	function _addCustomSchedule($schedules){
		if ( $this->checkPeriod && ($this->checkPeriod > 0) ){
			$scheduleName = 'every' . $this->checkPeriod . 'hours';
			$schedules[$scheduleName] = array(
				'interval' => $this->checkPeriod * 3600, 
				'display' => sprintf('Every %d hours', $this->checkPeriod),
			);
		}		
		return $schedules;
	}

	function hook_into_wp_update_api() {
		$this->set_api();
		$this->maybeCheckForUpdates();
		add_filter('plugins_api', array(&$this, 'injectInfo'), 10, 3);
		//Insert our update info into the update array maintained by WP
		add_action('site_transient_update_plugins', array(&$this,'injectUpdate')); //WP 3.0+
		//Override requests for plugin information
		$triggered = $this->trigger_update_check();
		$this->json_error = get_option('pue_json_error_'.$this->slug);
		if ( !empty($this->json_error) )
			add_action('admin_notices', array(&$this, 'display_json_error'));
	}

	function trigger_update_check() {
		//we're just using this to trigger a PUE ping whenever an option matching the given $this->option_key is saved..
		//if ( isset($_REQUEST['page'] ) && $_REQUEST['page'] == $this->options_page_slug ) {
			$triggered = false;
			if ( !empty($_POST) ) {
				foreach ( $_POST as $key => $value ) {
					$triggered = $this->maybe_trigger_update($value, $key, $this->option_key);
				}
				
			}
			return $triggered;
		/*} else {
			return false;
		}*/
	}

	function maybe_trigger_update($value, $key, $site_key_search_string) {
		if ( $key == $site_key_search_string || (is_array($value) && isset($value[$site_key_search_string]) ) ) {
			//if $site_key_search_string exists but the actual key field is empty...let's reset the install key as well.
			if ( $value == '' || ( is_array($value) && empty($value[$site_key_search_string] ) ) || $value != $this->api_secret_key || ( is_array($value) && $value[$site_key_search_string] != $api_secret_key ) )
				delete_option($this->pue_install_key);
			//remove_action('admin_notices', 'display_json_error');
			$this->api_secret_key = $value;
			$this->set_api($this->api_secret_key);
			$this->checkForUpdates();
			return true;
		}
		//remove_action('after_plugin_row_'.$this->pluginFile, 'wp_plugin_update_row', 10, 2);
		return false;
	}
	
	/**
	 * Retrieve plugin info from the configured API endpoint.
	 * 
	 * @uses wp_remote_get()
	 * 
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return $pluginInfo
	 */
	function requestInfo($queryArgs = array()){
		//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
		$queryArgs['pu_request_plugin'] = $this->slug; 
		
		if ( !empty($this->api_secret_key) )
			$queryArgs['pu_plugin_api'] = $this->api_secret_key;  
			
		if ( !empty($this->install_key) )
			$queryArgs['pue_install_key'] = $this->install_key;

		//todo: this can be removed in a later version of PUE when majority of EE users are using more recent versions.
		$queryArgs['new_pue_chk'] = 1;
        
		//include version info
			$queryArgs['pue_active_version'] = $this->getInstalledVersion();
		
		//include domain info
			$queryArgs['site_domain'] = $this->current_domain;

		$queryArgs = apply_filters('puc_request_info_query_args-'.$this->slug, $queryArgs);
		
		//Various options for the wp_remote_get() call. Plugins can filter these, too.
		$options = array(
			'timeout' => 10, //seconds
			'headers' => array(
				'Accept' => 'application/json'
			),
		);
		$options = apply_filters('puc_request_info_options-'.$this->slug, array());
		
		$url = $this->metadataUrl; 

		if ( !empty($queryArgs) ){
			$url = add_query_arg($queryArgs, $url);
		}

		$result = wp_remote_get(
			$url,
			$options
		);

		$this->_send_extra_stats(); //we'll trigger an extra stats update here.

		//Try to parse the response
		$pluginInfo = null;
		if ( !is_wp_error($result) && isset($result['response']['code']) && ($result['response']['code'] == 200) && !empty($result['body']) ){
			
			$pluginInfo = PU_PluginInfo::fromJson($result['body']);
		}

		$pluginInfo = apply_filters('puc_request_info_result-'.$this->slug, $pluginInfo, $result);
		
		return $pluginInfo;
	}




	private function _send_extra_stats() {
		//first if we don't have a stats array then lets just get out.
		if ( empty( $this->extra_stats) ) return;


		//set up args sent in body
		$body = array(
			'extra_stats' => $this->extra_stats,
			'user_api_key' => $this->api_secret_key,
			'pue_stats_request' => 1,
			'domain' => $this->current_domain,
			'pue_plugin_slug' => $this->slug,
			'pue_plugin_version' => $this->getInstalledVersion()
			);

		//setup up post args
		$args = array(
			'timeout' => 10,
			'blocking' => TRUE,
			'user-agent' => 'PUE-stats-carrier',
			'body' => $body,
			'blocking' => TRUE,
			'sslverify' => FALSE
			);

		$resp = wp_remote_post($this->metadataUrl, $args);
		
	}


	
	/**
	 * Retrieve the latest update (if any) from the configured API endpoint.
	 * 
	 * @uses PluginUpdateEngineChecker::requestInfo()
	 * 
	 * @return PluginUpdateUtility An instance of PluginUpdateUtility, or NULL when no updates are available.
	 */
	function requestUpdate(){
		//For the sake of simplicity, this function just calls requestInfo() 
		//and transforms the result accordingly.
		$pluginInfo = $this->requestInfo(array('pu_checking_for_updates' => '1'));
		delete_option('pue_json_error_'.$this->slug);
		if ( $pluginInfo == null ){
			return null;
		}
		//admin display for if the update check reveals that there is a new version but the API key isn't valid.  
		if ( isset($pluginInfo->api_invalid) )  { //we have json_error returned let's display a message
			$this->json_error = $pluginInfo; 
			update_option('pue_json_error_'.$this->slug, $this->json_error);
			return $this->json_error;
		}

		
		if ( isset($pluginInfo->new_install_key) ) {
			$this->install_key_arr['key'] = $pluginInfo->new_install_key; 
			update_option($this->pue_install_key, $this->install_key_arr);
		}
		
		//need to correct the download url so it contains the custom user data (i.e. api and any other paramaters)
		//oh let's generate the download_url otherwise it will be old news...
				
		if ( !empty($this->download_query) )  {
			$d_install_key = $this->install_key_arr['key'];
			$this->download_query['pue_install_key'] = $d_install_key;
			$this->download_query['new_pue_check'] = 1;
			$pluginInfo->download_url = add_query_arg($this->download_query, $pluginInfo->download_url);
		}
		
		return PluginUpdateUtility::fromPluginInfo($pluginInfo);
	}
	
	function in_plugin_update_message($plugin_data) {
		$plugininfo = $this->json_error;
		//only display messages if there is a new version of the plugin.
		if ( is_object($plugininfo) ) {
			if ( version_compare($plugininfo->version, $this->getInstalledVersion(), '>') ) {
				if ( $plugininfo->api_invalid ) {
					$msg = str_replace('%plugin_name%', $this->pluginName, $plugininfo->api_inline_invalid_message);
					$msg = str_replace('%version%', $plugininfo->version, $msg);
					$msg = str_replace('%changelog%', '<a class="thickbox" title="'.$this->pluginName.'" href="plugin-install.php?tab=plugin-information&plugin='.$this->slug.'&TB_iframe=true&width=640&height=808">What\'s New</a>', $msg);
					echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">' . $msg . '</div></td>';
				}
			}
		}
	}
	
	function display_changelog() {
	//todo (at some point in the future!) contents of changelog display page when api-key is invalid or missing.  It will ONLY show the changelog (hook into existing thickbox?)
	
	}
	
	function display_json_error() {
		$pluginInfo = $this->json_error;
		$update_dismissed = get_option($this->dismiss_upgrade);
		
		$is_dismissed = !empty($update_dismissed) && in_array($pluginInfo->version, $update_dismissed) ? true : false;
		
		if ($is_dismissed)
			return;
		
		//only display messages if there is a new version of the plugin.  
		if ( version_compare($pluginInfo->version, $this->getInstalledVersion(), '>') ) {
			if ( $pluginInfo->api_invalid ) {
				$msg = str_replace('%plugin_name%', $this->pluginName, $pluginInfo->api_invalid_message);
				$msg = str_replace('%version%', $pluginInfo->version, $msg);
			}
			//Dismiss code idea below is obtained from the Gravity Forms Plugin by rocketgenius.com
			?>
				<div class="updated" style="padding:15px; position:relative;" id="pu_dashboard_message"><?php echo $msg ?>
				<a href="javascript:void(0);" onclick="PUDismissUpgrade();" style='float:right;'><?php _e("Dismiss") ?></a>
            </div>
            <script type="text/javascript">
                function PUDismissUpgrade(){
                    jQuery("#pu_dashboard_message").slideUp();
                    jQuery.post(ajaxurl, {action:"<?php echo $this->dismiss_upgrade; ?>", version:"<?php echo $pluginInfo->version; ?>", cookie: encodeURIComponent(document.cookie)});
                }
            </script>
			<?php
		}
	}
	
	function dashboard_dismiss_upgrade() {
		$os_ary = get_option($this->dismiss_upgrade);
		if (!is_array($os_ary))
			$os_ary = array();
		
		$os_ary[] = $_POST['version'];
		update_option($this->dismiss_upgrade, $os_ary);
	}
	
	/**
	 * Get the currently installed version of the plugin.
	 * 
	 * @return string Version number.
	 */
	function getInstalledVersion(){
		if ( function_exists('get_plugins') ) {
			$allPlugins = get_plugins();
		} else {
			include_once(ABSPATH.'wp-admin/includes/plugin.php');
			$allPlugins = get_plugins();
		}
		if ( !empty($allPlugins) ) {
			foreach ( $allPlugins as $loc => $details ) {
					//prepare string for match.
					$slug_match = str_replace('-','\-',$this->slug);
					if ( !empty($slug_match) && preg_match('/(?<=)(^'.$slug_match.')((?=\/)|(?=\.))/', $loc) ) {
						update_option('pue_file_loc_'.$this->slug, $loc);
						return $allPlugins[$loc]['Version'];
					}
				}
			delete_option('pue_file_loc_'.$this->slug, $loc); 
		}
		return ''; //this should never happen
	}
	
	/**
	 * Check for plugin updates. 
	 * The results are stored in the DB option specified in $optionName.
	 * 
	 * @return void
	 */
	function checkForUpdates(){
		$state = get_option($this->optionName);
		if ( empty($state) ){
			$state = new StdClass;
			$state->lastCheck = 0;
			$state->checkedVersion = '';
			$state->update = null;
		}
		
		$state->lastCheck = time();
		$state->checkedVersion = $this->getInstalledVersion();
		update_option($this->optionName, $state); //Save before checking in case something goes wrong 
		
		$state->update = $this->requestUpdate();
		update_option($this->optionName, $state);
	}
	
	/**
	 * Check for updates only if the configured check interval has already elapsed.
	 * 
	 * @return void
	 */
	function maybeCheckForUpdates(){
		if ( !is_admin() ) return;
		
		if ( empty($this->checkPeriod) ){
			return;
		}
		
		$state = get_option($this->optionName);
	
		$shouldCheck =
			empty($state) ||
			!isset($state->lastCheck) || 
			( (time() - $state->lastCheck) >= $this->checkPeriod*3600 );
		//$shouldCheck = true;
		
		if ( $shouldCheck ){
			$this->checkForUpdates();
		}
	}
	
	/**
	 * Intercept plugins_api() calls that request information about our plugin and 
	 * use the configured API endpoint to satisfy them. 
	 * 
	 * @see plugins_api()
	 * 
	 * @param mixed $result
	 * @param string $action
	 * @param array|object $args
	 * @return mixed
	 */
	function injectInfo($result, $action = null, $args = null){
    	$relevant = ($action == 'plugin_information') && isset($args->slug) && ($args->slug == $this->slug);
		if ( !$relevant ){
			return $result;
		}
		$state = get_option($this->optionName);
		if( !empty($state) && isset($state->update) ) {
			$state->update->name = $this->pluginName;
			$result = PU_PluginInfo::fromJson($state->update,true);;
			$updates = $result->toWpFormat();
		}
		//$pluginInfo = $this->requestInfo(array('pu_checking_for_updates' => '1'));
		//if ($pluginInfo){
			//return $pluginInfo->toWpFormat();
		//}
		if ( $updates )		
			return $updates;
		else
			return $result;
	}
	
	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 * 
	 * @param array $updates Update list.
	 * @return array Modified update list.
	 */
	function injectUpdate($updates){
		$state = get_option($this->optionName);
		//Is there an update to insert?
		if ( !empty($state) && isset($state->update) && !empty($state->update) ){
			//Only insert updates that are actually newer than the currently installed version.
			if ( version_compare($state->update->version, $this->getInstalledVersion(), '>') ){
				$updates->response[$this->pluginFile] = $state->update->toWpFormat();
			}
		}
		add_action('after_plugin_row_'.$this->pluginFile, array(&$this, 'in_plugin_update_message'));
		if ( $this->json_error )
			remove_action('after_plugin_row_'.$this->pluginFile, 'wp_plugin_update_row', 10, 2);		
		return $updates;
	}
	
	/**
	 * Register a callback for filtering query arguments. 
	 * 
	 * The callback function should take one argument - an associative array of query arguments.
	 * It should return a modified array of query arguments.
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback 
	 * @return void
	 */
	function addQueryArgFilter($callback){
		add_filter('puc_request_info_query_args-'.$this->slug, $callback);
	}
	
	/**
	 * Register a callback for filtering arguments passed to wp_remote_get().
	 * 
	 * The callback function should take one argument - an associative array of arguments -
	 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
	 * for details on what arguments are available and how they work. 
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback
	 * @return void
	 */
	function addHttpRequestArgFilter($callback){
		add_filter('puc_request_info_options-'.$this->slug, $callback);
	}
	
	/**
	 * Register a callback for filtering the plugin info retrieved from the external API.
	 * 
	 * The callback function should take two arguments. If the plugin info was retrieved 
	 * successfully, the first argument passed will be an instance of  PU_PluginInfo. Otherwise, 
	 * it will be NULL. The second argument will be the corresponding return value of 
	 * wp_remote_get (see WP docs for details).
	 *  
	 * The callback function should return a new or modified instance of PU_PluginInfo or NULL.
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback
	 * @return void
	 */
	function addResultFilter($callback){
		add_filter('puc_request_info_result-'.$this->slug, $callback, 10, 2);
	}
}
	
endif;

if ( !class_exists('PU_PluginInfo') ):

/**
 * A container class for holding and transforming various plugin metadata.
 * @version 1.1
 * @access public
 */
class PU_PluginInfo {
	//Most fields map directly to the contents of the plugin's info.json file.

	public $name;
	public $slug;
	public $version;
	public $homepage;
	public $sections;
	public $download_url;

	public $author;
	public $author_homepage;
	
	public $requires;
	public $tested;
	public $upgrade_notice;
	
	public $rating;
	public $num_ratings;
	public $downloaded;
	public $last_updated;
	public $render_pass;
	
	public $id = 0; //The native WP.org API returns numeric plugin IDs, but they're not used for anything.
		
	/**
	 * Create a new instance of PU_PluginInfo from JSON-encoded plugin info 
	 * returned by an external update API.
	 * 
	 * @param string $json Valid JSON string representing plugin info. 
	 * @return PU_PluginInfo New instance of PU_PluginInfo, or NULL on error.
	 */
	public static function fromJson($json, $object = false){
		$apiResponse = (!$object) ? json_decode($json) : $json;
		if ( empty($apiResponse) || !is_object($apiResponse) ){
			return null;
		}
		
		//Very, very basic validation.
		$valid = (isset($apiResponse->name) && !empty($apiResponse->name) && isset($apiResponse->version) && !empty($apiResponse->version)) || (isset($apiResponse->api_invalid) || isset($apiResponse->no_api));
		if ( !$valid ){
			return null;
		}
		
		$info = new PU_PluginInfo();
		
		foreach(get_object_vars($apiResponse) as $key => $value){
			$key = str_replace('plugin_', '', $key); //let's strip out the "plugin_" prefix we've added in plugin-updater-classes.
			$info->$key = $value;
		}
		
		return $info;		
	}
	
	/**
	 * Transform plugin info into the format used by the native WordPress.org API
	 * 
	 * @return object
	 */
	public function toWpFormat(){
		$info = new StdClass;
		
		//The custom update API is built so that many fields have the same name and format
		//as those returned by the native WordPress.org API. These can be assigned directly. 
		
		$sameFormat = array(
			'name', 'slug', 'version', 'requires', 'tested', 'rating', 'upgrade_notice',
			'num_ratings', 'downloaded', 'homepage', 'last_updated',
		);
		foreach($sameFormat as $field){
			if ( isset($this->$field) ) {
				$info->$field = $this->$field;
			}
		}
		
		//Other fields need to be renamed and/or transformed.
		$info->download_link = $this->download_url;
		
		if ( !empty($this->author_homepage) ){
			$info->author = sprintf('<a href="%s">%s</a>', $this->author_homepage, $this->author);
		} else {
			$info->author = $this->author;
		}
		
		if ( is_object($this->sections) ){
			$info->sections = get_object_vars($this->sections);
		} elseif ( is_array($this->sections) ) {
			
			$info->sections = $this->sections;
			
		} else {
			$info->sections = array('description' => '');
		}
				
		return $info;
	}
}
	
endif;

if ( !class_exists('PluginUpdateUtility') ):

/**
 * A simple container class for holding information about an available update.
 * 
 * @version 1.1
 * @access public
 */
class PluginUpdateUtility {
	public $id = 0;
	public $slug;
	public $version;
	public $homepage;
	public $download_url;
	public $sections = array();
	public $upgrade_notice;
	
	/**
	 * Create a new instance of PluginUpdateUtility from its JSON-encoded representation.
	 * 
	 * @param string $json
	 * @return PluginUpdateUtility
	 */
	public static function fromJson($json){
		//Since update-related information is simply a subset of the full plugin info,
		//we can parse the update JSON as if it was a plugin info string, then copy over
		//the parts that we care about.
		$pluginInfo = PU_PluginInfo::fromJson($json);
		if ( $pluginInfo != null ) {
			return PluginUpdateUtility::fromPluginInfo($pluginInfo);
		} else {
			return null;
		}
	}
	
	/**
	 * Create a new instance of PluginUpdateUtility based on an instance of PU_PluginInfo.
	 * Basically, this just copies a subset of fields from one object to another.
	 * 
	 * @param PU_PluginInfo $info
	 * @return PluginUpdateUtility
	 */
	public static function fromPluginInfo($info){
		$update = new PluginUpdateUtility();
		$copyFields = array('id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice', 'sections');
		foreach($copyFields as $field){
			$update->$field = $info->$field;
		}
		return $update;
	}
	
	/**
	 * Transform the update into the format used by WordPress native plugin API.
	 * 
	 * @return object
	 */
	public function toWpFormat(){
		$update = new StdClass;
		
		$update->id = $this->id;
		$update->slug = $this->slug;
		$update->new_version = $this->version;
		$update->url = $this->homepage;
		$update->package = $this->download_url;
		if ( !empty($this->upgrade_notice) ){
			$update->upgrade_notice = $this->upgrade_notice;
		}
		
		return $update;
	}
}
	
endif;