<?php
/**
 * @package		Educare
 * @version 	1.4.4
 * @author	  	FixBD <fixbd.org@gmail.com>
 * @copyright  	GPL-2.0+
 * @link		http://github.com/fixbd/educare
 * @license	  	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * 
 * Plugin Name:  Educare
 * Plugin URI:	 http://github.com/fixbd/educare
 * Description:	 Educare is a powerful online School/College students & results management system dev by FixBD. This plugin allows you to manage and publish students results. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year Custom field and much more... Also you can import & export unlimited students and results just a click!
 * Version:      1.4.4
 * Author:       FixBD
 * Author URI:   http://github.com/fixbd
 * License:		 GPL-2.0+
 * Text Domain:  Educare
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
 * 
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


if ( ! defined( 'EDUCARE_VERSION' ) ) {
	/**
	 * Plugin Name-Space For Educare.
	 *
	 * @since 1.2.0
     * 
	 */

    // Make it simple! (Define Educare Name-Space)
    // Plugin Version
	define('EDUCARE_VERSION', '1.4.4');
    // Settings Version
    define('EDUCARE_SETTINGS_VERSION', '1.0');
    define('EDUCARE_RESULTS_VERSION', '1.0');
    // Educare Dir Path
    define('EDUCARE_DIR', plugin_dir_path(__FILE__));
    define('EDUCARE_INC', EDUCARE_DIR.'includes'.'/');
    define('EDUCARE_ADMIN', EDUCARE_INC.'admin'.'/');
    define('EDUCARE_TEMP', EDUCARE_DIR.'templates'.'/');
    define('EDUCARE_FOLDER', basename(dirname(__FILE__)));
    define('EDUCARE_URL', plugin_dir_url(EDUCARE_FOLDER).EDUCARE_FOLDER.'/');
}

// Create a database table for plugin settings and student results system
require_once(EDUCARE_INC.'database/educare-database.php');

// Activation action
register_activation_hook( __FILE__, 'educare_database_table' );
// Uninstall action
register_uninstall_hook( __FILE__, 'educare_uninstall_action' );

// Include plugin functions
require_once(EDUCARE_INC.'functions.php');
// Add educare admin css and script
require_once(EDUCARE_INC.'support/educare-themes.php');
// Educare results system (front view)
require_once(EDUCARE_TEMP.'users/results_systems.php');


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
    function educare_action_links($links, $file) {
        // Declare a static variable to store the plugin's main file name.
        static $educare;

        // Get the plugin's main file name using plugin_basename function.
        if (!$educare) {
            $educare = plugin_basename(__FILE__);
        }

        // Define the custom action links to be added.
        $action_links = array(
            'settings' => 'Settings',
            'management' => 'Management',
            'all-results' => 'All Results',
            'all-students' => 'All Students'
        );

        // Loop through each custom action link and add it to the $links array.
        foreach ($action_links as $url => $title) {
            // Check if the current plugin file matches the plugin's main file.
            if ($file == $educare) {
                // Create the HTML link with the appropriate URL and title.
                $in = '<a href="' . esc_url('admin.php?page=educare-'.$url) . '">' . esc_html($title) . '</a>';
                // Add the custom action link to the beginning of the $links array.
                array_unshift($links, $in);
            }
        }        
        
        // Return the modified array of action links.
        return $links;
    }

    // Add the 'educare_action_links' function as a filter to modify plugin action links.
    add_filter('plugin_action_links', 'educare_action_links', 10, 2);
}



?>