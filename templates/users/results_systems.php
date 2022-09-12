<?php

/** 
* ### Function For Letter Grade
* Create function {educare_letter_grade} for letter grade = A+, A, B, C, D, F (failed)
* or points grade = 5, 4, 3.5, 3, 2, 1, 0 (based on default settings).

* @since 1.0.0
* @last-update 1.2.0

* @param int $marks				Specific martks convert to grade or point
* @param bull true/false 	For return grade points

* @return string/int
*/

function educare_letter_grade($marks, $points = null) {
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

	$grade_system = educare_check_status('grade_system');
	$current = $grade_system->current;
	$grade_system = $grade_system->rules->$current;
	$grade_system = json_decode(json_encode($grade_system), true);

	/** 
	* Check optional subject marks
	* Note: Educare add 1 before optional subject marks.
	* Exp: 1 85
	* Her 1 = optional subject and 85 = marks
	* In this way educare define and identify optional subjects. So, when you add a result to the csv files - you need to add 1 symbol before the optional subject
	*/
	$optional_marks = substr(strstr($marks, ' '), 1);
	if ($optional_marks) {
		$marks = $optional_marks;
	}

	foreach ($grade_system as $rules => $grade) {
		// if ($rules == 'failed' or $rules == 'success') break;
		// get first rules number to compare
		$rules1 = strtok($rules, '-');
		// get second rules number to compare
		$rules2 =substr(strstr($rules, '-'), 1);

		if ($marks >= $rules1 and $marks <= $rules2) {
			$marks = $grade[1];
		}
	}

	// return point grade if true
	if ($points) {
		foreach ($grade_system as $rules => $grade) {
			if ($marks == $grade[1]) {
				$marks = $grade[0];
			}
		}
		if ($optional_marks) {
			$marks = "1 $marks";
		}
	} else {
		// print success or failed
		$failed = end($grade_system);
		
		if ($marks == $failed[1]) {
			$marks = '<div class="failed">'.esc_html($marks).'</div>';
		}
		else {
			$marks = '<div class="success">'.esc_html($marks).'</div>';
		}
	}
	
	return $marks;
	
}



/**  
### usage: educare_get_marks($print);

* @since 1.0.0
* @last-update 1.2.0

* @param object $print	Print specific subject value

* @return int
*/

function educare_get_marks($Subject) {
	$total_subject = 0;
	$points = array();

	foreach($Subject as $name => $mark) {
		$total_subject++;
		$points[] = educare_letter_grade($mark, true);
	}
	
	$add_optinal_mark = 0;
	$all_sub = $total_subject - 1;
	
	for ($x = 0; $x <= $all_sub; $x++) {
		$optinal = strtok($points[$x], ' ');
		$optinal = strtok($optinal);
		
		if ($optinal) {
			
			if ($optinal > 2) {
				$add_optinal_mark = $optinal - 2;
			} else {
				$add_optinal_mark = 0;
			}
		}
	}
	
	$main_subjects = array();
		foreach($points as $marks) {
			// detect optional subjects with [whtite-space]
			preg_match ('/ /', $marks, $optional); 
			if(count($optional) == 0) $main_subjects[] = $marks;
	}
	
	// $test = Array ( 0 => 3, 1=> 5, 2=> 4, 3=> 2 );
	
	/** 
	* You know, php array start in 0.
	* So, if your subject is 4 and we call $total_subject directly in loop expirations it's return 5 digit.
	* For example: 0, 1, 2, 3, 4. if you see loop start at 0 then 1, 2, 3..., our subject is 4 but it's looping 5 times. for solve this matter we need to decrease (4 - 1). here $total_subject = 4.
	# Notes: don't call $total_subject / --$total_subject directly in loop expirations. it's complicated the code!. first assign this a var. Let's do it-
	*/
	$count = count($main_subjects) - 1;
	
	$pass = true;
	for ($x = 0; $x <= $count; $x++) {
		$sub_points = $main_subjects[$x];
		if (empty ($sub_points)) {
			$pass = false;
		}
	}
	
	if ($pass == false) {
		$gpa = 0;
	} else {
		$gpa = array_sum($main_subjects);
		$gpa += $add_optinal_mark;
		if (count($main_subjects) >= 0) {
			$gpa /= count($main_subjects);
			// ignore unnecessary digits!
			$gpa = number_format((float)$gpa, 2, '.', '');
		}
	}
	if ($gpa > 5) {
		$gpa = "5.00";
	}
	return $gpa;
}



