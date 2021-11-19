<?php
/*
* function for default settings
* @param string $list	Settings, Subject, Exam, Class, Year, Extra field
* @return null
*/
function educare_add_default_settings($list) {
	global $wpdb;
	$table = $wpdb->prefix."Educare_settings";
   
	$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
	
	if (!$search) {
		
		if ($list == 'Settings') {
			$target = array(
				'confirmation'=>'checked',
				'guide'=>'checked',
				'photos'=>'checked',
				'auto_results'=>'checked',
				'advance'=>'unchecked',
				'delete_subject'=>'checked',
				'clear_field'=>'checked',
				'results_page'=>'results'
			);
		}
		
		if ($list == 'Subject') {
			$target = array(
				'English',
				'Mathematics',
				'ICT'
			);
		}
		
		if ($list == 'Class') {
			$target = array(
				'Class 6',
				'Class 7',
				'Class 8'
			);
		}
		
		if ($list == 'Exam') {
			$target = array(
				'Exam no 1',
				'Exam no 2',
				'Exam no 3'
			);
		}
		
		if ($list == 'Year') {
			$target = array(
				'2020',
				'2021',
				'2022'
			);
		}
		
		if ($list == 'Extra_field') {
			$target = array(
				// type => Name
				'date Date of Birth',
				'text Fathers Name',
				'text Mothers Name',
				'text Institute',
				'text Type',
				'email Email',
				'number Mobile No'
			);
		}
		
		$wpdb->insert($table, array(
			"list" => $list,
			"data" => json_encode($target)
	    ));
	}
}

// create function for apply default settings/all in one
function educare_default_settings() {
	educare_add_default_settings('Settings');
	educare_add_default_settings('Subject');
	educare_add_default_settings('Class');
	educare_add_default_settings('Exam');
	educare_add_default_settings('Year');
	educare_add_default_settings('Extra_field');
}



?>