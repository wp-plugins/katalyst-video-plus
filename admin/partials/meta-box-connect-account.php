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
	<?php wp_nonce_field('kvp_connect_account', '_kvp_nonce'); ?>
	<p><strong><?php _e( 'Service', 'kvp'); ?></strong></p>
	<select id="service" name="new_account[service]">
	<?php foreach( $services as $service => $settings ) : ?>
		<option value="<?php echo $service; ?>"><?php echo $settings['label']; ?></option>
	<?php endforeach; ?>
	</select>
	<p><strong><?php _e( 'Username', 'kvp'); ?></strong></p>
	<input id="feed_id" name="new_account[username]" class="form-input-tip" size="16" value="" type="text">
	<p><strong><?php _e( 'Author', 'kvp'); ?></strong></p>
	<?php wp_dropdown_users( array( 'name' => 'new_account[author]', 'who' => 'authors', ) ); ?>
	<p class="howto"><?php _e('Posts from this account will be published under this author.'); ?></p>
	<p><strong><?php _e( 'Categories', 'kvp'); ?></strong></p>
	<?php post_categories_meta_box( get_default_post_to_edit( 'post', false ), $category_args ); ?>
	<?php do_action( 'kvp_connect_account_after' ); ?>
	<input type="hidden" name="action" value="connect_account" />
	<?php submit_button( __( 'Connect Account', 'kvp'), 'primary large', 'submit', true, array( 'style' => 'float: right;' ) ); ?>
</form>