<?php

/** =====================( Functions Details )======================
	
	# function for default settings

	* @since 1.0.0
	* @last-update 1.2.0

	* @param mixed $list			For Settings, Class, Exam, Year, Extra field
	* @return void
	
===================( function for default settings )=================== **/

function educare_add_default_settings($list, $show_data = null, $new_data = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";

	if ($list == 'Settings') {
		$target = array(
			'confirmation' => 'checked',
			'guide' => 'checked',
			'photos' => 'checked',
			'auto_results' => 'checked',
			'advance' => 'unchecked',
			'problem_detection' => 'checked',
			'clear_data' => 'unchecked',
			'custom_results' => 'unchecked',
			'results_page' => 'results',
			'students_page' => 'students',
			'institute' => 'Name Of The Institutions (Title) Or Slogan',
			'optional_sybmbol' => '✓',
			'display' => [
				'Name' => ['Name', 'checked'],
				'Roll_No' => ['Roll No', 'checked'],
				'Regi_No' => ['Regi No', 'checked'],
				'Class' => ['Class', 'checked'],
				'Exam' => ['Exam', 'checked'],
				'Year' => ['Year', 'checked']
			],
			'grade_system' => [
				'current' => 'Default',
				'rules' => [
					'Default' => [
						'80-100' => [5, 'A+'],
						'70-79'  => [4, 'A'],
						'60-69'  => [3.5, 'A-'],
						'50-59'  => [3, 'B'],
						'40-49'  => [2, 'C'],
						'33-39'  => [1, 'D'],
						'0-32'  => [0, 'F']
					]
				]
			],
			'educare_info' => [
				'version' => '1.2.0',
				'educare_settings' => '1.0',
				'educare_results' => '1.0',
				'package' => 'free'
			]
		);
	}
	
	if ($list == 'Class') {
		$subject = array('English', 'Mathematics', 'ICT');
		$target = array(
			'Class 6' => $subject,
			'Class 7' => [],
			'Class 8' => []
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
			'2022',
			'2023',
			'2024'
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

	if ($show_data) {
		return $target;
	} else {
		$search = $wpdb->get_results("SELECT * FROM $table WHERE list='$list'");
		if ($new_data) {
			$target = $new_data;
		}
	}
	
	if ($search) {
		foreach ($search as $print) {
			$id = $print->id;
			unset($print->id);
			$print->data = json_encode($target);
		}

		$print = json_decode(json_encode($print), TRUE);
		$wpdb->update($table, $print, array('ID' => $id));
		
	} else {
		$wpdb->insert($table, array(
			"list" => $list,
			"data" => json_encode($target)
		));
	}
}

// create function for store default settings/all in one
function educare_default_settings() {
	educare_add_default_settings('Settings');
	educare_add_default_settings('Class');
	educare_add_default_settings('Exam');
	educare_add_default_settings('Year');
	educare_add_default_settings('Extra_field');
}


?>