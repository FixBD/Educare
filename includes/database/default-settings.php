<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * ### Educare default settings
 *
 * @since 1.0.0
 * @last-update 3.4.8
 *
 * @param mixed $list			For Settings, Class, Group, Exam, Year, Extra_field
 * @return void
 */

function educare_add_default_settings($list, $show_data = null, $new_data = null) {
	global $wpdb;
	$table = $wpdb->prefix."educare_settings";

	if ($list == 'Settings') {
		// Default setting for settings
		$target = array(
			'confirmation' => 'checked',
			'guide' => 'checked',
			'show_cover_photos' => 'checked',
			'photos' => 'checked',
			'user_documents' => 'unchecked',
			'user_signature' => 'unchecked',
			'auto_results' => 'checked',
			'auto_regi_no' => 'unchecked',
			'position_filtering' => 'unchecked',
			'certificate_system' => 'unchecked',
			'rattings_system' => 'unchecked',
			'student_info' => 'checked',
			'details' => 'checked',
			'grade_sheet' => 'checked',
			'show_banner' => 'unchecked',
			'admin_area_banner' => 'unchecked',
			'quick_overview' => 'unchecked',
			'remarks' => 'unchecked',
			'publish_results_by_teachers' => 'unchecked',
			'print_only_remarks' => 'unchecked',
			'results_card_template' => 'educare_default_results_card',
			'search_form_template' => 'educare_default_search_form',
			'profiles_template' => 'educare_default_profiles',
			'certificate_template' => 'educare_default_certificate',
			'custom_certificate_bg' => '',
			'data_per_page' => 15,
			'update_anywhere' => 'checked',
			'advance' => 'checked',
			'problem_detection' => 'checked',
			'clear_data' => 'unchecked',
			'results_page' => 'results',
			'profiles_page' => 'my-profiles',
			'edit_profiles_page' => 'edit-profiles',
			'front_dashboard' => 'dashboard',
			'optional_sybmbol' => '✓',
			'passed' => 'Passed',
			'failed' => 'Failed',
			'group_subject' => 0,
			'staff_profiles' => 'unchecked',
			'user_profiles' => 'unchecked',
			'publish_user_by_admin' => 'unchecked',
			'publish_results_by_admin' => 'unchecked',
			'connect_wp_users' => 'unchecked',
			're_captcha' => 'unchecked',
			'site_key' => '',
			'secret_key' => '',
			'always_re_captcha' => 'checked',
			'display' => [
				'Name' => [
					'title' => 'Name',
					'status' => 'checked',
					'type' => 'text',
				],
				'Roll_No' => [
					'title' => 'Roll No',
					'status' => 'checked',
					'type' => 'text',
				],
				'Regi_No' => [
					'title' => 'Regi No',
					'status' => 'checked',
					'type' => 'text',
				],
				'Class' => [
					'title' => 'Class',
					'status' => 'checked',
					'type' => 'text',
				],
				'Group' => [
					'title' => 'Group',
					'status' => 'unchecked',
					'type' => 'text',
				],
				'Exam' => [
					'title' => 'Exam',
					'status' => 'checked',
					'type' => 'text',
				],
				'Year' => [
					'title' => 'Year',
					'status' => 'checked',
					'type' => 'number',
				],
				'Term' => [
					'title' => 'Term',
					'status' => 'unchecked',
					'type' => 'text',
				],
				'user_pin' => [
					'title' => 'User Pin',
					'status' => 'unchecked',
					'type' => 'password',
				],
				'School' => [
					'title' => 'School',
					'status' => 'unchecked',
					'type' => 'select',
				],
				'Staff_position' => [
					'title' => 'Staff Position',
					'status' => 'unchecked',
					'type' => 'select',
				],
			],
			'results_card' => [
				'details' => [
					'details' => [
						'title' => 'Details',
						'subtitle' => '',
						'status' => 'unchecked'
					],
					'result' => [
						'title' => 'Result',
						'subtitle' => '',
						'status' => 'checked'
					],
					'year' => [
						'title' => 'Year',
						'subtitle' => '',
						'status' => 'checked'
					],
					'gpa' => [
						'title' => 'GPA',
						'subtitle' => '',
						'status' => 'checked'
					]
				],

				'grade_sheet' => [
					'grade_sheet' => [
						'title' => 'Grade Sheet',
						'subtitle' => '',
						'status' => 'checked'
					],
					'no' => [
						'title' => 'No.',
						'subtitle' => '',
						'status' => 'checked'
					],
					'subject' => [
						'title' => 'Subject',
						'subtitle' => '',
						'status' => 'checked'
					]
				],

				'template' => [
					'educare_default_results_card' => [
						'marks' => [
							'title' => 'Marks',
							'subtitle' => 'Exam Marks',
							'status' => 'checked',
							'default_value' => '80',
							'placeholder' => '00',
							'type' => 'number',
							'auto' => 'off'
						],
						'gpa' => [
							'title' => 'GPA',
							'subtitle' => 'Number Points',
							'status' => 'checked',
							'default_value' => '5',
							'placeholder' => '5',
							'type' => 'number',
							'auto' => 'on'
						],
						'grade' => [
							'title' => 'Grade',
							'subtitle' => 'Letter Grade',
							'status' => 'checked',
							'default_value' => 'A+',
							'placeholder' => 'A+',
							'type' => 'text',
							'auto' => 'on'
						],
					]
				]
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
				'version' => '1.5.0',
				'educare_settings' => '1.0',
				'educare_results' => '1.0',
				'package' => 'pro'
			]
		);
	} elseif ($list == 'Class') {
		// Default setting for class
		$subject = array(
			'English',
			'Mathematics',
			'ICT',
			'History',
			'Social Studies',
			'Fine Arts',
			'Science',
			'Economics',
			'Religion',
			'Agriculture'
		);
		
		$target = array(
			'Class 6' => $subject,
			'Class 7' => [],
			'Class 8' => [],
			'Class 9' => [],
			'Class 10' => []
		);
	} elseif ($list == 'Group') {
		// Default group wise subject
		$sub_for_science = array(
			'Physics',
			'Chemistry',
			'Biology'
		);
		
		$sub_for_commerce = array(
			'Finance & Banking',
			'Accounting',
			'Business Ent'
		);

		// Default setting for group
		$target = array(
			'Science' => $sub_for_science,
			'Commerce' => $sub_for_commerce,
			'Arts' => []
		);
	} elseif ($list == 'Exam') {
		// Default setting for exam
		$target = array(
			'Exam no 1',
			'Exam no 2',
			'Exam no 3'
		);
	} elseif ($list == 'Year') {
		// Default setting for year
		$target = array(
			'2023',
			'2024',
			'2025',
		);
	} elseif ($list == 'Extra_field') {
		// Default setting for extra fiels
		$target = array(
			array (
				'title' => "Date of Birth",
				'type' => 'date',
        'for' => Array ('students', 'teachers', 'results')
			),
			array (
				'title' => "Father's Name",
				'type' => 'text',
        'for' => Array ('students', 'results')
			),
			array (
				'title' => "Mother's Name",
				'type' => 'text',
        'for' => Array ('students', 'results')
			),
			array (
				'title' => "Mobile No",
				'type' => 'number',
        'for' => Array ('students', 'teachers')
			)
		);
	} else {
		$target = array();
	}

	if ($show_data) {
		return $target;
	} else {
		$search = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $table WHERE list = %s", $list)
		);

		if ($new_data) {
			$target = $new_data;
		}
	}
	
	if ($search) {
		foreach ($search as $print) {
			if ($list == 'Settings') {
				$target = educare_encrypt_data($target);
			} else {
				$target = json_encode($target);
			}

			$id = $print->id;
			unset($print->id);
			$print->data = $target;
		}

		$print = json_decode(json_encode($print), TRUE);
		$wpdb->update($table, $print, array('ID' => $id));
		
	} else {
		if ($list == 'Settings') {
			$target = educare_encrypt_data($target);
		} else {
			$target = json_encode($target);
		}

		$wpdb->insert($table, array(
			"list" => $list,
			"data" => $target
		));
	}
}

// create function for store default settings/all in one
function educare_default_settings() {
	if (current_user_can( 'manage_options' )) {
		educare_add_default_settings('Settings');
		educare_add_default_settings('Class');
		educare_add_default_settings('Group');
		educare_add_default_settings('Exam');
		educare_add_default_settings('Year');
		educare_add_default_settings('Extra_field');
	}
}


?>