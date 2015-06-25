<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Displays About Screen
*
* @link       http://katalystvideoplus.com
* @since      3.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

$display_version = get_option( 'kvp_version', __( 'Unknown', 'kvp' ) )
?>
<div class="wrap about-wrap">

	<h1><?php printf( __( 'Welcome to Katalyst Video Plus&nbsp;%s' ), $display_version ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating! Katalyst Video Plus provides a way of automatically creating videos posts in your website.' ), $display_version ); ?></div>

	<div class="kvp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>
	
	<div class="changelog headline-feature">
		<h2><?php _e( 'Introducing the New and Improved Katalyst Video Plus' ); ?></h2>
		<div class="feature-section">
			<h3><?php _e( 'Our newest version focuses on improved flexability.' ); ?></h3>
			<p><?php _e( 'KVP now allows you to import videos from channels, playlists, search terms, and by video ID.' ); ?></p>
		</div>

		<div class="clear"></div>
	</div>

	<hr />

	<div class="changelog feature-list">
		<h2><?php _e( 'Under the Hood' ); ?></h2>

		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Customizer API' ); ?></h4>
				<p><?php _e( 'Expanded JavaScript APIs in the customizer enable a new media experience as well as dynamic and contextual controls, sections, and panels.' ); ?></p>
			</div>
			<div class="last-feature">

			</div>
		</div>
		
	</div>

	<hr />

	<div class="changelog feature-list">

		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Keep Katalyst Video Plus FREE' ); ?></h4>
				<p><?php _e( "5 star ratings help us bring KVP to more users. More happy users mean more support, more features, and more of everything you know and love about Katalyst Video Plus. We couldn't do this without your support.", 'kvp' ); ?></p>
				<p><strong><?php _e( 'Rate it five stars today!', 'kvp' ); ?></strong> <a class="kvp-rating-link" href="http://wordpress.org/support/view/plugin-reviews/katalyst-video-plus?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a></p>
				<a href="http://wordpress.org/support/view/plugin-reviews/katalyst-video-plus?filter=5" target="_blank" class="button-primary"><?php _e( 'Rate It', 'kvp' ); ?></a>
			</div>
			<div class="last-feature">
				<ul>
					<li><a href="" target="_blank"><?php _e( 'Release Notes', 'kvp' ); ?></a>
					<li><a href="" target="_blank"><?php _e( 'Getting Started', 'kvp' ); ?></a>
					<li><a href="http://katalystvideoplus.com/extensions/" target="_blank"><?php _e( 'More Features', 'kvp' ); ?></a>
					<li><a href="http://katalystvideoplus.com/contact/" target="_blank"><?php _e( 'Support Resources', 'kvp' ); ?></a>
				</ul>
			</div>
		</div>

		<hr />

		<div class="return-to-dashboard">
			<?php if ( current_user_can( 'update_core' ) && isset( $_GET['updated'] ) ) : ?>
			<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>"><?php
				is_multisite() ? _e( 'Return to Updates' ) : _e( 'Return to Dashboard &rarr; Updates' );
			?></a> |
			<?php endif; ?>
			<a href="<?php echo esc_url( self_admin_url() ); ?>"><?php
				is_blog_admin() ? _e( 'Go to Dashboard &rarr; Home' ) : _e( 'Go to Dashboard' ); ?></a>
		</div>
	</div>
	
</div>