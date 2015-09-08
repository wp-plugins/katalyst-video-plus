<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/**
* @link             http://katalystvideoplus.com
* @since            2.0.0
* @package          Katalyst_Video_Plus
*
* @wordpress-plugin
* Plugin Name:      Katalyst Video Plus
* Plugin URI:       http://katalystvideoplus.com/
* Description:      Create a multiple source video network with WordPress.
* Version:          3.2.1
* Author:           Keiser Media Group
* Author URI:       http://keisermedia.com/
* License:          GPL-2.0+
* License URI:      http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:      katalyst-video-plus
* Domain Path:      /lang
*
*	Copyright 2013  keisermedia.com  (email: support@keisermedia.com)
*
*	This program is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   This program is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
**/

require_once plugin_dir_path( __FILE__ ) . 'inc/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-katalyst-video-plus.php';

register_activation_hook( __FILE__, array( 'Katalyst_Video_Plus_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Katalyst_Video_Plus_Deactivator', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function run_katalyst_video_plus () {

	$katalyst_video_plus = new Katalyst_Video_Plus( 'Katalyst Video Plus', '3.2.1', '3.4' );
	$katalyst_video_plus->run();

}
run_katalyst_video_plus();