<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Displays KVP Source test
*
* @link       http://katalystvideoplus.com
* @since      3.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/
?>
<div id="#source-test-results">
<?php if( isset($results['error']) && 200 != $results['error']['code'] ) : ?>
	<div class="no-video-results"><?php echo $results['error']['message']; ?></div>
<?php else : ?>
	<p style="text-align: right;"><span style="float:left;"><?php printf( __( 'Scanned: %d New / %d Videos | %d Duplicates', 'kvp' ), count($results['items']), $results['page_info']['scanned'], $results['page_info']['duplicates'] ); ?></span>
		<?php printf( __( 'Total Videos: %d | Execution Time: %ss', 'kvp'), $results['page_info']['total'], $results['page_info']['execution_time'] ); ?>
	</p>
	<?php if( 0 == $results['page_info']['total'] ) : ?>
	<div class="no-video-results"><?php _e( 'No videos match your request.', 'kvp' ); ?></div>
	<?php elseif( 0 == count($results['items']) ) : ?>
	<div class="no-video-results"><?php _e( 'No new videos found.', 'kvp' ); ?></div>
	<?php else : ?>
	<div class="video-group">
	<?php foreach( $results['items'] as $item ) : ?>
		<div class="video-card">
			<div class="name column-name">
				<h4><?php echo $item['title']; ?></h4>
			</div>
			<img src="<?php echo $item['thumbnail']; ?>" class="video-thumb" />
		</div>
	<?php endforeach; ?>
	</div>
	<?php endif; ?>
<?php endif; ?>
</div>