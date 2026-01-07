<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// StudentResult class
require_once(EDUCARE_TEMP.'student-result-class.php');
// Educare all default display data layout
require_once(EDUCARE_TEMP.'educare-default-display-data.php');
// Educare all default forms to insert data
require_once(EDUCARE_TEMP.'educare-default-forms.php');
// Educare all default search form template list
require_once(EDUCARE_TEMP.'educare-default-search-form.php');
// Educare all default results card template list
require_once(EDUCARE_TEMP.'educare-default-results-card.php');
// Educare template preview
require_once(EDUCARE_TEMP.'template-preview.php');
// Begin educare template functionality

/**
 * Get template data for results card.
 * usage:
 * print_r(educare_get_template_data('fields'));	
 * 
 * @param string|null $key Optional. The specific key for template data retrieval.
 * @param bool $all Optional. Whether to retrieve all template data or only checked data.
 * @return mixed|array|null The retrieved template data based on the specified key and options.
 */
function educare_get_template_data($key = null, $all = true, $specific_template = null) {
	// Get the default template data
	$default_template = educare_default_results_card('', true);

	// Get the active/current template
	if ($specific_template) {
		$active_template = sanitize_text_field($specific_template);
	} else {
		$active_template = educare_check_status('results_card_template');
	}
	

	// Check if the active template function exists
	if ($active_template && function_exists($active_template)) {
		$template_data = $active_template('', true);

		$card_field = educare_check_status('results_card');
		$card_field = json_decode(json_encode($card_field), true);

		if (isset($card_field['template'][$active_template])) {
			$template_data['fields'] = $card_field['template'][$active_template];
		} else {
			$template_data = $active_template('', true);
		}
	} else {
		// If the active template function doesn't exist, use data from the default template
		$template_data = $default_template;
	}

	// Filter template data.
	$template_data = apply_filters( 'educare_filter_card_template', $template_data );

	$new_template_data = array();

	if ($key) {
		// Retrieve specific key data/value
		if (key_exists($key, $template_data)) {
			$new_template_data = $template_data[$key];
		} else {
			// If the key doesn't exist in the active template, check the default template
			if (key_exists($key, $default_template)) {
				$new_template_data = $default_template[$key];
			}
		}
	} else {
		// Return all data
		$new_template_data = $template_data;
	}

	// Filter template data.
	// $new_template_data = apply_filters( 'educare_filter_card_template', $new_template_data );

	if ($all) {
		return $new_template_data;
	} else {
		$checked_data = array();

		if ($new_template_data) {
			// Filter out data with status 'checked'
			foreach ($new_template_data as $key => $value) {
				if ($value['status'] == 'checked') {
					$checked_data[$key] = $value;
				}
			}
		}

		return $checked_data;
	}
}



/**
 * ### Get specific class or Group wise subject
 * Usage example: educare_get_options_for_subject('Class 6', 'Group');
 * Note: Return all data if $class_name is empty.
 * 
 * usage: 
 * print_r(educare_get_data('Year'));
 * 
 * @since 1.4.2
 * @last-update 1.4.2
 * @param string $class_name				For specific class or group subject
 * @param string $data_for					Select database (Class or Group);
 * 
 * @return array
 */
 function educare_get_data($data_for = 'Class', $class_name = false) {
	global $wpdb;
	$table = $wpdb->prefix.EDUCARE_PREFIX.'settings';
	
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM $table WHERE list = %s", $data_for)
	);
	
	if ($results) {
		foreach ( $results as $print ) {
			$data = $print->data;
			$data = json_decode($data, true);

			if ($class_name) {
				$subject_list = array();

				if (key_exists($class_name, $data)) {
					foreach ($data[$class_name] as $subject) {
						$subject_list[] = $subject;
					}

					return $subject_list;
				} else {
					return $subject_list;
				}

			} else {
				$all_calss = array();
				foreach ($data as $class => $sub) {
					if (is_array($sub)) {
						$all_calss[] = $class;
					} else {
						$all_calss[] = $sub;
					}
					
				}
				return $all_calss;
			}

		}
	}
}



/**
 * Output select options based on data retrieved from educare_get_data.
 *
 * usage: 
 * educare_get_option('Year')
 * 
 * @param string $data_for The data key for which options are to be retrieved.
 * @param bool $specific_key Optional. If true, retrieve options for a specific key within the data.
 * @return void Outputs HTML select options based on the retrieved data.
 */
function educare_get_option($data_for = 'Class', $specific_key = false) {
	if ($data_for == 'School') {
		echo '<option value="">Not found</option>';
	} else {
		$results = educare_get_data($data_for, $specific_key);

		if ($data_for == 'Staff_position') {
			if (!in_array('Teachers', $results)) {
				// if Teachers not exists
				$results[] = "Teachers";
				// array_unshift($results, "Teachers");
			}
		}

		if ($data_for == 'Year') {
			rsort($results);
		}

		if ($results) {
			if (isset($_POST['default'][$data_for])) {
				$current_value = sanitize_text_field( $_POST['default'][$data_for] );
			} else {
				$current_value = '';
			}

			foreach ($results as $value) {
				echo '<option value="'.esc_attr( $value ).'" '.selected($current_value, esc_attr($value), false ).'>'.esc_html( $value ).'</option>';
			}
		} else {
			echo '<option value="">Not found</option>';
		}
	}
}



/**
 * Retrieves data from a specified table in the WordPress database based on provided criteria.
 *
 * @param array $select An associative array containing query criteria:
 *                     - 'table': The table name to retrieve data from.
 *                     - 'row': Optional. The row/column name to filter by.
 *                     - 'value': Optional. The value to match in the specified row.
 *                     - 'data': Optional. The specific column/row to retrieve from each result.
 *                     - 'return_key': Optional. Return an array of keys only without data.
 *                     - 'target_key': Optional. Return a specific key's value from data.
 *                     - 'json_decode': Optional. If true, decode JSON-encoded data.
 *
 * @return mixed|array Returns the retrieved data as an array or a specific value, based on the criteria.
 *                     Returns false if no data is found.
 */
