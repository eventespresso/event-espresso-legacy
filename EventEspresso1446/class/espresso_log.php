<?php

/**
 * * Singleton logging class. Can be called from anywhere in the plugin to log data to a log file.
 * * Defaults to wp-content/uploads/espresso/logs/espresso_log.txt
 * */
//Usage
//espresso_log::singleton()->log( array ( 'file' => __FILE__, 'function' => __FUNCTION__, 'status' => '[INSERT MESSAGE]' ) );

class espresso_log {

	var $file;
	private static $inst;

	//Set the file path - Change the file name is needed
	function __construct() {
		//echo __FILE__;
		//echo dirname( __FILE__ );
		$folder = EVENT_ESPRESSO_UPLOAD_DIR . 'logs/';
		//echo $folder;
		$this->file = $folder . 'espresso_log.txt';
		
		$uploads = wp_upload_dir();
		if (!is_dir(EVENT_ESPRESSO_UPLOAD_DIR) && is_writable($uploads['baseurl'])) {
			mkdir(EVENT_ESPRESSO_UPLOAD_DIR);
		}
		if (!is_dir(EVENT_ESPRESSO_UPLOAD_DIR.'logs') && is_writable(EVENT_ESPRESSO_UPLOAD_DIR)) {
			mkdir(EVENT_ESPRESSO_UPLOAD_DIR.'logs');
		}
		
		if (is_writable(EVENT_ESPRESSO_UPLOAD_DIR.'logs') && !file_exists($this->file)) {
			touch($this->file);
		}
	}

	public static function singleton() {
		if (!isset(self::$inst)) {
			$c = __CLASS__;
			self::$inst = new $c;
		}
		return self::$inst;
	}

	public function log($message) {
		if (is_writable($this->file)) {
			$fh = fopen($this->file, 'a') or die("Cannot open file! " . $this->file);
			
			$message['file'] = ! empty( $message['file'] ) ? basename( $message['file'] ) : '';
			$message['function'] = ! empty( $message['function'] ) ? '  -> ' . basename( $message['function'] ) : '';
			
			if ( is_array( $message['status'] )) {
				$msg = '';
				foreach ( $message['status'] as $key => $value ) {
					$msg .= ' & ' . $key . ' = ' . $value ;
				}
				$message['status'] = "\n\tVARS : " . ltrim( $msg, ' & ' );				
			} else {
				$message['status'] = ! empty( $message['status'] ) ? "\n\t" . $message['status'] : '';
			}			
			
			if ( ! empty( $message['file'] )) {
				fwrite( $fh, '[ ' . date("Y-m-d H:i:s") . ' ]  ' . $message['file'] . $message['function'] . $message['status'] . "\n" );
			} else {
				fwrite( $fh, "\n\n" );
			}
			fclose($fh);
		} else {
			global $notices;
			$notices['errors'][] = sprintf(__('Your log file is not writable. Check if your server is able to write to %s.', 'event_espresso'), $this->file);
		}
	}

	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

}

function espresso_do_log_entry($file, $function, $message) {
	espresso_log::singleton()->log(array('file' => $file, 'function' => $function, 'status' => $message));
}
global $org_options;
if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
	add_action('action_hook_espresso_log', 'espresso_do_log_entry', 10, 3);
}