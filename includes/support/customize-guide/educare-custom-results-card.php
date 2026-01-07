<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Custom Educare Results Template
 *
 * This function allows customization of the Educare results card.
 *
 * ## Usage:
 * 1. Ensure that you have selected the **Educare Default Results Template** from  
 *    **WP Dashboard > Educare > Settings > Results Card**. This is required because,  
 *    only the default results card supports custom design hooks.
 * 2. Copy and paste this function along with the action (hook) into your active theme's `functions.php` file.
 * 3. Implement your custom logic within `educare_my_custom_results_template` or your own function to modify the results card.
 * 4. Once completed, reload the results page where you use the Educare shortcode to display results, and your custom design will be applied.
 *
 * ## Important Notes:
 * - To modify the results card or search forms, you **must** select the default template.  
 *   Otherwise, this function will not be executed.
 * - This function accepts a single parameter `$print`, which contains student data.  
 *   You must pass this argument, though you may rename it as needed.
 *
 * ## Default Educare Results Card:
 * - **GitHub:** [Educare Default Results Card](https://github.com/FixBD/Educare/blob/educare/includes/support/educare-default-results-card.php)
 * - **Plugin Directory:** `educare/includes/support/educare-default-results-card.php`
 *
 * ## Customizing Educare Search Forms:
 * - **GitHub:** [Educare Custom Search Form](https://github.com/FixBD/Educare/blob/educare/includes/support/educare-custom-search-form.php)
 * - **Plugin Directory:** `educare/includes/support/educare-custom-search-form.php`
 *
 * @since 1.5.0
 * @last-update 1.6.0
 *
 * @param object|array $print Student data for the results card.
 * @return void
 */

 function educare_my_custom_results_template($print) {
	// Developers: Use this function to implement a custom results template.
	// The $print parameter contains the student data you wish to display.
	// echo '<pre>';	
	// print_r($print);	
	// echo '</pre>';

	// Example: Display results using the Educare default marks style. You can customize this as needed or create your own design from scratch.
	echo '<div class="m-3">';
		educare_get_marks_terms($print);
	echo '</div>';
}

// Hook the custom results template into Educare.
add_action('educare_custom_results', 'educare_my_custom_results_template', 10, 1);

// happy coding :)

?>