function educare_get_results($select) {
  /*
  // this is an sample table selection key and value
  $select = array (
		'table' => 'settings',     	// SELECT * FROM $select
		'row' => 'list',           	// WHERE $row (need to compare with value)
		'value' => 'Class',       	// WHERE $row equal to $value ($row='$value')
		'data' => 'data',          	// if found results print specific key/row (data) value
		'return_key' => false,			// Return key only without data
		'target_key' => 'Class 6',	// Return specific key value. ignore 'return_key' or set 'return_key' => false when target specific key for return data
	);
  */

  // table name is requred otherwise return
  if (!isset($select['table'])) return false;
  // All optional key and value
	isset($select['get_results']) ? : $select['get_results'] = 'get_results';
  // WHERE to select (WHERE $row='$value')
  isset($select['row']) ? : $select['row'] = '';
  // WHERE row equal to value (WHERE $row='$value')
  isset($select['value']) ? : $select['value'] = '';
  // if found results print specific key (data)
  isset($select['data']) ? : $select['data'] = '';
	// return key only
	isset($select['return_key']) ? : $select['return_key'] = '';
	// id data found target specific key for getting value
	isset($select['target_key']) ? : $select['target_key'] = '';
  isset($select['json_decode']) ? : $select['json_decode'] = false;
  
  global $wpdb;
  $table = $wpdb->prefix.EDUCARE_PREFIX.$select['table'];

	if (!educare_table_exists($table)) {
		return false;
	}

  $row = $select['row'];
	$get_results = $select['get_results'];

  if ($row) {
    $val = $select['value'];
    if ($val) {
      $results = $wpdb->$get_results("SELECT * FROM $table WHERE $row='$val'");
    } else {
      $results = $wpdb->$get_results("SELECT $row FROM $table");
    }
    
  } else {
    $results = $wpdb->$get_results("SELECT * FROM $table");
  }

  if ($results) {
    $data = $select['data'];
    if ($data) {
      foreach ($results as $key => $value) {
        if (json_decode($value->$data)) {
					$data = json_decode($value->$data);

					if ($data) {
						$target = $select['target_key'];
						$return_key = $select['return_key'];

						if ($return_key) {
							$return_key_only = array();
							foreach ($data as $key => $value) {
								$return_key_only[] = $key;
							}

							return $return_key_only;
						}

						if ($target) {
							if (property_exists($data, $target)) {
								return $data->$target;
							} else {
								return false;
							}
						}
						return $data;
					} else {
						return false;
					}
        } else {
          return json_decode($value->$data);
        }
        
      }
    }

    if ($row and !$select['value']) {
      $new_data = array();
      
      foreach ($results as $key => $value) {
				if (property_exists($row,  $value)) {
					$new_data[] = $value->$row;
				}
      }

      return $new_data;
    }

    return $results;

  } else {
    return false;
  }
}



/**
 * Generates an HTML form for searching student or teacher records based on roles.
 *
 * This function generates a search form that allows users to search for student or teacher records
 * based on the specified roles. The form includes input fields and select dropdowns for entering
 * search criteria, such as Name, Roll Number, and Registration Number, and a Search button to submit
 * the search query. The form also includes a security nonce to prevent unauthorized access.
 *
 * @param string $roles The roles for which the search form is being generated. Default is 'results'.
 * @return void Outputs the HTML form for searching student or teacher records.
 */
function educare_get_search_form($roles = 'results') {
	if (isset($_POST['roles'])) {
		$roles = sanitize_text_field( $_POST['roles'] );
	}

	// Show banner
	educare_show_school_banner();
  
	echo '<form id="search-form" class="add_results" method="post" action="">
		<div class="content">';
			// Show page title
			echo educare_get_page_title($roles, 'update');

			echo educare_guide_for(__('Use this form to search for data and update or remove specific records. (All fields are required.)', 'educare'));
			
			// secure nonce for this form.
			$nonce = wp_create_nonce( 'educare_crud_data' );
			echo '<input type="hidden" name="crud_data_nonce" value="'.esc_attr($nonce).'">';
			echo '<input type="hidden" name="roles" value="'.esc_attr($roles).'">';

			// Student details begin
			$requred = educare_check_status('display');
			$requred_title = educare_requred_data($requred, true);
			$requred_title = educare_roles_wise_filed(array('roles' => $roles, 'fields' => $requred_title));
			$requred_title['auto_fill'] = true;
			unset($requred_title['user_pin']);
			$i = 0;

			foreach ($requred_title as $key => $value) {
				if ($roles != 'teachers' and $key == 'Name') continue;
				if ($key == 'auto_fill') continue;

				echo '<div class="mb-3 row">
					<label for="default_'.esc_attr($key).'" class="col-md-2 col-form-label d-flex align-items-center">'.esc_html__($value, 'educare').'</label>
					<div class="col-md-10">';

						if($key == 'Name' || $key == 'Roll_No' || $key == 'Regi_No') {
							$field_value = '';

							if (isset($_POST['default'])) {
								if (key_exists($key, $_POST['default'])) {
									$field_value  = sanitize_text_field( $_POST['default'][$key] );
								}
							}

							echo '<input type="text" name="default['.esc_attr($key).']" value="'.esc_attr($field_value).'" placeholder="'.sprintf(__('Enter %s', 'educare'), esc_attr__($value, 'educare')).'" class="form-control" id="default_'.esc_attr($key).'">';
						} else {
							echo '<select id="default_'.esc_attr($key).'" name="default['.esc_attr($key).']">';
							educare_get_option($key);
							echo '</select>';
						}

					echo '</div>
				</div>';
			}

			if (key_exists('auto_fill', $requred_title)) {
				echo '<div class="mb-3 row">
					<label for="default_auto_fill" class="col-md-2 col-form-label d-flex align-items-center"></label>
					<div class="col-md-10">
						<button type="submit" name="search" class="btn btn-outline-success d-flex gap-2 align-items-center m-0 crud-forms">'.sprintf(__('%s Search', 'educare'), '<i class="dashicons dashicons-search"></i>').'</button>
					</div>
				</div>';
			}

		echo '</div>';

	echo '</form>';
  
}