/** 
### Create function for display maarks/sanitize optional maarks
* Usage: echo educare_display_marks(85);

* @sincce 1.2.2
* @last-update 1.2.2

* @param object $marks 	init

* @return init
*/

function educare_display_marks($marks) {
	$optinal = strtok($marks, ' ');
	
	if ($optinal == 1) {
		$marks = substr(strstr($marks, ' '), 1) . ' ' . educare_check_status('optional_sybmbol');
	}

	return $marks;
}



/** 
### Create function for students result status
* Usage: echo educare_results_status($subject, $id);

* @sincce 1.0.0
* @last-update 1.2.2

* @param object $print 	Select specific subject
* @param int $id				Specific subject id
* @param int $gpa				return GPA if true, otherwise return passed/failed

* @return string|HTML
*/

function educare_results_status($subject, $id, $gpa = null) {
	$passed = "<div class='success results_passed'>Passed</div>";
	$failed = "<div class='failed results_failed'>Failed</div>";
	
	$geting_mark = educare_get_marks($subject);
	if ( !empty ($geting_mark)) {
		$status = $passed;
	} else {
		$status = $failed;
	}
	
	// Get auto results, if auto result is checked
	if (educare_check_status('auto_results') == 'checked') {
		if ($gpa) {
			$status = educare_get_marks($subject);
		} else {
			$status = $status;
		}
	} else {
		if ($gpa) {
			$getting_value = educare_value('GPA', $id);
			if (empty($getting_value)) {
				$status = '0';
			} else {
				$status = educare_value('GPA', $id);
			}
		} else {
			$status = strtolower(educare_value('Result', $id));
			if ($status == 'passed') {
				$status = $passed;
			} else {
				$status = $failed;
			}
		}
	}
	return $status;
}



