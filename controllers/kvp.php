<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

final class Katalyst_Video_Plus {
	
	private $admin;
	private $front_end;
	
	public function __construct() {
		
		if( is_admin() )
			$this->admin();
		
		else
			$this->front_end();
		
		if( !wp_next_scheduled('kvp_cron') )
			wp_schedule_event( time(), 'hourly', 'kvp_cron' );
		
		add_action('kvp_cron', array(@$this, 'cron') );
		
	}
	
	private function admin() {
		include_once(KVP__PLUGIN_DIR . 'controllers/admin.php');
		$this->admin = new KVP_Admin();
	}
	
	private function front_end() {
		include_once(KVP__PLUGIN_DIR . 'controllers/front-end.php');
		$this->front_end = new KVP_Front_End();
	}
	
	public function cron() {
	
		include_once( KVP__PLUGIN_DIR . 'models/import.php' );
		
		$providers = apply_filters('kvp_providers', array() );
		$sources   = get_option('kvp_sources');
		
		foreach( $sources as $source ) {
			
			do_action('kvp_load_' . $source['provider'] . '_import_files');
			
			if( !isset($providers[$source['provider']]) )
				continue;
			
			if( !class_exists($providers[$source['provider']]['class']) )
				continue;
			
			$process = new $providers[$source['provider']]['class'];
			$process->import($source, 'cron');
			
		}
		
	}
	
}