/**
 * Adjusts and filters fields based on the specified roles.
 *
 * This function adjusts and filters the fields to be displayed in forms based on the specified roles.
 * It takes an array of data as input, which includes the roles, fields, additional fields, and an option
 * to ignore certain fields. It processes the data and returns an array of filtered and adjusted fields
 * based on the specified roles, ensuring that certain fields are ignored or added based on the role type.
 *
 * @param array $data An array containing roles, fields, additional fields, and ignore option.
 * @return array An array of adjusted and filtered fields based on the specified roles.
 */
function educare_roles_wise_filed($data) {
	isset($data['roles']) ? : $data['roles'] = null;
	isset($data['fields']) ? : $data['fields'] = array();
	isset($data['add_fields']) ? : $data['add_fields'] = array();
	isset($data['get_ignore']) ? : $data['get_ignore'] = false;
	// $roles, $fields, $add_fields = null

	if ($data['roles']) {
		// For add field
		$data['add_fields'] = array();

		// for ingnore fields
		if ($data['roles'] == 'students') {
			$ignore = array(
				'Exam',
				'Term',
				'Group',
				'Results',
				'GPA',
				'auto_fill',
				'Staff_position'
				// 'user_pin'
			);
		} elseif ($data['roles'] == 'teachers') {
			$ignore = array(
				'Roll_No',
				'Regi_No',
				'Class',
				'Group',
				'Exam',
				'Year',
				'Term',
				'Results',
				'GPA',
				'auto_fill',
				'user_pin'
			);
		} elseif ($data['roles'] == 'marks') {
			$ignore = array(
				'Name',
				'Roll_No',
				'Regi_No',
				'Results',
				'GPA',
				'auto_fill',
				'user_pin',
				'Staff_position'
			);
		} else {
			// if results. because our forms build for results system
			$ignore = array(
				'Group',
				'Staff_position'
				// 'user_pin'
			);
		}

		if ($data['get_ignore']) {
			return $ignore;
		}
		
		if ($ignore) {
			foreach ($ignore as $value) {
				if (key_exists($value, $data['fields'])) {
					unset($data['fields'][$value]);
				}
			}
		}

		if ($data['add_fields']) {
			foreach ($data['add_fields'] as $key => $value) {
				$data['fields'][$key] = $value;
			}
		}

		return $data['fields'];
	}
}



/**
 * Generates and displays extra fields based on settings data.
 *
 * usage: 
 * educare_get_extra_field();
 * 
 * This function retrieves and processes extra field details from the database settings and generates
 * corresponding input fields in the form. It first queries the database for extra field details,
 * such as the field type and display name. Then, it dynamically generates HTML input fields based on
 * the retrieved details, allowing users to input additional information.
 * 
 * @param str $roles check if data for teachers or students
 *
 * @return void
 */
function educare_get_extra_field($roles, $language = 'eng') {
	$select = array (
		'table' => 'settings',     // SELECT * FROM $select
		'row' => 'list',           // WHERE $row (need to compare with value)
		'value' => 'Extra_field',  // WHERE $row equal to $value ($row='$value')
		'data' => 'data',          // if found results print specific key/row (data) value
	);

	// get template setting default fields
	$extra_field = educare_get_template_data('extra_field');
	// Get extra fields from database
	$saved_extra_field = educare_get_results($select);

	// Ensure both results are arrays, or initialize them as empty arrays
	if (!is_array($extra_field)) {
		$extra_field = array();
	}

	// // add saved fieds
	$extra_field = array_merge($extra_field, $saved_extra_field);

	if ($extra_field) {
		if ($language == 'ar') {
			$data_details = isset($_POST['Others']['Details']) ? $_POST['Others']['Details'] : array();
		} else {
			$data_details = isset($_POST['Details']) ? $_POST['Details'] : array();
		}

		$data_details = (array) $data_details;
		

		foreach ($extra_field as $details) {
			$details = (object) $details;
			// show specific data only based on roles
			$data_for = educare_sanitize_array( $details->for );

			if (in_array($roles, $data_for) || !$data_for) {
				$title = sanitize_text_field( $details->title );
				// $name = str_replace(' ', '_', $title);
				$type = sanitize_text_field( $details->type );

				if ($language == 'ar') {
					$name = 'Others[Details]['.$title.']';
				} else {
					$name = 'Details['.$title.']';
				}

				if (isset($data_details[$title])) {
					$value = sanitize_text_field( $data_details[$title] );
				} else {
					$value = '';
				}

				echo '<div class="mb-3 row">
					<label for="extra-'.esc_attr($language).'-'.esc_attr($title).'" class="col-xl-2 col-form-label d-flex align-items-center">'.esc_html__($title, 'educare').'</label>
					<div class="col-xl-10">
						<input type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'" class="form-control" id="extra-'.esc_attr($language).'-'.esc_attr($title).'">
					</div>
				</div>';
			}
		}
	} else {
		// No extra fields found
	}

}



/**
 * Filters and returns checked data from an array of objects.
 *
 * This function takes an array of objects and filters out the objects based on their status property.
 * It returns a new stdClass object containing the filtered data, either as titles only or complete objects,
 * depending on the specified parameters. The status parameter allows selecting objects with a specific status,
 * such as 'checked'. If title_only is set to true, the function returns an object with only the titles of
 * the filtered objects; otherwise, it returns the complete filtered objects.
 *
 * @param array   $data        An array of objects to filter.
 * @param bool    $title_only  Whether to include only titles in the returned object.
 * @param string  $status      The status value to filter by (e.g., 'checked').
 *
 * @return stdClass A new stdClass object containing the filtered data.
 */
function educare_checked_data($data, $title_only = true, $status = 'checked') {
	$newObject = new stdClass();
	
	foreach ($data as $key => $object) {
    if ($title_only) {
      if ($status == 'all') {
        $newObject->$key =  $object->title;
      } else {
        if (property_exists($object, 'status')) {
          if ($object->status == $status) {
            $newObject->$key =  $object->title;
          }
        }
      }
    } else {
      if (property_exists($object, 'status')) {
        if ($object->status == $status) {
          $newObject->$key =  $object;
        }
      }
    }
	}

	return $newObject;
}



