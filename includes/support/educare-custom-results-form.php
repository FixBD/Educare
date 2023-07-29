<?php
/**
 * ### educare_my_custom_results_forms();
 * 
 * function for customize educare (results) search forms
 *
 * usage => 
 * 1. Copy and paste this function and action (hook) in your themes functions.php files
 * 2. allow/enable 'Custom Design Permission' in plugin settings (because we care of your security)
 * 3. Throw your logic [code] under 'specified' function for customize results forms
 * 4. Done, now you can see your custom design!
 *
 * Notes: When you add or hook custom functionality for customize the results card or search forms, you need to allow/enable the 'Custom Design Permission' options in the (educare) plugin settings. Otherwise, this function will be ignored.
 *
 * You need to keep all forms [name] as default. like -
 * 1. <select name="Class">
 * 2. <select name="Exam">
 * 3. <select name="Year">
 * 4. <input name="Roll_No">
 * 5. <input name="Regi_No">
 * 6. <button name="educare_results">
 *
 * How to customize educare results card (table) ?
 * Follow this files =>
 * @link GitHub: https://github.com/FixBD/Educare/blob/educare/includes/support/educare-custom-results-card.php
 * @see Plugin Dir: educare/includes/support/educare-custom-results-card.php
 * 
 * @since 1.4.0
 * @last-update 1.4.2
 * 
 * @return mixed
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

function educare_my_custom_results_forms() {
	// Kep form data
	if (isset($_POST)) {
		foreach ($_POST as $key => $value) {
			$$key = sanitize_text_field( $value );
		}
 	} else {
		$Roll_No = $Regi_No = $Class = $Exam = $Year = '';
	}
	
	?>

	<form class="add_results" action="" method="post" id="edit">

		<p>Roll No:</p>
		<input type="number" name="Roll_No" value="<?php echo esc_attr( $Roll_No )?>" placeholder="Enter Roll No">

		<p>Regi No:</p>
		<input type="number" name="Regi_No" value="<?php echo esc_attr( $Regi_No )?>" placeholder="Enter Regi No">
		
		<p>Class:</p>
		<select name="Class">
			<?php educare_get_options('Class', $Class);?>
		</select>
		
		<p>Exam:</p>
		<select name="Exam">
			<?php educare_get_options('Exam', $Exam);?>
		</select>

		<p>Select Year:</p>
		<select name="Year">
			<?php educare_get_options('Year', $Year);?>
		</select>
		
		<?php
		$site_key = educare_check_status('site_key');
		echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($site_key).'"></div>';
		?>

		<button id="results_btn" type="submit">View Results</button>
	</form>
	<?php
}

// Hook/Apply specified function with 'educare_custom_results_forms'
add_action( 'educare_custom_results_forms', 'educare_my_custom_results_forms' );

?>