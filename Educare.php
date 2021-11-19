<?php
/**
 * Plugin Name:   Educare
 * Plugin URI:	   http://github.com/fixbd/educare
 * Description:	 Educare is a powerful online School/College students & results management system dev by FixBD . This plugin allows you to manage and publish students results. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year Custom field and much more... Also you can import & export unlimited students and results just a click!
 * Version:           1.0.0
 * Author:             FixBD
 * Author URI:     http://github.com/fixbd
 * License:		   GPL-2.0+
 * Text Domain:  Educare
 * 
 * Attention please...
 * Educare is a free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, see <https://www.gnu.org/licenses/>.
 *
 * @package		Educare
 * @version 		1.0.0
 * @author	  	 FixBD <fixbd.org@gmail.com>
 * @copyright  	GPL-2.0+
 * @link				http://github.com/fixbd/educare
 * @license	  	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Make it simple! (Define paths)
// You can use ### include "your/url/files.php";
define('EDUCARE_DIR', plugin_dir_path(__FILE__));
define('EDUCARE_INC', EDUCARE_DIR.'includes'.'/');
define('EDUCARE_ADMIN', EDUCARE_INC.'admin'.'/');
define('EDUCARE_TEMP', EDUCARE_DIR.'templates'.'/');
define('EDUCARE_FOLDER', basename(dirname(__FILE__)));
define('EDUCARE_URL', plugin_dir_url(EDUCARE_FOLDER).EDUCARE_FOLDER.'/');

// Create a database table for student results system
require_once(EDUCARE_INC.'database/educare-database.php');
register_activation_hook( __FILE__, 'educare_database_table' );

// Create a database table for plugin settings
require_once(EDUCARE_INC.'database/default-settings.php');
register_activation_hook( __FILE__, 'educare_default_settings' );

// Include plugin functions
require_once(EDUCARE_INC.'functions.php');

// add educare admin css and script
require_once(EDUCARE_INC.'support/educare-themes.php');


// educare results system (front view)
require_once(EDUCARE_TEMP.'users/results_systems.php');


// add options after plugin activation
add_filter( 'plugin_action_links', 'educare_action_links', 10, 2 );

function educare_action_links( $links, $file ) {
	static $educare;
	
    if (!$educare) {
        $educare = plugin_basename(__FILE__);
    }
    
    if ($file == $educare) {
        $in = '<a href="admin.php?page=educare-settings">' . __('Settings','educare') . '</a>';
        array_unshift($links, $in);
    }
    if ($file == $educare) {
        $in = '<a href="admin.php?page=educare-import-results">' . __('Import Results','educare') . '</a>';
        array_unshift($links, $in);
    }
    if ($file == $educare) {
        $in = '<a href="admin.php?page=educare-add-results">' . __('Add Results','educare') . '</a>';
        array_unshift($links, $in);
    }
    if ($file == $educare) {
        $in = '<a href="admin.php?page=educare-view-results">' . __('View Results','educare') . '</a>';
        array_unshift($links, $in);
    }
    
    return $links;
}


?>