/**
 * Outputs table header cells for a grade sheet based on provided term data.
 *
 * This function generates and outputs table header cells for a grade sheet based on the given term data.
 * The term data should be provided as an associative array, where each key represents a term and its value
 * contains additional information about the term, such as title and subtitle. The front parameter controls
 * whether to display hidden terms when set to false (default), and it's particularly useful when generating
 * grade sheets for display purposes.
 *
 * @param array   $terms  An associative array of term data.
 * @param bool    $front  Whether to display hidden terms (default is false).
 *  @param bool 	$text_domain	for translation
 *
 * @return void Outputs the HTML table header cells for the grade sheet.
 */
function educare_grade_sheet_th($terms, $front = false, $text_domain = null) {
	if (!$text_domain) {
		$text_domain = 'educare';
	}

	foreach ($terms as $term => $termInfo) {
		if ($term == 'grade_sheet') continue;
		$title = $subtitle = '';
		
		if (key_exists('hide', $termInfo) && $front === true) {
			if ($termInfo['hide'] == 'on') {
				continue;
			}
		}

		if (key_exists('title', $termInfo)) {
			$title = $termInfo['title'];
		}
		
		if (key_exists('subtitle', $termInfo)) {
			$subtitle = $termInfo['subtitle'];
			if ($subtitle) {
				$subtitle = '<small>'.esc_html__($subtitle, $text_domain).'</small>';
			}
		}
		
		echo '<th class="term-'.esc_attr($term).'">'. esc_html__($title, $text_domain) . wp_kses_post( $subtitle ).'</th>';
	}
}



/**
 * Outputs table cells for entering marks data based on provided term and subject information.
 *
 * This function generates and outputs table cells for entering marks data based on the given term and subject information.
 *
 * @param array   $terms      An associative array of term data.
 * @param string  $subArray   The subarray key for organizing the data structure.
 * @param string  $unique     Unique identifier for the data entry.
 * @param string  $subject    The subject for which marks are being entered.
 *
 * @return void Outputs the HTML table cells for entering marks data.
 */
function educare_get_marks_input($terms, $subArray, $unique, $subject) {
	foreach ($terms as $term => $termInfo) {
		// hide field based on fields settings
		if (key_exists('hide', $termInfo)) {
			if ($termInfo['hide'] == 'on') {
				continue;
			}
		}

		echo '<td>';

		if (isset($_POST[$subArray][$unique][$subject][$term])) {
			$value = sanitize_text_field( $_POST[$subArray][$unique][$subject][$term] );
		} else {
			$value = '';
		}
		
		// Show auto status
		if ($termInfo['auto'] == 'on') {
			$auto = 'Auto';

			if ($term == 'gpa' or $term == 'grade') {
				if ($termInfo['auto'] == 'on') {
					if (isset($_POST[$subArray][$unique][$subject]['marks']) && !empty($_POST[$subArray][$unique][$subject]['marks'])) {
						if ($term == 'gpa') {
							$auto = educare_letter_grade($_POST[$subArray][$unique][$subject]['marks'], true);
						} else {
							$auto = educare_letter_grade($_POST[$subArray][$unique][$subject]['marks']);
						}
					} else {
						$auto = $auto;
					}
				}
			}

			echo '<div class="auto-fields">'.wp_kses_post($auto).'</div>';
			echo '<input type="hidden" name="' . esc_attr($subArray) . '[' . esc_attr($unique) . '][' . esc_attr($subject) . '][' . esc_attr($term) . ']" value="' . esc_attr($value) . '">';
		} else {
			// Select field
			if ($termInfo['type'] == 'select') {
				echo '<select name="' . esc_attr($subArray) . '[' . esc_attr($unique) . '][' . esc_attr($subject) . '][' . esc_attr($term) . ']">';
				foreach ($termInfo['value'] as $val) {
					echo '<option value="'.esc_attr($val).'" '.selected( $val, $value, false).'>' . esc_html($val) . '</option>';
				}
				echo '</select>';
			} else {
				// Input field
				echo '<input type="' . esc_attr($termInfo['type']) . '" name="' . esc_attr($subArray) . '[' . esc_attr($unique) . '][' . esc_attr($subject) . '][' . esc_attr($term) . ']" value="' . esc_attr($value) . '" step="any">';
			}
		}

		echo '</td>';

	}

	if (isset($_POST[$subArray][$unique][$subject]['optional'])) {
		$value = sanitize_text_field( $_POST[$subArray][$unique][$subject]['optional'] );
	} else {
		$value = '';
	}

	echo '<td>
	<input type="hidden" name="' . esc_attr($subArray) . '[' . esc_attr($unique) . '][' . esc_attr($subject) . '][optional]">
	<input type="checkbox" value="yes" name="' . esc_attr($subArray) . '[' . esc_attr($unique) . '][' . esc_attr($subject) . '][optional]" '.checked($value, 'yes', false).'>
	</td>';
}



/**
 * Generates and outputs the HTML table for entering marks data based on provided roles and subjects.
 *
 * This function generates and outputs an HTML table for entering marks data based on the provided roles (e.g., 'results')
 * and subjects. It fetches information about the available terms and subjects, displays relevant table headers, and
 * provides input fields for entering marks. The function also handles the display of optional subject checkboxes.
 *
 * @param string  $roles      The role for which marks data is being entered (e.g., 'results').
 * @param array   $subjects   An array of subjects for which marks are being entered.
 * @param int     $unique     Optional. Unique identifier for the data entry.
 * @param string  $subArray   Optional. The subarray key for organizing the data structure.
 *
 * @return void Outputs the HTML table for entering marks data.
 */
