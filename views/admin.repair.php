<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); } ?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2><?php _e('Repair', 'kvp'); ?></h2>
	<?php
	if ( !empty($_REQUEST) && wp_verify_nonce($_REQUEST['_wpnonce'], 'kvp_repair_nonce' ) ) {
		
		echo __('Beginning Repair', 'kvp') . '<br />';
		
		foreach( $source_ids as $id ) {
			
			if( !isset($sources[$id]) ) {
				printf( __('Source %s does not exist.', 'kvp'), '\'<i>' . $id . '</i>\'' );
				continue;
			}
			
			$source = $sources[$id];
				
			
			do_action('kvp_load_' . $source['provider'] . '_import_files');
			
			if( !isset($this->providers[$source['provider']]) ) {
				printf( '<span class="error-message">' . __('Provider %s does not exist.', 'kvp') . '</span>', '\'<i>' . $source['provider'] . '</i>\'' );
				continue;
			}
			
			if( !class_exists($this->providers[$source['provider']]['class']) ) {
				printf( '<span class="error-message">' . __('Provider class %s does not exist.', 'kvp') . '</span>', '\'<i>' . $this->providers[$source['provider']]['class'] . '</i>\'' );
				continue;
			}
			
			if( !isset($source['errors']) || empty($source['errors']) )
				continue;
			
			$process = new $this->providers[$source['provider']]['class'];
			$process->import($source, 'repair');
		}
		
		echo __('Repair Complete', 'kvp') . '<br />';
		
	} else {
		_e('There was an error in processing your request.', 'kvp');
	}
	?>
</div>