<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

foreach( $this->providers as $provider => $info )
	$provider_opts	.= sprintf( '<option value="%1$s">%2$s</option>\n\r', $provider, $info['title'] );

?>
<?php if( empty($this->providers) ) : ?>

<p class="howto"><?php printf('<a href="%s" title="%s" target="_blank">%s</a>', 'http://wordpress.org/plugins/kvp-youtube-lite/', __('WordPress Repository', 'kvp'), __( 'Download a provider here.', 'kvp')); ?></p>

<?php else : ?>

<form method="post">
	<?php wp_nonce_field('kvp_add_source', '_kvp_nonce'); ?>
	<p><strong><?php _e( 'Provider', 'kvp'); ?></strong></p>
	<select id="provider" name="new_source[provider]"><?php echo $provider_opts; ?></select>
	<p><strong><?php _e( 'Username', 'kvp'); ?></strong></p>
	<input id="feed_id" name="new_source[username]" class="form-input-tip" size="16" value="" type="text">
	<p><strong><?php _e( 'Author', 'kvp'); ?></strong></p>
	<?php wp_dropdown_users( array( 'name' => 'new_source[author]' ) ); ?>
	<p class="howto"><?php _e('Posts from this source will be published under this author.'); ?></p>
	<p><strong><?php _e( 'Categories', 'kvp'); ?></strong></p>
	<?php post_categories_meta_box( get_default_post_to_edit( 'post', false ), $category_args ); ?>
	<input type="hidden" name="action" value="add" />
	<?php submit_button( __( 'Add Source', 'kvp'), 'primary large', 'submit', true, array( 'style' => 'float: right;' ) ); ?>

</form>

<?php endif;