<?php
/*
 * Plugin Name: WPfomify
 * Plugin URI: https://wpfomify.com
 * Version: 2.2.5
 * Description: Social Proof Marketing Plugin for WordPress.
 * Author: IdeaBox Creations
 * Author URI: https://ideabox.io
 * Copyright: (c) 2017 IdeaBox Creations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ibx-wpfomo
*/

define( 'IBX_WPFOMO_VER', '2.2.5' );
define( 'IBX_WPFOMO_DIR', plugin_dir_path( __FILE__ ) );
define( 'IBX_WPFOMO_URL', plugins_url( '/', __FILE__ ) );
define( 'IBX_WPFOMO_PATH', plugin_basename( __FILE__ ) );
define( 'IBX_WPFOMO_FILE', __FILE__ );

require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-loader.php';
