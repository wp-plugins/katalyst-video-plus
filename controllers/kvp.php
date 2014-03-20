<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

final class Katalyst_Video_Plus {
	
	private $admin;
	private $front_end;
	
	public function __construct() {
		
		if( is_admin() )
			$this->admin();
		else
			$this->front_end();
		
		add_action('init', array($this, 'setup_cron') );
		
	}
	
	public function activation() {
		
		$this->purge_cron();
		
	}
	
	public function deactivation() {
		
		$this->purge_cron();
		
		
	}
	
	public function admin() {
		include_once(KVP__PLUGIN_DIR . 'controllers/admin.php');
		$this->admin = new KVP_Admin();
	}
	
	private function front_end() {
		include_once(KVP__PLUGIN_DIR . 'controllers/front-end.php');
		$this->front_end = new KVP_Front_End();
	}

	
	public function setup_cron() {
		
		if( !wp_next_scheduled('kvp_import_cron') )
			wp_schedule_event( time(), 'hourly', 'kvp_import_cron');
			
		add_action('kvp_import_cron', array($this, 'cron_event') );
		
	}
	
	private function purge_cron() {
		
		$crons = get_option('cron');
		
		foreach( $crons as $timestamp => $hooks ) {
			
			if( !is_array($hooks) )
				continue;
			
			foreach( $hooks as $hook => $keys ) {
				
				if( false === strpos($hook, 'kvp_') )
					continue;
					
				foreach( $keys as $k => $v )
					wp_unschedule_event($timestamp, $hook, $v['args']);
				
			}
			
		}
		
	}
	
	public function cron_event() {
		
		include_once( KVP__PLUGIN_DIR . 'models/import.php' );
		
		$providers = apply_filters('kvp_providers', array() );
		$sources   = get_option('kvp_sources');
		
		foreach( $sources as $id => $source ) {
			
			do_action('kvp_load_' . $source['provider'] . '_import_files');
			
			if( !isset($providers[$source['provider']]) ){
				echo $providers[$source['provider']];
				return;
			}
			
			if( !class_exists($providers[$source['provider']]['class']) ){
				echo $providers[$source['provider']]['class'];
				return;
			}
			$process = new $providers[$source['provider']]['class'];
			$process->import( $id );
			
		}
		
	}
	
}