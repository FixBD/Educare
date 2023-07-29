<?php
/**
 * ### Educare Marksheed Systems
 * 
 * Using this features admin (teacher) can add subject wise multiple student results at a same time. So, it's most usefull for (single) teacher. There are different teachers for each subject. Teachers can add marks for their specific subject using this feature. And can print all student marks as a marksheet. After, the mark addition is done for all the subjects, students can view and print their results when admin publish it as results. Also, teacher can publish single subject results. (We call it - THE GOLDEN FEATURES FOR TEACHER!).
 * 
 * @since 1.2.4
 * @last-update 1.4.0
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (educare_database_check('educare_marks')) {
	educare_database_table('educare_marks');
}

// Create tab
$action = 'mark-sheed';
$tab = array (
	// Tab name => Icon
  'add_marks' => 'text-page',
  // 'import_marks' => 'database-import'
);

educare_tab_management($action, $tab);

?>

