<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Form for connecting WordPress to Service
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/
?>
<form method="post">
	<?php wp_nonce_field( 'kvp_connect_source', '_kvp_nonce' ); ?>
	<p><strong><?php _e( 'Source Name', 'kvp'); ?></strong></p>
	<input id="source_name" name="new_source[name]" class="form-input-tip" size="16" value="" type="text" autocomplete="off" />
	<p><strong><?php _e( 'Service', 'kvp'); ?></strong></p>
	<select id="source_service" name="new_source[service]">
	<?php foreach( $services as $service => $settings ) : ?>
		<option value="<?php echo $service; ?>"><?php echo $settings['label']; ?></option>
	<?php endforeach; ?>
	</select>
	<p><strong><?php _e( 'Type', 'kvp'); ?></strong></p>
	<select id="source_type" name="new_source[type]">
		<option value="" disabled selected><?php _e( 'Select Type', 'kvp' ); ?></option>
	<?php foreach( $types as $type => $label ) : ?>
		<option value="<?php echo $type; ?>"><?php echo $label; ?></option>
	<?php endforeach; ?>
	</select>
	<p class="howto hide-if-js"><?php _e( 'All types may not be available for all sources.', 'kvp' ); ?></p>
	<p><strong><?php _e( 'Items', 'kvp'); ?></strong></p>
	<textarea id="items" name="new_source[items]" class="widefat" cols="50" rows="5"></textarea>
	<p class="howto"><?php _e( 'Separate multiple items with a comma.', 'kvp' ); ?></p>
	<p><strong><?php _e( 'Author', 'kvp'); ?></strong></p>
	<?php wp_dropdown_users( array( 'name' => 'new_source[author]', 'who' => 'authors', ) ); ?>
	<p class="howto"><?php _e( 'Posts from this source will be published under this author.', 'kvp' ); ?></p>
	<p><strong><?php _e( 'Categories', 'kvp'); ?></strong></p>
	<?php post_categories_meta_box( get_default_post_to_edit( 'kvp_video' ), $category_args ); ?>
	<p><strong><?php _e( 'Schedule Frequency', 'kvp'); ?></strong></p>
	<select id="schedule_freq" name="new_source[schedule_freq]">
	<?php foreach( wp_get_schedules() as $freq => $val ) : ?>
		<option value="<?php echo $freq; ?>" <?php selected( 'hourly', $freq ); ?>><?php echo $val['display']; ?></option>
	<?php endforeach; ?>
	</select>
	<?php do_action( 'kvp_connect_source_after' ); ?>
	<input type="hidden" name="new_source[creator]" value="<?php echo get_current_user_id(); ?>" />
	<input type="hidden" name="new_source[status]" value="active" />
	<input type="hidden" name="action" value="connect_source" />
	<?php submit_button( __( 'Connect Source', 'kvp'), 'primary large', 'submit', true, array( 'style' => 'float: right;' ) ); ?>
</form>
<script>
	var services_types	= <?php echo json_encode($services_types); ?>;
</script>