function educare_get_marks_fields($roles, $subjects, $unique = 0, $subArray = 'Subject') {
	// Marks fields bassed on template
	if ($roles == 'results') {
		$terms = educare_get_template_data('fields', false);
	} else {
		$terms = array();
	}

	$results_card = educare_check_status('results_card');
	// getting grade_sheet checked data
	$default_terms = educare_checked_data($results_card->grade_sheet, false);
	$default_terms = json_decode(json_encode($default_terms), true);

	// Show Mark sheet fields
	?>
	<div class="table_container">
		<table class="bg-white w-100 list grade_sheet" style="min-width: 100%;">
			<thead>
				<tr>
					<?php
					educare_grade_sheet_th($default_terms);
					educare_grade_sheet_th($terms, true);
					?>

					<th class="optional_sub">
						<div class="action_menu"><i class="dashicons action_button dashicons-info"></i> <menu class="action_link msg text-black"><b>Optional Subject</b><hr>If the student or your result system includes an optional subject, please indicate it. Otherwise, you may disregard this field.</menu></div>
					</th>
				</tr>
			</thead>
			
			<tbody>
				<?php 
				$serialNo = 1;
				if ($subjects) {
					foreach ($subjects as $subject) {
						echo '<tr>';

						foreach ($default_terms as $term => $termInfo) {
							if ($term == 'grade_sheet') continue;
							if ($term == 'no') echo '<td>' . esc_html($serialNo++) . '</td>';
							if ($term == 'subject') echo '<td>' . esc_html($subject) . '</td>';
	
							if (is_object($subject)) {
								if (property_exists($subject, $term)) {
									echo '<td>' . esc_html($subject->$term) . '</td>';
								}
							}
						}

						educare_get_marks_input($terms, $subArray, $unique, $subject);

						echo '</tr>';
					}
				} else {
					// Subject not found
					$url = admin_url().'/admin.php?page=educare-management&menu=Class';
					// wen don't need to additon optional field. beacuse $default_terms has 3 incliding grade_sheet!
					$span = count($default_terms) + count($terms);

					echo '<tr><td colspan="'.esc_attr( $span ).'">';
					echo educare_guide_for("Currently, you don't have added any subject in this class. Please add some subject by <a href='".esc_url( $url)."' target='_blank'>Click Here</a> or <a href='#Class'>Change Class</a>. Thanks", false);
					echo '</td></tr>';
				}

				// <!-- getting group wise subject list -->
				?>

				<tbody id="Group_list"></tbody>
			</tbody>
		</table>
	</div>
	<?php
}



/**
 * Retrieves and merges the details and grade sheet data of the results card.
 *
 * This function retrieves the details and grade sheet data of the results card from the database and
 * merges them into a single object. It fetches the results card information using the `educare_check_status`
 * function and combines the details and grade sheet data into a single object. The merged results card object
 * is returned for further processing.
 *
 * @return object|null The merged results card data as an object, or null if no data is found.
 */
function educare_get_card_data() {
	$results_card_all = educare_check_status('results_card');
	$results_card_details = $results_card_all->details;
	$results_card_grade_sheet = $results_card_all->grade_sheet;
	$results_card = (object) array_merge((array) $results_card_details, (array) $results_card_grade_sheet);
	return $results_card;
}



/**
 * Displays the subject fields and marks input fields based on the roles and class selection.
 *
 * This function is responsible for rendering the subject fields and marks input fields based on the provided roles
 * and class selection. It dynamically fetches and displays the subject options, marks input fields, and additional
 * controls based on the user's selections. The function also handles the display of error or success messages.
 *
 * @param string $roles The user's role (e.g., 'results', 'students', 'teachers').
 * @param string|null $class_name The selected class name. Defaults to null.
 * @return void
 */