/** =====================( Functions Details )======================

	### Front end educare results system

	* Usage:
		* in WordPress editor = [educare_results]
		* in PHP files = <?php echo do_shortcode( '[educare_results]' ); ?>

	* Futures:
		1. viewers can find result by Name, Registration number, Roll Number, Exam, Passing year.
		2. Results Table
		3. field validation
		4. Error Notice
		5. Profiles photos
		6. Print Results
		etc...
		
	* @since 1.0.0
	* @last-update 1.2.0

	* @return mixed

==================( function for Results Shortcode )==================*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Create shortcode fo Educare results
add_shortcode('educare_results', 'educare_get_results' );

function educare_get_results() {    
	global $wpdb;
	$table_name = $wpdb->prefix . 'educare_results';
	$educare_results = false;
	
	// Results Functions
	$not_found = $errmsg = ''; // only for results forms
	$Class = $Exam = $Roll_No = $Regi_No = $Year = '';

	$chek_name = educare_check_status('Name', true);
	$chek_roll = educare_check_status('Roll_No', true);
	$chek_regi = educare_check_status('Regi_No', true);
	$chek_class = educare_check_status('Class', true);
	$chek_exam = educare_check_status('Exam', true);
	$custom_results = educare_check_status('custom_results');

	echo '<div class="educare_results">';

	if (isset($_POST['educare_results']) or isset($_POST['educare_results_by_id'])) {
	
		if (isset($_POST['educare_results'])) {
			$Class = sanitize_text_field($_POST['Class']);
			$Exam = sanitize_text_field($_POST['Exam']);
			$Roll_No = sanitize_text_field($_POST['Roll_No']);
			$Regi_No = sanitize_text_field($_POST['Regi_No']);
			$Year = sanitize_text_field($_POST['Year']);

			$search_regi = $search_roll = $search_class = $search_exam = '';
			if ($chek_roll) {
				$search_roll = " AND Roll_No='$Roll_No'";
			}
			if ($chek_regi) {
				$search_regi = " AND Regi_No='$Regi_No'";
			}
			if ($chek_class) {
				$search_class = " AND Class='$Class'";
			}
			if ($chek_exam) {
				$search_exam = " AND Regi_No='$Exam'";
			}
			
			$educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE Year='$Year' $search_class $search_exam $search_roll $search_regi");
		}
		
		/** 
		Take one more step for,
		# Sometimes students/hacker can (force) access others results by database id.
		Notes: when you (admin) view results by id we don't need Class,  Exam, Year Roll & Regi No, just directly select your desired results by the id. if we don't take this step, anyone/hacker can access results by id.
		*/
		// get results by id (only for admin)
		if (isset($_POST['educare_results_by_id'])) {
			// check if users is admin or not
			if ('is_admin') {
				// if user is admin, then access results by id
				$id = sanitize_text_field($_POST['id']);
				$educare_results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
			} else {
				// if users is hacker/someone else
				$educare_results = false;
			}
		}
		
		if ($educare_results) {
			foreach($educare_results as $print) {
				$id = $print->id;
				$Subject = json_decode($print->Subject);
				$Details = json_decode($print->Details);

				if ($custom_results == 'checked' and function_exists('educare_custom_results')) {
					wp_kses_post(educare_custom_results($print));
				} else {
					?>
					<!-- Begin (frond end) Results Body -->
					<div class="result_body">
						<?php
						if (educare_check_status('photos') == 'checked') {
							if ($Details->Photos == 'URL') {
								$Photos = EDUCARE_STUDENTS_PHOTOS;
							} else {
								$Photos = $Details->Photos;
							}
							echo "<div class='student_photos'>
							<img src='".esc_url($Photos)."' class='img' alt='".esc_attr($print->Name)."'/></center>
							</div>";
						}

						if ($chek_name) {
							echo "<h2> ".esc_html($print->Name)."</h2>";
						}
						
						echo '<div class="table_body">
						<table class="result_details">';
						
							echo '<tr>';
							if ($chek_roll and $chek_regi) {
								echo '<td>'.esc_html( $chek_roll ).'</td>
									<td>'.esc_html($print->Roll_No).'</td>';

								if ($chek_name) {
									echo '<td>'.esc_html( $chek_name ).'</td>
									<td>'.esc_html($print->Name).'</td>';
								} else {
									echo '<td></td>
									<td></td>';
								}

								echo '</tr>';

								echo '<tr>
									<td>'.esc_html( $chek_regi ).'</td>
									<td>'.esc_html($print->Regi_No).'</td>
									<td>Class</td>
									<td>'.esc_html($print->Class).'</td>
								</tr>';
							} else {
								echo '</tr>';
								if ($chek_roll) {
									echo '<td>'.esc_html( $chek_roll ).'</td>
										<td>'.esc_html($print->Roll_No).'</td>';
								}

								if ($chek_regi) {
									echo '<tr>
										<td>'.esc_html( $chek_regi ).'</td>
										<td>'.esc_html($print->Roll_No).'</td>';
								}

								if ($chek_name) {
									echo '<td>'.esc_html( $chek_name ).'</td>
										<td>'.esc_html($print->Name).'</td>';
								}
								echo '</tr>';
							}

							/** 
							// Above cond retun like this =>
							<tr>
								<td>Roll No</td>
								<td><?php echo esc_html($print->Roll_No);?></td>
								<td>Name</td>
								<td><?php echo esc_html($print->Name);?></td>
							</tr>
								
							<tr>
								<td>Reg No</td>
								<td><?php echo esc_html($print->Regi_No);?></td>
								<td>Class</td>
								<td><?php echo esc_html($print->Class);?></td>
							</tr>
							*/
							?>
						
							<!-- Extra field -->
							<?php echo educare_get_data_by_student($id, 'Details');?>
							
							<tr>
								<td>Result</td>
								<td><?php echo wp_kses_post(educare_results_status($Subject, $id));?></td>
								<td>Year</td>
								<td><?php echo esc_html($print->Year);?></td>
							</tr>
							
							<tr>
								<td>GPA</td>
								<td colspan='3'>
									<?php echo esc_html(educare_results_status($Subject, $id, true));?>
								</td>
							</tr>
						</table>
						</div>
						
						<h3>Grade Sheet</h3>
						
						<div class="table_body">			
							<table class="grade_sheet">
								<thead>
									<tr>
										<th>No</th>
										<th>Subject</th>
										<th>Marks</th>
										<th>Grade</th>
									</tr>
								</thead>
								
								<tbody>
									<?php
									echo educare_get_data_by_student($id, 'Subject');
									
									/**  ====== Above function return like this ======
									<tr>
										<td>1</td>
										<td>Subject 1</td>
										<td>{marks}</td>
										<td>{letter grade}</td>
									</tr>
									
									<tr>
										<td>2</td>
										<td>Subject 1</td>
										<td>{marks}</td>
										<td>{letter grade}</td>
									</tr>

									<tr>
										<td>3</td>
										<td>Subject 3</td>
										<td>{marks}</td>
										<td>{letter grade}</td>
									</tr>

									........................................................
									........................................................
									*/
									?>
									
								</tbody>
							</table>
						</div>
						<div class="no_print">
							<button onClick="<?php echo esc_js('window.print()');?>" class="print_button"><i class="fa fa-print"></i> Print</button>
							<button onClick="<?php echo esc_js('history.back()');?>" class="back_button"><i class="fa fa-backward"></i> Back</button>
						</div>
					</div> <!-- .result_body -->
					<?php
				}
			}
		} else {
			if (!$chek_roll) {
				$Roll_No = true;
			}
			if (!$chek_regi) {
				$Regi_No = true;
			}

			if(!empty($Class) && !empty($Exam) && !empty($Roll_No) && !empty($Regi_No) && !empty($Year) ) {
				$not_found = '<div class="error_results"><h2 class="error">Results Not Found</h2><p>Please try again!</p></div>';
			} else {
				ob_start();
				echo '<div class="error_results"><div class="error_notice">
				<p>You must fill ';
			
				// notify if empty Class
				if (empty($Class) ) {
					echo '<b>Class</b>, ';
				}
				
				// notify if empty Exam
				if (empty($Exam) ) {
					echo '<b>Exam</b>, ';
				}
				
				// notify if empty Roll No
				if (empty($Roll_No) ) {
					echo '<b>Roll No</b>, ';
				}
				
				// notify if empty Reg No
				if (empty($Regi_No) ) {
					echo '<b>Reg No</b>, ';
				}
				
				// notify if empty Year
				if (empty($Year) ) {
					echo '<b>Year</b>, ';
				}
				
				echo 'Please fill all the required fields carefully. thanks.</p></div></div>';
				$errmsg = ob_get_clean();
			}
		}
	}
	
	
	// Show search forms when results not found
	if (!$educare_results) {
		if ($custom_results == 'checked' and function_exists('educare_custom_search_form')) {
			wp_kses_post(educare_custom_search_form($not_found, $errmsg));
		} else {
			echo '<div class="results_form">';
			// echo '<h1>Results</h1>';
			echo wp_kses_post($not_found);
			echo wp_kses_post($errmsg);
			?>
			
			<form class="educare_results_form forms" action="" method="post" id="educare_results">
				<?php
				
				echo '<div class="select">';
				if ($chek_class) {
					?>
					<p>Select Class:
					<select id="Class" name="Class" class="form-control">
						<?php educare_get_options('Class', $Class);?>
					</select>
					</p>
					<?php
				} else {
					echo '<input type="hidden" name="Class" value="Null">';
				}
	
				if ($chek_exam) {
					?>
					<p>Select Exam:
					<select id="Exam" name="Exam" class="fields">
						<?php educare_get_options('Exam', $Exam);?>
					</select>
					</p>
					<?php
				} else {
					echo '<input type="hidden" name="Exam" value="Null">';
				}
				echo '</div>';

				if ($chek_roll) {
					echo ''.esc_attr($chek_roll).':
					<label for="Roll_No" class="labels" id="roll_no"></label>
					<input type="number" id="Roll_No" name="Roll_No" class="fields" value="'.esc_attr($Roll_No).'" placeholder="Enter '.esc_attr($chek_roll).'">';
				} else {
					echo '<input type="hidden" name="Roll_No" value="">';
				}
				if ($chek_regi) {
					echo ''.esc_attr($chek_regi).':
					<label for="Regi_No" class="labels" id="regi_no"></label>
					<input type="number" id="Regi_No" name="Regi_No" class="fields" value="'.esc_attr($Regi_No).'" placeholder="Enter '.esc_attr($chek_regi).'">';
				} else {
					echo '<input type="hidden" name="Regi_No" value="">';
				}
				?>

				Select Year:
				<label for="Year" class="labels" id="year"></label>
				<select id="Year" name="Year" class="form-control">
					<?php 
					// echo '<option value="0">Select Year</option>';
					wp_kses_post(educare_get_options('Year', $Year));
					?>
				</select>
				
				<button id="results_btn" class="results_button button" name="educare_results" type="submit">View Results </button>
			</form>
			</div>
			<?php
		}
	}
	
	echo "</div>"; // .educare_result
	
}



?>