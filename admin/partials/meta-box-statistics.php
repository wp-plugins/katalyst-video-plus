<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Displays statistics on KVP usage
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/
global $wpdb;

$sources	= get_option( 'kvp_accounts', array() );

$services['inactive'] = array(
	'label'		=> __( 'Inactive Service', 'kvp' ),
	'color'		=> '#34495e',
	'highlight'	=> '#2c3e50',
	'value'		=> null
);

$services		= apply_filters( 'kvp_services', $services );
$services_data	= array();
$authors		= array();
$authors_data	= array(
	
	array(
		'value'	=> null,
		'color'	=> '#e74c3c',
		'highlight'	=> '#c0392b',
		'label'	=> null,
	),
	
	array(
		'value'	=> null,
		'color'	=> '#e67e22',
		'highlight'	=> '#d35400',
		'label'	=> null,
	),
	
	array(
		'value'	=> null,
		'color'	=> '#f1c40f',
		'highlight'	=> '#f39c12',
		'label'	=> null,
	),
	
	array(
		'value'	=> null,
		'color'	=> '#2ecc71',
		'highlight'	=> '#27ae60',
		'label'	=> null,
	),
	
	array(
		'value'	=> null,
		'color'	=> '#3498db',
		'highlight'	=> '#2980b9',
		'label'	=> null,
	),
	
);

foreach( $services as $service => $settings ) {
	
	$services[$service]['value'] = 0;
	
}

$users_data = array();


foreach( $services as $service => $data ) {
	
	$services_data[] = $data;
	
}

$posts_meta	= $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '%s'", '_kvp') );

foreach( $posts_meta as $post_meta ) {
	
	$post_meta = unserialize( $post_meta );
	
	if( isset($services[$post_meta['service']]['value']) )
		++$services[$post_meta['service']]['value'];
	
	else
		++$services['inactive']['value'];
	
	
}

function usort_callback( $a, $b ) {
	
	if( $a['count'] == $b['count'] )
		return 0;
	
	return ( $a['count'] > $b['count'] ) ? -1 : 1;
	
}

usort( $authors, 'usort_callback' );
$authors = array_slice( $authors, 0, 5 );

foreach( $authors as $key => $data ) {
	
	$authors_data[$key]['label'] = get_the_author_meta( 'display_name', $data['author'] );
	$authors_data[$key]['value'] = $data['count'];
	
}

?>
<div style="display: inline-block;">
	<h3><?php _e( 'Videos by Service', 'kvp' ); ?></h3>
	<canvas id="service-percentage" width="400" height="200"></canvas>
</div>
<div style="display: inline-block;">
	<h3><?php _e( 'Top Contributing Authors', 'kvp' ); ?></h3>
	<canvas id="top-authors" width="400" height="200"></canvas>
</div>
<script>
(function( $ ) {
	var services 		= $('#service-percentage').get(0).getContext('2d');
	var services_data	= <?php echo json_encode( $services ); ?>;
	var services_chart	= new Chart(services).Doughnut( services_data );
	
	var authors 		= $('#top-authors').get(0).getContext('2d');
	var authors_data	= <?php echo json_encode( $authors_data ); ?>;
	var authors_chart	= new Chart(authors).Doughnut( authors_data );
	
})( jQuery );
</script>