function educare_get_subject_field($roles = 'results', $class_name = null) {
	if (key_exists('default', $_POST)) {
		if (key_exists('Class', $_POST['default'])) {
			$class_name = $_POST['default']['Class'];
		}
	}

	if (isset($_POST['default']['id']) or isset($_POST['Subject'][0])) {
		// getting selected class subject
		$subjects = educare_check_settings('Class', $_POST['default']['Class']);

		// getting selected group subject
		if (isset($_POST['default']['Group']) && !empty($_POST['default']['Group'])) {
			// getting selected group subject
			$get_group_sub = educare_check_settings('Group', $_POST['default']['Group']);
			// check if subject exists or not in the subject list '$_POST['Subject'][0]'
			if ($get_group_sub) {
				foreach ($get_group_sub as $sub_name) {
					if (key_exists($sub_name, $_POST['Subject'][0])) {
						$subjects[] =  $sub_name;
					}
				}
			}
		}

		// foreach ($_POST['Subject'] as $key => $value) {
		// 	$subjects[] = $key;
		// }
		
	} else {
		if ($class_name) {
			// getting class wise subject for specific class name
			$subjects = educare_get_data('Class', $class_name);
		} else {
			// if empty $class_name, then get first class name
			$first_class = educare_get_data('Class');
			$class_name = reset($first_class);
			// Now getting subject baseed on first class
			$subjects = educare_get_data('Class', $class_name);
		}
	}

  // Marge group wise subject
  if (isset($_POST['select_subject'])) {
    $group_sub = $_POST['select_subject'];
    if ($group_sub) {
			$subjects = educare_get_data('Class', $class_name);
      $subjects = array_unique(array_merge($subjects, $group_sub));
    }
  }

	// Define/Get all terms/fields that supported current template
	if (isset($_POST['roles'])) {
		$roles = sanitize_text_field( $_POST['roles'] );
	}

	$failed = educare_check_status('failed');
	$passed = educare_check_status('passed');
	$auto_results = educare_check_status('auto_results');
	$results_card = educare_get_card_data();

	$results_card = educare_requred_data($results_card, true, true);
	$result = $gpa = $Status = '';

	if (isset($_POST['default']['Result'])) {
		$result = sanitize_text_field( $_POST['default']['Result'] );
	}
	if (isset($_POST['default']['GPA'])) {
		$gpa = sanitize_text_field( $_POST['default']['GPA'] );
	}
	if (isset($_POST['default']['Status'])) {
		$Status = sanitize_text_field( $_POST['default']['Status'] );
	}

	if ($roles == 'results') {
		// Auto results guidelines
		if ($auto_results == 'checked') {
			echo educare_guide_for(
				sprintf(
					__(
						"Click here to <a href='%s' target='_blank'>Disable Auto Results</a> system from Educare settings to manually set <b>%s</b> or <b>%s</b>.",
						'educare'
					),
					esc_url('/wp-admin/admin.php?page=educare-settings&menu=Results_System'),
					esc_html($results_card["result"]),
					esc_html($results_card["gpa"])
				)
			);
		} else {
			// Show results and GPA fields
			?>
			<div class="d-flex gap-3 my-3">
				<div class="w-100">
					<label for="FormResult" class="form-label"><?php _e($results_card['result'], 'educare')?></label>

					<select id="FormResult" name="default[Result]" class="form-control" <?php disabled( 'checked', $auto_results ) ;?>>
						<option value="Passed" <?php selected( 'Passed', esc_attr($result) ) ;?>><?php echo esc_html( $passed ) ;?></option>
						<option value="Failed" <?php selected( 'Failed', esc_attr($result) ) ;?>><?php echo esc_html( $failed ) ;?></option>
					</select>
				</div>
				
				<div class="w-100">
					<label for="FormGAP" class="form-label"><?php _e($results_card['gpa'], 'educare')?></label>

					<input id="FormGAP" type="number" name="default[GPA]" class="fields rounded" value="<?php echo esc_attr($gpa) ;?>" placeholder="0.00" step="any" <?php disabled( 'checked', $auto_results ) ;?>>
				</div>
			</div>
		<?php
		}
	}
	
	// Show subject guide
	if ($roles == 'results') {
		echo educare_guide_for('add_subject');
	} else {
		echo educare_guide_for(
			sprintf(
				__(
					"Click here to <a href='%s' target='_blank'>Add More Subject</a>.",
					'educare'
				),
				esc_url('/wp-admin/admin.php?page=educare-management&Subject'),
				esc_url('/wp-admin/admin.php?page=educare-settings&menu=Card_Settings')
			)
		);
	}
	
	// Show Mark sheet fields
	educare_get_marks_fields($roles, $subjects);

	// Get the display status
  $required = educare_check_status('display');
  // Get required data for display
	$required_fields = educare_requred_data($required, true);
	?>

	<!-- display error or success msg -->
	<div id="sub_msgs"></div>

	<!-- Show button for add or edit group wise subject -->
	<div id="add_to_button" class="mb-3">
		<div id='edit_add_subject' class='btn btn-outline-success'>
			<i class='dashicons dashicons-edit'></i>
		</div>
	</div>
	<?php 
	
	if (key_exists('Group', $required_fields)) {
		echo educare_guide_for(sprintf(__('Click here to <a href="%s" target="_blank">Add More Group</a>.', 'educare'), '/wp-admin/admin.php?page=educare-management&Group'));
	}
}



/**
 * Retrieves the required fields based on user roles and field data.
 *
 * Educare Queary Functions 1. Getting Requred Fields
 * This function retrieves the required fields for a given set of user roles and field data. It filters out any fields
 * marked as ignored for the specified roles and adds additional conditions, such as excluding the 'Name' field for
 * roles other than 'teachers'. The resulting list of required fields is then returned.
 *
 * @param string $roles The user's role (e.g., 'results', 'students', 'teachers').
 * @param array $fields_data An array containing field data for processing.
 * @return array An array containing the required fields based on the specified roles and field data.
 */
function educare_get_requred_fields($roles, $fields_data) {
	$ignore = educare_roles_wise_filed(array('roles' => $roles, 'get_ignore' => true));

	// Requred name field for teachers roles
	if ($roles != 'teachers') {
		array_push($ignore, 'Name');
	}
	
	$requred = educare_check_status('display');
	$requred_fields = educare_combine_fields($requred, $ignore, $fields_data);

	return $requred_fields;
}



/**
 * Get required fields based on roles and optionally return only the values.
 *
 * @param string|array $roles      The roles for which required fields are being fetched.
 * @param bool         $only_value Whether to return only the values or the full array.
 *
 * @return array|string Returns an array of required fields or sanitized values based on the conditions.
 */
function educare_required_fields($roles, $only_value = false) {
  // Get the display status
  $required = educare_check_status('display');

  // Get required data for display
  $required_title = educare_requred_data($required, true);

  // Filter required data based on roles
  $required_title = educare_roles_wise_filed(array('roles' => $roles, 'fields' => $required_title));

  // If only returning values, sanitize and return
  if ($only_value) {
    $required_value = array();

    if ($required_title) {
      foreach ($required_title as $value) {
        $required_value[] = sanitize_text_field($value);
      }
    }

    return $required_value;
  }

  // Return the full array of required fields
  return $required_title;
}



/**
 * Generates dynamic SQL query based on provided filters and options.
 *
 * This function generates a dynamic SQL query based on the provided filters and options, such as required fields,
 * sorting criteria, and pagination. It constructs the SQL query using the WordPress $wpdb global object and prepares
 * the query with sanitized values. The resulting SQL query string is returned.
 *
 * @param array $requred_fields An array of required fields and filtering options.
 * @param string $roles The user's role (e.g., 'results', 'students', 'teachers').
 * @return string The dynamically generated SQL query string.
 */
