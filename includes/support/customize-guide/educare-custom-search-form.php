<?php

/**
 * Custom Educare Search Form
 *
 * This function generates a customized search form for retrieving student results.
 *
 * ## Usage:
 * - This function is hooked into `educare_custom_search_form`, allowing customization  
 *   of the search form used in Educare.
 * - It captures user input, sanitizes it, and displays relevant search fields.
 *
 * ## Important Notes:
 * To modify the search forms, you **must** select the default search form template in Educare search form templates.  
 * - Ensure that the form fields are properly validated and sanitized before processing.
 * - The Google reCAPTCHA is included for security; make sure the site key is correctly configured.
 *
 * @since 1.5.0
 * @last-update 1.6.0
 *
 * @param object|array $print Student data or validation messages.
 * @return void
 */

function educare_my_custom_search_form($print) {
	// Capture and sanitize form data
	$Roll_No = $Regi_No = $Class = $Exam = $Year = '';

	// keep the form data if submitted
	if (!empty($_POST)) {
		foreach ($_POST as $key => $value) {
			$$key = sanitize_text_field($value);
		}
	}

	// Display error messages if any exist
	if (!empty($print) && isset($print['msgs'])) {
		echo '<div class="alert alert-danger" role="alert">';
		echo wp_kses_post($print['msgs']);
		echo '</div>';
	}
	?>

	<form class="educare-form box content bg-light educare-search-form" method="post" id="educareForm">
		<p>Roll No:</p>
		<input type="number" name="Roll_No" value="<?php echo esc_attr($Roll_No); ?>" placeholder="Enter Roll No">

		<p>Regi No:</p>
		<input type="number" name="Regi_No" value="<?php echo esc_attr($Regi_No); ?>" placeholder="Enter Regi No">

		<p>Class:</p>
		<select name="Class">
			<?php educare_get_options('Class', $Class); ?>
		</select>

		<p>Exam:</p>
		<select name="Exam">
			<?php educare_get_options('Exam', $Exam); ?>
		</select>

		<p>Select Year:</p>
		<select name="Year">
			<?php educare_get_options('Year', $Year); ?>
		</select>

		<?php
		// Google reCAPTCHA integration
		$site_key = educare_check_status('site_key');
		if (!empty($site_key)) {
			echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
		}
		?>

		<button id="educare_results" class="btn btn-primary results_button button" name="educare_results" type="submit">View Results</button>
	</form>

<?php
}

// Hook the custom search form into Educare
add_action('educare_custom_search_form', 'educare_my_custom_search_form', 10, 1);
