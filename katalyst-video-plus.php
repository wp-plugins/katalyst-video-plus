<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }
/*
Plugin Name: Katalyst Video Plus
Plugin URI: http://keisermedia.com/projects/katalyst-video-plus
Description: Automatically import and integrate videos from hosting providers.
Author: Keiser Media Group
Author URI: http://keisermedia.com/
Version: 1.1.0
Text Domain: kvp
Domain Path: /languages
License: GPL3

	Copyright 2013  keisermedia.com  (email: support@keisermedia.com)

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

define( 'KVP__VERSION', '1.0.0' );
define( 'KVP__MINIMUM_WP_VERSION', '3.5' );
define( 'KVP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

include_once( KVP__PLUGIN_DIR . 'controllers/kvp.php' );

$kvp = new Katalyst_Video_Plus();