function educare_dynamic_sql($requred_fields, $roles = 'results') {
	global $wpdb;
	// Define table name to access data
	$table = $wpdb->prefix.EDUCARE_PREFIX.$roles;

	if (!educare_table_exists($table)) {
		return false;
	}

	$table_structure = $wpdb->get_results("DESCRIBE $table");
	unset($requred_fields['dashboard'], $requred_fields['profiles_id'], $requred_fields['profiles_for'], $requred_fields['print'], $requred_fields['nonce']);

	$table_field = array();

	// Getting table field for dynamically search
	if (!empty($table_structure)) {
		foreach ($table_structure as $column) {
			$table_field[] = $column->Field;
		}
	}
	
	$s_query = array();

	if (isset($requred_fields['search'])) {
		$table_field = array();

		$requred = educare_check_status('display');
		$requred_title = educare_requred_data($requred, true);
		$requred_title = educare_roles_wise_filed(array('roles' => $roles, 'fields' => $requred_title));

		foreach ($requred_title as $column => $title) {
			if ($column == 'School') {
				continue;
			}

			$table_field[] = $column;
		}

		if ($table_field) {
			foreach ($table_field as $field) {
				$s_query[] = "`".esc_sql( $field )."` LIKE '%" . esc_sql( ucwords($requred_fields['search']) ) . "%'";
			}
		}
	}

	$s_querys = implode('OR ', $s_query);



	$prepared_values = array();
	$orderby = $limit = '';
	$sql = "WHERE 1=1 ";

	foreach ($requred_fields as $key => $value) {
		if ($key == 'search') {

			// Add search query
			$sql .= 'AND ('. $s_querys . ') ';
			continue;
		}

		if ($key == 'order_by' or $key == 'order') {
			$orderby = sanitize_text_field($requred_fields['order_by']);
			$order = sanitize_text_field($requred_fields['order']);
			$orderby = "ORDER BY $orderby $order";
			continue;
		}

		if ($key == 'per-page' or $key == 'offset') {
			$limit = (int) sanitize_text_field($requred_fields['per-page']);
			$offset = isset($requred_fields['offset']) ? (int) sanitize_text_field($requred_fields['offset']) : 0;

			$limit = "LIMIT $offset, $limit";
			continue;
		}

		$sql .= "AND `$key`=%s ";
		$prepared_values[] = sanitize_text_field($value);
	}

	$sql .= "$orderby $limit";

	if ($prepared_values) {
		$sql = $wpdb->prepare($sql, $prepared_values);
	}
	
	return $sql;
}



function educare_table_exists($table_name) {
	global $wpdb;
	// Check if table exists using INFORMATION_SCHEMA
	$table_exists = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
		DB_NAME, $table_name
	));

	return (bool) $table_exists; // Returns true if exists, false otherwise
}




/**
 * Generates SQL query for fetching data from a specific table based on provided filters.
 *
 * This function generates an SQL query for fetching data from a specific table based on provided filters and options.
 * It constructs the SQL query using the WordPress $wpdb global object and prepares the query with sanitized values.
 * The resulting SQL query string is returned.
 *
 * @param string $roles The user's role (e.g., 'results', 'students', 'teachers').
 * @param array $requred_fields An array of required fields and filtering options.
 * @param bool $crud Whether the query is for CRUD (Create, Read, Update, Delete) operations. Default is false.
 * @return string The dynamically generated SQL query string.
 */
function educare_get_sql_new($roles, $requred_fields, $crud = false) {
	global $wpdb;
	// Define table name
	$table = $roles;
	$table_name = $wpdb->prefix.EDUCARE_PREFIX.$table;

	if (!educare_table_exists($table_name)) {
		return false;
	}

	// Build the SELECT query
	if ($requred_fields) {
		$sql = "SELECT * FROM $table_name WHERE ";
	} else {
		$sql = "SELECT * FROM $table_name ";
	}
	
	$prepared_values = array();

	foreach ($requred_fields as $key => $value) {
		// We need to encrypt the plain text password using wp_hash_password() to match the stored encrypted password in the user_pin field. But  wp_hash_password() generates a different hash each time it's called, even for the same password. In that case, you won't be able to directly compare the encrypted password stored in the database with the hashed password generated by wp_hash_password(). To verify the password, you can use the wp_check_password() function instead.
		if ($key == 'user_pin') {
			continue;
			// $value = wp_check_password($value);
		}

		$sql .= "`$key`=%s AND ";

		$prepared_values[] = $value;
	}

	// Check to ignore specific ID
	if ($crud) {
		if (isset($_POST['id']) && !empty($_POST['id'])) {
			$id = sanitize_text_field( $_POST['id'] );
			$sql .= $wpdb->prepare('id <> %d AND ', $id);
		}
	}
	
	// Remove the last 'AND'
	$sql = rtrim($sql, 'AND ');
	$sql = $wpdb->prepare($sql, $prepared_values);
	return $sql;
}



/**
 * Handles CRUD (Create, Read, Update, Delete) operations for educare data.
 *
 * This function processes the CRUD operations for educare data based on user input from POST requests.
 * It performs various checks, validations, and database operations to insert, update, or delete data records.
 *
 * @param bool $import Whether the function is used for data import. Default is false.
 * @return string|null A status message indicating the result of the CRUD operation or null.
 */
