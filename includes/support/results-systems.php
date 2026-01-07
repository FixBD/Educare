<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/** 
 * ### Function For Letter Grade
 * 
 * Create function {educare_letter_grade} for letter grade = A+, A, B, C, D, F (failed)
 * or points grade = 5, 4, 3.5, 3, 2, 1, 0 (based on default settings).
 * 
 * @since 1.0.0
 * @last-update 1.6.0
 * 
 * @param int $marks				Specific martks convert to grade or point
 * @param bull true/false 	For return grade points
 * 
 * @return string/int
 */

function educare_add_float_num($number) {
	// Ensure the number is converted to a float before addition
	return floatval($number) + 0.9;
}



/**
 * Converts numerical marks into letter grades based on a specified grade system.
 *
 * This function takes a numerical mark and optionally a flag to return point grades. It uses a predefined grade system to
 * convert the numerical mark into corresponding letter grades or point grades. The grade system is fetched from the
 * configuration and applied to determine the appropriate grade. Optional subject marks are also supported in the
 * numerical mark format. Additionally, the function handles displaying "success" or "failed" indicators based on the grade system.
 *
 * @param float|string $marks The numerical marks to be converted into letter grades or point grades.
 * @param bool|null    $points Optional. Whether to return point grades instead of letter grades. Default is null.
 * @return string The converted letter grade or point grade, or HTML indicators for "success" or "failed" status.
 */
function educare_letter_grade($marks, $points = null, $html = true) {
	/** 
	// ============== For Manually =================
	// check optional marks

	$optional_marks = substr(strstr($marks, ' '), 1);
	if ($optional_marks) {
		$marks = $optional_marks;
	}
	
	if ($marks >= 80 and $marks <= 100) {
			$marks = 'A+';
	}
	elseif ($marks >= 70 and $marks <= 79) {
			$marks = 'A';
	}
	elseif ($marks >= 60 and $marks <= 69) {
			$marks = 'A-';
	}
	elseif ($marks >= 50 and $marks <= 59) {
			$marks = 'B';
	}
	elseif ($marks >= 40 and $marks <= 49) {
			$marks = 'C';
	}
	elseif ($marks >= 33 and $marks <= 39) {
			$marks = 'D';
	} else {
		$marks = 'F';
	}
	*/

	$grade_system = educare_grading_system();
	$grade_system = (array)$grade_system;

	// Filter grade system.
	$grade_system = apply_filters( 'educare_set_grading_system', $grade_system );

	/** 
	* Check optional subject marks
	* Note: Educare add 1 before optional subject marks.
	* Exp: 1 85
	* Her 1 = optional subject and 85 = marks
	* In this way educare define and identify optional subjects. So, when you add a result to the csv files - you need to add 1 symbol before the optional subject
	*/
	if (strpos($marks, ' ')) {
		$optional_marks = substr(strstr($marks, ' '), 1);
		$marks = $optional_marks;
	} else {
		$optional_marks = false;
	}

	$hihest_marks = array_key_first($grade_system);
	$hihest_marks = substr(strstr($hihest_marks, '-'), 1);

	// check marks overflow
	$marks = ($marks > $hihest_marks) ? $hihest_marks : $marks;

	foreach ($grade_system as $rules => $grade) {
		// if ($rules == 'failed' or $rules == 'success') break;
		// get first rules number to compare
		$rules1 = strtok($rules, '-');
		// get second rules number to compare
		$rules2 =substr(strstr($rules, '-'), 1);

		if ($marks >= $rules1 and $marks <= educare_add_float_num($rules2)) {
			$marks = $grade[1];
		}
	}

	// return point grade if true
	if ($points) {
		if ($points === 'comments') {
			foreach ($grade_system as $rules => $grade) {
				if ($marks == $grade[1]) {
					$marks = $grade[2];
				}
			}
		} else {
			foreach ($grade_system as $rules => $grade) {
				if ($marks == $grade[1]) {
					$marks = $grade[0];
				}
			}
			
			if ($optional_marks) {
				$marks = "1 $marks";
			}
		}
	} else {
		// print success or failed
		$failed = end($grade_system);
		
		if ($html) {
			if ($marks == $failed[1]) {
				$marks = '<div class="failed">'.esc_html($marks).'</div>';
			} else {
				$marks = '<div class="success">'.esc_html($marks).'</div>';
			}
		}
	}
	
	return $marks;
	
}



/**  
 * usage: educare_get_marks($print);
 * 
 * @since 1.0.0
 * @last-update 1.2.0
 * 
 * @param object $print	Print specific subject value
 * 
 * @return int
 */
function educare_get_gpa($print) {
	$student = new StudentResult($print);
	$subjects = $student->combineMarks('', true);
	$subjectList = array();
	
	foreach ($subjects as $sub => $value) {
		$subjectList[$sub]['gpa'] = educare_letter_grade($value->combine, true);
		$subjectList[$sub]['optional'] = $value->optional;
	}

	$optinal_mark = 0;

	foreach ($subjectList as $sub => $value) {
		if ($value['optional']) {
			if ($value['gpa'] > 2) {
				$optinal_mark += $value['gpa'] - 2;
			}

			// remove optional subject
			unset($subjectList[$sub]);
		}
	}

	$pass = true;
	$sum = $optinal_mark;
	
	foreach ($subjectList as $sub => $value) {
		if ($value['gpa']) {
			$sum += $value['gpa'];
		} else {
			$pass = false;
		}
	}

	if ($pass === false) {
		$gpa = 0;
	} else {
		$gpa = $sum;
		if (count($subjectList) >= 0) {
			$gpa /= count($subjectList);
		}

		// adjust max value based on educare (settings) grading system
		$settings_rules = educare_check_status('grade_system');
		$current_rules = $settings_rules->current;
		$current_rules = $settings_rules->rules->$current_rules;
		$current_rules = json_decode(json_encode($current_rules), true);
		$max_rules = max($current_rules)[0];
		
		if ($gpa > $max_rules) {
			$gpa = $max_rules;
		}
	}

	return floor($gpa * 100) / 100;
}


// educare_get_gpa_to_letter_grade(4)
function educare_get_gpa_to_letter_grade($gpa) {
	$grade_system = educare_check_status('grade_system');
	$current = $grade_system->current;
	$grade_system = $grade_system->rules->$current;
	$grade_system = json_decode(json_encode($grade_system), true);

	foreach ($grade_system as $grade) {
		if ($gpa >= $grade[0] and $gpa <= educare_add_float_num($grade[0])) {
			// return letter grade
			return $grade[1];
		} else {
			if ($gpa >= $grade[0]) {
				return $grade[1];
			}
		}
	}

	return $gpa;
}

// Dont't close
