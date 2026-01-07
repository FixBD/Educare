<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * The StudentResult class handles various calculations and operations related to student results.
 */
class StudentResult {
	private $data;

	/**
	 * Constructor for the StudentResult class.
	 *
	 * @param object $print The student data object containing details like marks, subjects, and remarks.
	 */
	public function __construct($print, $sttgs = array()) {
		$this->data = $print;
		$this->settings = (object)$sttgs;
	}

	/**
	 * Get students based on current student data.
	 *
	 * @param bool $all Whether to retrieve all students' data or current students' data.
	 * @return array An array of student data.
	 */
	public function getStudents() {
		global $wpdb;
		$requred_fields = array();

		// Current students (previous exam) data
		$requred = educare_check_status('display');
		// Getting all requered field key and title
		$requred_fields = educare_requred_data($requred, true);
		$requred_fields = educare_get_requred_fields('students', $requred_fields);
		
		foreach ($requred_fields as $key => $value) {
			$val = isset($this->data->$key) ? sanitize_text_field( $this->data->$key ) : '';
			$requred_fields[$key] = $val;
		}

		$sql = array();
		
		foreach ($requred_fields as $key => $value) {
			$sql[$key] = sanitize_text_field( $value );
		}

		$sql = educare_get_sql_new('students', $sql);
		$studentsData = $wpdb->get_results($sql);
		
		return $studentsData;
	}

	/**
	 * Combine different terms marks into one value.
	 *
	 * @param object|null $subjectData Optional. The subject data containing marks for each term.
	 * @param bool $optional Whether to include optional subjects.
	 * @param bool $average Whether to calculate the average.
	 * @return array An array of combined marks.
	 */
	public function combineMarks($subjectData = null, $optional = false, $average = false) {
		if (!$subjectData) {
			$subjectData = $this->data->Subject;
		}

		if (is_string($subjectData)) {
			$subjectData = json_decode($subjectData);
		}
    
    $combineMarks = array();
		$combine_terms = array (
			'marks'
		);

		$remove_terms = array();

		foreach ($subjectData as $sub_name => $sub_terms) {
			foreach ($sub_terms as $terms_key => $terms_value) {
				if (in_array($terms_key, $combine_terms)) {
					if (isset($remove_terms[$terms_key]) && !empty($remove_terms[$terms_key])) {
						$remove_terms[$terms_key] += intval($terms_value);
					} else {
						$remove_terms[$terms_key] = intval($terms_value);
					}
				}
			}
		};

		// new (cleaned) combine terms
		$combine_terms = array();
		
		foreach ($remove_terms as $terms_key => $terms_value) {
			if ($terms_value) {
				$combine_terms[] = $terms_key;
			}
		}
		
		$total_terms = count($combine_terms);

    foreach ($subjectData as $subject => $marks) {
			$combine = 0;

			foreach ($combine_terms as $term) {
				if (property_exists($marks, $term)) {
					if ($marks->$term) {
						$combine += $marks->$term;
					}
				}
			}

			$averageMarks = $combine;

			if ($total_terms) {
				$averageMarks /= $total_terms;

				if ($average) {
					$combine /= $total_terms;
				}
			}

			if (is_float($combine)) {
				$combine = number_format($combine, 2, '.', '');
			}
			if (is_float($averageMarks)) {
				$averageMarks = number_format($averageMarks, 2, '.', '');
			}

			if ($optional) {
				$marks->combine = $combine;
				$marks->average = $averageMarks;
				$combineMarks[$subject] = $marks;
			} else {
				$combineMarks[$subject] = $combine;
			}
    }

    return $combineMarks;
	}


	/**
	 * Calculate the average marks obtained by a student.
	 *
	 * Getting average marks obtained by student (based on combained marks)
	 * 
	 * @param object|null $subjectData Optional. The subject data containing marks.
	 * @param bool $total Whether to return the total marks.
	 * @return float|int The calculated average marks.
	 */
	public function getAverage($subjectData = null, $total = false, $filtering = true) {
		if (!$subjectData) {
			$subjectData = json_decode($this->data->Subject);
		}
    
    $subjects = $this->combineMarks($subjectData);
		$average = $failed = $failed_marks = 0;

		if ($subjects) {
			foreach ($subjects as $marks) {
				if ($marks <= 32) {
					$failed++;
					$failed_marks += $marks;
				}

				$average += $marks;
			}

			// return total marks
			if ($total) {
				return $average;
			}

			// Average marks. Devide with total subjects
			$average /= count($subjects);
			// remove unnececary digit
			if ($failed) {
				if (educare_check_status('position_filtering') == 'checked' && $filtering) {
					return '('.-esc_html($failed).') ' . round($average, 2);
				} else {
					return round($average, 2);
				}
			} else {
				return round($average, 2);
			}
			
		}
		
		return 0;
	}


	/**
	 * Calculate the GPA (Grade Point Average) for a student.
	 *
	 * @param array|null $subjects Optional. An array of subjects and marks.
	 * @return float The calculated GPA.
	 */
	public function getGPA($subjects, $term = 'combine') {
		if (!$subjects) {
			$subjects = $this->combineMarks('', true);
		}
		
		$subjectList = array();
		
		foreach ($subjects as $sub => $value) {
			$subjectList[$sub]['gpa'] = educare_letter_grade($value->$term, true);
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
				if ($subjectList) {
					$gpa /= count($subjectList);
				} else {
					$gpa = 0;
				}
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

	/**
	 * Get the status of the student (Passed/Failed/GPA) without html.
	 *
	 * @param string $showStatus The status to display (GPA by default).
	 * @param array|null $subjects Optional. An array of subjects and marks.
	 * @return mixed The student's status.
	 */
	public function getStatus($showStatus = 'GPA', $subjects = null, $term = 'combine' ) {
		if ($subjects) {
			$subjects = $this->combineMarks($subjects, true);
		} else {
			$subjects = $this->combineMarks('', true);
		}
		
		$gpa = $this->getGPA($subjects, $term);
		$passed = '<div class="success results_passed">'.__('Passed', 'educare').'</div>';
		$failed = '<div class="failed results_failed">'.__('Failed', 'educare').'</div>';
		
		if ($gpa) {
			$status = $passed;
		} else {
			$status = $failed;
		}
		
		// Get auto results, if auto result is checked
		if (educare_check_status('auto_results') == 'checked') {
			if ($showStatus === 'GPA') {
				return esc_html($gpa);
			} else {
				return wp_kses_post($status);
			}
	
		} else {
			if (property_exists($this->data, $showStatus)) {
				return esc_html($this->data->$showStatus);
			} else {
				return false;
			}
		}
	}
}


?>