<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}




/**
 * Generates the default search form layout and displays a search form for retrieving student results using the default search form template.
 *
 * This function is responsible for generating and displaying the default search form layout for retrieving student results. It takes
 * various parameters, including a student data object, template details, and settings. If template details are provided, it returns
 * the default template information. If a student data object is provided, it processes the form data, displays the search form
 * layout with input fields and controls, and handles form submission for searching student results or generating certificates. The
 * function also handles reCAPTCHA integration if enabled. The generated layout is based on the default search form template and can
 * be customized using the provided template information and settings.
 *
 * @since 1.4.2
 * @last-update 3.4.8
 * 
 * @param object|null $print The student data object containing details like marks, subjects, and remarks. Not used in this function.
 * @param bool $template_details Optional. If true, returns the default template information. Default is false.
 * @param array $settings Optional. An array of settings for controlling the behavior of the search form. Default is false.
 * @return array|null The default template information if $template_details is true, otherwise null.
 */
function educare_default_search_form($print = null, $template_details = false, $settings = false) {
  // Define customizable fields (default value).
  $fields = array(
    'results_button' => [                     // Fields name
      'title' => 'Results Button',            // Fields title
      'subtitle' => 'Results button title',   // Messages || guide for this fields
      'value' => 'View Results'               // Fields value
    ],
    'certificate_button' => [
      'title' => 'Certificate Button',
      'subtitle' => 'Certificate button title',
      'value' => 'Get Certificate'
    ]
  );

  // Get cutomized fields
  $fields = educare_get_template_settings($fields, __FUNCTION__);

  // Set template details (title or thumbnail)
	if ($template_details) {
		$template_info = array(
			'title' => 'Default Search Form',
			'thumbnail' => EDUCARE_URL . 'assets/img/templates-preview/search-form-templates/default-search-form.jpg', // for default use EDUCARE_TEMPLATE_THUMBNAIL
      'fields' => $fields, // Default text value
			'control_data' => false, // Ingnore results template
			'control_msgs' => true, // To show custome error||succse mesgs
		);
		// return info
		return $template_info;
	} else {
		// Check if custom search form is available. If available, use it. Otherwise, use the default search form.
		if (has_action('educare_custom_search_form')) {
			do_action('educare_custom_search_form', $print);
			return;
		}

		// Show spinner when ajax request
		echo '<div id="educare-loading"><div class="educare-spinner"></div></div>';
		
		?>
		<div class="result_body">
			<div class="results-container">
				<?php 
				if (educare_check_status('show_banner') == 'checked') {
					?>
					<div class="fixbd-flex banner">
						<div>
							<img src="<?php echo esc_url(educare_get_attachment(0, 'logo1'))?>">
						</div>
						<div class="title">
							<h1>Institutions Name Or Title</h1>
							<p class="sub-title">Rangpur, Dhaka, Bangladesh></p>
							<p class="sub-title">Founded in <?php echo esc_html(date('Y'))?></p>
						</div>
						<div>
							<img src="<?php echo esc_url(educare_get_attachment(0, 'logo2'))?>">
						</div>
					</div>
					<?php

					if (isset($print['msgs'])) {
						$status = 'error ';
						if ($print['status']) {
							$status = 'success ';
						}
						
						echo '<div class="notice notice-'.esc_attr($status).' is-dismissible"><p>'.wp_kses_post( $print['msgs'] ).'</p></div>';
					}

				}

				echo '<form class="educare-form box content bg-light educare-search-form" method="post" id="educareForm">';
					// Check reCAPTCHA status
					$re_captcha = educare_check_status('re_captcha');
					$always_re_captcha = educare_check_status('always_re_captcha');
					// Check requred fields data
					$requred = educare_check_status('display');
					// Getting all requered field key and title
					$requred_title = educare_requred_data($requred, true);
					// Remove name field from $requred_title. because, we don't need to name field to find/search the results
					unset($requred_title['Name']);
					unset($requred_title['Group']);
					
					foreach ($requred_title as $key => $title) {
						// Define default value for handle php error
						$value = '';
						// Getting fields value
						if (isset($_POST[$key])) {
							$value  = sanitize_text_field( $_POST[$key] );
						}

						echo '<div class="row">';
						echo '<div class="col-25">';
						echo '<label for="'.esc_attr($key).'">'.esc_html__($title, 'educare').'</label>';
						echo '</div>';
						echo '<div class="col-75">';

							// for input fields
							if ($key == 'Roll_No' || $key == 'Regi_No' or $key == 'user_pin') {
								// $type = 'number';
								$type = 'text';

								if ($key == 'user_pin') {
									$type = 'password';
								}

								echo '<input type="'.esc_attr($type).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'" placeholder="'.esc_attr__($title, 'educare').'">';
							} else {
								// for select fields
								echo '<select id="'.esc_attr($key).'" name="'.esc_attr($key).'">';
								educare_get_option($key);
								echo '</select>';
							}
							
						// close div
						echo '</div>';
						echo '</div>';
					}

          // Button and Recaptcha
					echo '<input type="hidden" name="educare_results">';
					echo '<div class="row">
						<div class="col-25"></div>
						<div class="col-75">';
              if ($re_captcha == 'checked' && ($always_re_captcha == 'unchecked' && is_user_logged_in() === false || $always_re_captcha == 'checked')) {
                $site_key = educare_check_status('site_key');
                
                if ( current_user_can( 'manage_options' ) and empty($site_key)) {
                  echo educare_show_msg(
                    sprintf(
                      __(
                        '<b>The Google Recaptcha checkbox field is hidden. Please enter/paste your google recaptcha v2 site key at</b><hr><a href="%s" target="_blank"><code>Educare > Settings > Security > Site Key</code></a><br><small>Notes: Only admin can view these messages</small>',
                        'educare'
                      ),

                      esc_url( admin_url() ).'/admin.php?page=educare-settings&menu=Security'
                    ),

                    false
                  );
                }
    
                echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($site_key).'"></div>';
              }

              if ($settings['results'] == 'true') {
                echo '<button id="educare_results" class="btn btn-primary results_button button" name="educare_results" type="submit">'.esc_html__($fields['results_button']['value'], 'educare').'</button>';
              }
						echo '</div>
					</div>';

					// Close all section
				echo '</form>';
			echo '</div>';
		echo '</div>';
	}
}

// Hook the function to the educare_search_form_template action
add_action( 'educare_search_form_template', 'educare_default_search_form' );

?>