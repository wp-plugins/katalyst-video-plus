<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* Registers KVP post types
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/inc
* @author     Keiser Media <support@keisermedia.com>
*/

class Katalyst_Video_Plus_Post_Types {
	
	/**
	 * The slug of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $slug;
	
	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @var      string    $slug       The slug of this plugin.
	 */
	public function __construct( $slug, $version ) {
		
		$this->slug		= $slug;
		$this->version	= $version;
		
	}
	
	/**
	 * Registers kvp_video post type
	 * 
	 * @since 3.0.0
	 */
	public function register_video() {
		
		$labels = array(
			'name'               => _x( 'Videos', 'post type general name', $this->slug ),
			'singular_name'      => _x( 'Video', 'post type singular name', $this->slug ),
			'menu_name'          => _x( 'Videos', 'admin menu', $this->slug ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', $this->slug ),
			'add_new'            => _x( 'Add New', 'Video', $this->slug ),
			'add_new_item'       => __( 'Add New Video', $this->slug ),
			'new_item'           => __( 'New Video', $this->slug ),
			'edit_item'          => __( 'Edit Video', $this->slug ),
			'view_item'          => __( 'View Video', $this->slug ),
			'all_items'          => __( 'All Videos', $this->slug ),
			'search_items'       => __( 'Search Videos', $this->slug ),
			'parent_item_colon'  => __( 'Parent Videos:', $this->slug ),
			'not_found'          => __( 'No videos found.', $this->slug ),
			'not_found_in_trash' => __( 'No videos found in Trash.', $this->slug )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'video' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_icon'			 => 'dashicons-video-alt3',
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);
		
		register_post_type( 'kvp_video', $args );
		
	}
	
	/**
	 * Registers video taxonomies
	 * 
	 * @since 3.0.0
	 */
	public function register_taxonomies() {
		
		$cat_labels = array(
			'name'              => _x( 'Video Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Video Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Video Categories' ),
			'all_items'         => __( 'All Video Categories' ),
			'parent_item'       => __( 'Parent Video Category' ),
			'parent_item_colon' => __( 'Parent Video Category:' ),
			'edit_item'         => __( 'Edit Video Category' ),
			'update_item'       => __( 'Update Video Category' ),
			'add_new_item'      => __( 'Add New Video Category' ),
			'new_item_name'     => __( 'New Video Category Name' ),
			'menu_name'         => __( 'Categories' ),
		);

		$cat_args = array(
			'hierarchical'      => true,
			'labels'            => $cat_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'video/category' ),
		);

		register_taxonomy( 'kvp_video_category', array( 'kvp_video' ), $cat_args );
		
	}
	
	public function enqueue_assets() {
		
		
		
	}
	
}