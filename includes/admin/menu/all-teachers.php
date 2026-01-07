<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Manage Educare Teachers
 * 
 * Here admin can add, edit, update teachers and their details. For this you have to select the options that you see here. Options details: firt to last (All, Add, Update, Import teachers).
 * 
 *  @since 1.0.0
 *  @last-update 1.4.2
 */

educare_check_access();
educare_get_data_management('teachers');

?>