function educare_crud_data_new($import = false) {
	if (isset($_POST['roles'])) {
		$roles = sanitize_text_field( $_POST['roles'] );
	} else {
		// return 'Roles Must be requred';
		$roles = 'results';
	}

	if ($roles === 'teachers') {
		return educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like student and staffer profiles.', false);
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		global $wpdb;
		// Define table name
		$table = $roles;
		$table_name = $wpdb->prefix.EDUCARE_PREFIX.$table;

		if (!educare_table_exists($table_name)) {
			if (!isset($_POST['search'])) {
				return educare_show_msg('Error occurred to handle it', false);
			}
		}

		$connect_wp_users = educare_check_status('connect_wp_users');
		
		if (isset($_POST['form_settings']['connect_wp_users'])) {
			$connect_wp_users = $_POST['form_settings']['connect_wp_users'];

			if ($connect_wp_users == 'on') {
				$connect_wp_users = 'checked';
			} else {
				$connect_wp_users = 'unchecked';
			}
		}

		// Define sample data to be inserted
		// $data = array();
		// Define the ID to ignore
		$requred_fields = array();
		$id = $sql = '';
		
		if (isset($_POST['id']) && !empty($_POST['id'])) {
			global $wpdb;
			$id = sanitize_text_field( $_POST['id'] );

			$requred_fields = array(
				'id' => $id
			);
			// Get data only for specific ID
			// SELECT * FROM wp_educare_students WHERE `id`='10'
			$sql = educare_get_sql_new($roles, $requred_fields);

			if ($sql) {
				$results = $wpdb->get_row($sql);
			} else {
				$results = 0;
			}

			if ($results) {
				$data = json_decode(json_encode($results), true);
				unset($data['id']);
			}
			
		}
		
		// Process edit action otherwise CRUD
		if (!isset($_POST['edit'])) {
			// Process CRUD actions
			$requred_fields = educare_get_requred_fields($roles, $_POST['default']);
	
			// Notify when requred data is empty
			if (educare_is_empty($requred_fields)) {
				if ($import) {
					return 'empty';
				} else {
					// Notify Empty requred fields
					echo educare_is_empty($requred_fields);

					if(isset($_POST['search'])) {
						educare_get_search_form();
					}
				}
				
			} else {
				// Requred data is filled
				global $wpdb;

				$sql = educare_get_sql_new($roles, $requred_fields, true);

				if ($sql) {
					$results = $wpdb->get_row($sql);
				} else {
					$results = 0;
				}
				
				if ($results) {
					$data = json_decode(json_encode($results), true);
					unset($data['id']);
				}
			
				// Procces data
				if (isset($_POST['default']) && !empty($_POST['default'])) {
					// The 'details' POST variable exists and is not empty
					foreach ($_POST['default'] as $key => $value) {
						// do something with details
						$key = sanitize_text_field( $key );
						$value = sanitize_text_field( $value );
						
						if ($value) {
							$data[$key] = $value;
						}

					}
				} else {
					// The 'details' POST variable does not exist or is empty
					// Handle the error or set a default value
					if ($import) {
						return 'empty';
					} else {
						return educare_show_msg('Requred data is missing!');
					}
				}

				if (isset($_POST['Details']) && !empty($_POST['Details'])) {
					// The 'details' POST variable exists and is not empty
					$data['Details'] = json_encode($_POST['Details']);
				} else {
					// The 'details' POST variable does not exist or is empty
					// Handle the error or set a default value
					// $data['Details'] = '';
					$data['Details'] = json_encode(array());
				}

				if (isset($_POST['Others']) && !empty($_POST['Others'])) {
					// The 'details' POST variable exists and is not empty
					$data['Others'] = json_encode($_POST['Others']);
				} else {
					// The 'details' POST variable does not exist or is empty
					// Handle the error or set a default value
					// $data['Others'] = '';
					$data['Others'] = json_encode(array());
				}

				if (isset($_POST['Subject'][0]) && !empty($_POST['Subject'][0])) {
					$data['Subject'] = json_encode($_POST['Subject'][0]);
				} else {
					// Handle the error or set a default value
					// $data['Subject'] = '';
					$data['Subject'] = json_encode(array());
				}
			}

		}

		// Check is requred data missing or not
		if (educare_is_empty($requred_fields)) {
			// 'No data found for insert'
			// return educare_show_msg('Requred data is missing!', false);
			return;
		}
		
		// execute the query and get the first row
		if ($sql) {
			$row = $wpdb->get_row($sql);
		} else {
			$row = null;
		}
		
		// if data not found
		if ($row !== null) {
			// data found or Data already exists
			if(isset($_POST['search']) or isset($_POST['edit']) or isset($_POST['auto_fill'])) {
				// Store all data in to post. 
				foreach ($row as $key => $value) {
					if ($key == 'Details' or $key == 'Others' or $key == 'Subject') {
						if ($key == 'Subject') {
							$_POST[$key][0] = json_decode($value, true);
						} else {
							$_POST[$key] = json_decode($value, true);
						}
						
					} else {
						$_POST['default'][$key] = sanitize_text_field( $value );
					}
				}
				
				// Show forms
				if (!isset($_POST['auto_fill'])) {
					educare_get_forms();
				}
				
				return;
			} else {
				// Data already exists, return error message or do something else
				if ($import) {
					return 'exist';
				} else {
					return educare_show_msg(esc_html( ucfirst($roles) ) . ' already exists', false);
				}
			}

		} else {
			// unique data or not found
			// if request for find results
			if (isset($_POST['search']) or isset($_POST['edit']) or isset($_POST['auto_fill'])) {
				echo educare_show_msg('Data not found', false);

				if (isset($_POST['search'])) {
					educare_get_search_form();
				}
				
				return;
			} else {
				// if request for update or edit
				if ($id) {
					// Define the where condition
					$where = array(
						// replace with the ID of the row to update
						'id' => $id,
					);

					// Perform the update
					if (isset($_POST['delete'])) {
						// Execute the DELETE query
						$process = $wpdb->delete( $table_name, $where );
					} else {
						$process = $wpdb->update( $table_name, $data, $where );
					}

					// Check if the update was successful
					if ( $process === false ) {
						// handle error
						// Error to porocess request
						return educare_show_msg('There was an error processing your request');
					} elseif ( $process == 0 ) {
						// no rows were updated, handle accordingly
						return educare_show_msg(__('No changes were found in this request.', 'educare'));
					} else {
						// update was successful, handle accordingly
						// Perform the update
						if (isset($_POST['delete'])) {
							// Execute the DELETE query
							$_POST = array();
							return educare_show_msg('Successfully deleted ' . esc_html( $roles ));
						} else {
							return educare_show_msg(esc_html( ucfirst($roles) ) . ' update was successful');
						}
					}

				} else {
					// if request for insert
					// Data is unique, insert into table
					$insert = $wpdb->insert($table_name, $data);
					
					if ($insert === false) {
						// Error occurred, handle it
						
						if ($import) {
							return 'error';
						} else {
							return educare_show_msg('Error occurred to handle it', false);
						}

					} else {
						// Data inserted successfully
						$data_id = $wpdb->insert_id;
						$_POST['default']['id'] = $data_id;

						// Show success msgs
						if ($import) {
							return 'success';
						} else {
							return educare_show_msg(esc_html( ucfirst($roles) ) . ' inserted successfully');
						}
						
					}
				}
			}
		}
	}
}



function educare_results_status($print) {
	$active_template = educare_check_status('results_card_template');

	if (!function_exists($active_template)) {
		$active_template = 'educare_default_results_card';
	}

	return $active_template($print, true);
}




function educare_template_banner($school_id = null) {
	return false;
}



function educare_show_school_banner($school_id = null) {
	return false;
}


?>