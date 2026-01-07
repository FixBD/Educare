<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


function educare_search_form($atts) {
  $row = $error = $msgs = $ajaxRequest = $search_form = $control_data = $control_msgs = null;
	// Get active search form template
	$active_search_form = educare_check_status('search_form_template');

	if (!$active_search_form) {
		if (current_user_can( 'manage_options' )) {
			_e('Educare <b>Settings</b> database not found.', 'educare');
		}
		return;
	}
	
	// Check if active search form has custome requred fields
	if (function_exists($active_search_form)) {
		$search_form = $active_search_form('', true);

		if (isset($search_form['control_data']) && !empty($search_form['control_data'])) {
			$control_data = true;
		}

		if (isset($search_form['control_msgs']) && !empty($search_form['control_msgs'])) {
			$control_msgs = true;
		}
	}
	
	// if Ajax request
	if (isset($_POST['form_data'])) {
		$ajaxRequest = true;
		$nonce = sanitize_text_field($_POST['nonce']);
		$action_for = sanitize_text_field($_POST['action_for']);
		// Remove the backslash
		$_POST['form_data'] = stripslashes($_POST['form_data']);
		// parses query strings and sets the parsed values into the $_POST array.
		wp_parse_str($_POST['form_data'], $_POST);
		$_POST['nonce'] = $nonce;
		$_POST[$action_for] = true;
	}

  if (isset($_POST['id']) || isset($_POST['educare_results']) || isset($_POST['educare_certificate'])) {
		// Verify the nonce to ensure the request originated from the expected source
		educare_verify_nonce();
		
    // if request to get result by id
    if (isset($_POST['id'])) {
			// check if users is admin and can manage_options or not. Beacause, only admin can accsess results by ID
			if ( current_user_can( 'manage_options' ) || current_user_can( 'educare_admin' )) {
				// Get data only for specific ID, like this =>
				// $sql = "SELECT * FROM wp_educare_students WHERE `id`='10'";
				$requred_fields = array(
					'id' => sanitize_text_field( $_POST['id'] )
				);
			} else {
				$error = '<b>Sorry, you are not allowed to access this request.!</b><p>Please search results using this form.</p>';
				$requred_fields = array(
					'id' => ''
				);
			}
    } else {
      // Get data bassed on educare settings, like this =>
      // $sql = "SELECT * FROM wp_educare_students WHERE `Roll_No`='45' AND `Regi_No`='45' AND `Class`='JSS 1' AND `Year`='2022'";
      // in this function {educare_get_requred_fields('results', $_POST)} check educare settings and get all (default) checked field and combine with $_POST data value with all requred fields key
      $requred_fields = educare_get_requred_fields('results', $_POST);

			// Check if active search form has custome requred fields
			if ($search_form) {
				if (isset($search_form['requred_fields']) && !empty($search_form['requred_fields'])) {
					$requred_fields = educare_combine_fields($search_form['requred_fields'], '', $_POST, true);
				}
			}

			// get only published data
			$requred_fields['Status'] = 'publish';
			
			$re_captcha = educare_check_status('re_captcha');
			$always_re_captcha = educare_check_status('always_re_captcha');

			// check educare re_captcha status and execute
			if ($re_captcha == 'checked') {

				if ($always_re_captcha == 'unchecked' && is_user_logged_in() === false || $always_re_captcha == 'checked') {
					if (isset($_POST['g-recaptcha-response']) and $_POST['g-recaptcha-response'] != "") {
						$secret = educare_check_status('secret_key');
						$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
						$responseData = json_decode($verifyResponse);
						
						if (! $responseData->success) {
							$error = 'Invalid request!';
						}
						
					} else {
						$error = '<p>Make sure you are not a robot!</p>';

						if ( current_user_can( 'manage_options' ) and educare_check_status('site_key') == '' ) {
							$error .= '<p>The Google Recaptcha checkbox field is hidden. Please enter/paste your google recaptcha v2 site key at </p><p><a href="'.esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Security" target="_blank"><code>Educare > Settings > Security > Site Key</code></a></p><p>To ignore these messages, please enter/paste google recaptcha key or disable Google Recaptcha options from educare settings</p><p>Notes: Only admin can view these messages</p>';
						}
					}
				}
			}
    }

		if ($error) {
			// echo educare_show_msg($error, false);
			$msgs = array (
				'msgs' => $error,
				'type' => 'recaptcha',
				'status' => false
			);
		} else {
			// Check if any key value empty or not
			if (educare_is_empty($requred_fields)) {
				// Requred data/fields value is missing
				// echo educare_is_empty($requred_fields);
				$msgs = array (
					'msgs' => educare_is_empty($requred_fields, '', true),
					'type' => 'empty fields',
					'status' => false,
				);
			} else {
				// Requred data/fields value is valid or filled
				// Process data
				global $wpdb;
				$sql = educare_get_sql_new('results', $requred_fields);
				// execute the query and get the first row
				$row = $wpdb->get_row($sql);

				// data found
				if ($row !== null) {
					// data found or Data exists
					if ($control_data) {
						$msgs = array (
							'msgs' => 'Success, Data Found!',
							'type' => 'found',
							'data' => $row,
							'status' => true
						);
					} else {
						$access = true;

						if ($access) {
							// Get active results card template to show results
							$active_results_card = educare_check_status('results_card_template');
							$active_template = $active_results_card;

							// if request for get certificate
							if (isset($_POST['educare_certificate'])) {
								// Get active certificate template to show certificate
								$active_template = educare_check_status('certificate_template');
								// get active certificate template settings
								if (function_exists($active_template)) {
									$template_data = call_user_func($active_template, null, true);

									// Get template based result system
									if (isset($template_data['prepare_data'])) {
										// Check active result card template function
										if (function_exists($active_results_card)) {
											$row = call_user_func($active_results_card, $row, true);
										}
									}
								}
								
							}
							
							// Check if function exists or not to ingnore php error
							if (function_exists($active_template)) {
								// echo educare_show_msg('Success, Data Found!', true, false);

								ob_start();
								call_user_func($active_template, $row, false, $atts);
								$print_data = ob_get_clean();
								echo wp_kses_post($print_data);

								// Record status
								$msgs = array (
									'msgs' => 'Success, Data Found!',
									'type' => 'found',
									'status' => true
								);
							} else {
								// Show error msgs for admin, when results_card_template function not found
								if (current_user_can( 'manage_options' )) {
									// return educare_show_msg('Sorry, Error to load results card template. Your active results card template ('.esc_html($active_template).') is missing. Please, go to educare settings and change your current results card template.', false);
									$msgs = array (
										'msgs' => 'Sorry, Error to load results card template. Your active results card template ('.esc_html($active_template).') is missing. Please, go to educare settings and change your current results card template.',
										'type' => 'template not found',
										'status' => false
									);
								} else {
									// Show error msgs for users
									// return educare_show_msg('Sorry, Error to load data', false);
									$msgs = array (
										'msgs' => 'Sorry, Error to load data',
										'type' => 'template not found',
										'status' => false
									);
								}
							}

							// if request by Ajax
							if ($ajaxRequest) {
								// Close or remove 0 from ajxa response
								die;
							} else {
								return;
							}
						}
					}
					
				} else {
					// not found
					// echo educare_show_msg('Sorry, Data Not Found!');
					$msgs = array (
						'msgs' => 'Sorry, Data Not Found!',
						'type' => 'not found',
						'status' => true
					);
				}
			}
		}
  }

	// Search Form
	if ($control_data) {
		echo $active_search_form($msgs, false, $atts);
	} else {
		if ($row === null) {
			// Show msgs
			if (isset($msgs['msgs'])) {
				if (!$control_msgs) {
					echo educare_show_msg($msgs['msgs'], $msgs['status'], false);
				}
			}

			// Show form for search the result
			// Check if function exists or not to ingnore php error
			if (function_exists($active_search_form)) {
				echo $active_search_form($msgs, false, $atts);
			} else {
				// Show error msgs for admin
				if (current_user_can( 'manage_options' )) {
					return educare_show_msg('Sorry, Error to load search form template. Your active search form template ('.esc_html($active_search_form).') is missing. Please, go to educare <a href="'.esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Search_Form" target="_blank"><code>Educare > Settings > Search Form > Site Key</code></a> and change your current search form template.', false, false);
				} else {
					// Show error msgs for users
					return educare_show_msg('Sorry, Error to load data', false, false);
				}
			}
		}
	}

	// if request by Ajax
	if ($ajaxRequest) {
		// Close or remove 0 from ajxa response
		die;
	}
}



/**
 * ### Front end educare results system
 * 
 * Usage =>
 * in WordPress Editor: [educare_results]
 * in PHP files: do_shortcode( '[educare_results]' );
 * 
 * @since 1.0.0
 * @last-update 1.4.2
 * 
 * @return mixed
 */
function educare_results_form($atts) {
	// Extract attributes and their default values
	$atts = shortcode_atts(array(
		'results' => 'true',
		'certificate' => 'false',
	), $atts, 'educare_results');

	// Shortcode attributes not passed through to AJAX functions in wordpres. So, we need to wp_localize_script $atts and pass this thought Ajax
	wp_enqueue_script('educare-shortcode-script', EDUCARE_URL.'assets/js/educare-shortcode.js', array('jquery'), '1.0', false);
	wp_localize_script( 'educare-shortcode-script', 'shortcodeSettings', $atts );

	// Get $atts thought Ajax
	if (isset($_POST['settings']) && is_array($_POST['settings'])) {
    // Escape the values in the $_POST['settings'] array
    $escaped_settings = array_map('sanitize_text_field', $_POST['settings']);
    // Assign the escaped array to the $atts variable
    $atts = $escaped_settings;
	}

	// start output buffering
	ob_start();
	// Main content
	echo '<div id="educareResults">';
	echo educare_search_form($atts);
	echo '</div>';
	// return the output buffer contents
	return ob_get_clean();
}

// Create shortcode for educare results and certificate system
add_shortcode('educare_results', 'educare_results_form' );
add_action('wp_ajax_nopriv_educare_results_form', 'educare_results_form');
add_action('wp_ajax_educare_results_form', 'educare_results_form');


?>