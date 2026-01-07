<?php
/**
 * @package		Educare
 * @version 	1.6.3
 * @author	  	FixBD <fixbd.org@gmail.com>
 * @copyright  	GPL-2.0+
 * @link		http://github.com/fixbd/educare
 * @license  	https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Plugin Name:  Educare
 * Plugin URI:	 http://wordpress.org/plugins/educare/
 * Description:	 Educare is a powerful online School/College students & results management system dev by FixBD. This plugin allows you to manage and publish students results. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year Custom field and much more... Also you can import & export unlimited students and results just a click!
 * Version:      1.6.3
 * Author:       FixBD
 * Author URI:   http://fixbd.com
 * License:		 GPL-2.0+
 * Text Domain:  educare
 * 
 * Attention please...
 * Educare is a free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation. either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, see <https://www.gnu.org/licenses/>.
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Make it simple! (Define paths)
define('EDUCARE_FILE', plugin_basename( __FILE__ ));
define('EDUCARE_DIR', plugin_dir_path(__FILE__));
define('EDUCARE_INC', EDUCARE_DIR.'includes'.'/');
define('EDUCARE_ADMIN', EDUCARE_INC.'admin'.'/');
define('EDUCARE_TEMP', EDUCARE_DIR.'templates'.'/');
define('EDUCARE_FOLDER', basename(dirname(__FILE__)));
define('EDUCARE_URL', plugin_dir_url(EDUCARE_FOLDER).EDUCARE_FOLDER.'/');
define('EDUCARE_VERSION', '1.6.3');
define('EDUCARE_SETTINGS_VERSION', '1.0');
define('EDUCARE_RESULTS_VERSION', '1.0');

// Include plugin functions
require_once(EDUCARE_INC.'functions.php');
// Create a database table for plugin settings and student results system
require_once(EDUCARE_INC.'database/educare-database.php');

// Activation hook 1: Add database
register_activation_hook( __FILE__, 'educare_database_table' );
// Seactivation hook
register_deactivation_hook( __FILE__, 'educare_deactivation_hook' );
// Uninstall hook
register_uninstall_hook( __FILE__, 'educare_uninstall_action' );

/**
 * Adds custom action links to the plugin entry in the WordPress admin dashboard.
 *
 * This function is used to modify the action links displayed for the plugin in the
 * list of installed plugins in the WordPress admin dashboard. The action links provide
 * quick access to specific pages or actions related to the plugin.
 *
 * @param array $links An array of existing action links for the plugin.
 * @param string $file The main file of the current plugin.
 * @return array Modified array of action links.
 */
if (!function_exists('educare_action_links')) {
    
    function educare_action_links( $links, $file ) {
        static $educare;
        
        if (!$educare) {
            $educare = plugin_basename(__FILE__);
        }

        $action_links = array (
            // 'link || lug' => 'titile',
            'settings' => 'Settings',
            'management' => 'Management',
            'all-results' => 'All Results',
            'all-students' => 'All Students'
        );

        foreach ($action_links as $url => $title) {
            if ($file == $educare) {
                $url = 'admin.php?page=educare-' . esc_attr($url);
                $in = '<a href="'. esc_url($url) .'">'. esc_html($title) .'</a>';
                 // Add action link
                array_unshift($links, $in);
            }
        }
        
        return $links;
    }

    // add options after plugin activation
    add_filter( 'plugin_action_links', 'educare_action_links', 10, 2 );
}


?>