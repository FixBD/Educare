<?php
/** 
 * ### Default results style
 * 
 * Usage example: 
 * 1. For back end: add_action('admin_enqueue_scripts', 'educare_results_style');
 * 2. For front end: add_action('wp_enqueue_scripts', 'educare_results_style');t);
 * 
 * ==== Message for Users ====
 * If you like to remove default results style, just add this (below) action into your function.php files. Notes: if you remove default style, results system style will be going to inherit mode! or sometimes - it will adjust your themes style (like - table, th, tr, td). So, you need to add custom style for styleling your results systems.
 * 
 * For remove default style -
 * remove_action( 'wp_enqueue_scripts', 'educare_results_style' );
 * 
 * Recommended way to styling results system to overwrite default style, with your custom style. It's better than remove default style.
 * 
 * ==== Default style (results.css) source: ====
 * @link URL: https://github.com/fixbd/educare/assets/css/results.css
 * @see Plugin EDUCARE_URL. /assets/css/results.css
 * 
 * For add your custom style (CSS)
 * ==================================================================
		add_action('wp_enqueue_scripts', 'custom_results_style');

		function custom_results_style() {
			wp_enqueue_style('custom_results_style', get_template_directory_uri().'/assets/css/results.css');
		}
 * ==================================================================
 * 
 * @since 1.0.0
 * @last-update 1.0.0
 * 
 * @return void
 */

function educare_results_style() {
  wp_enqueue_style('educare_results', EDUCARE_URL.'assets/css/results.css', array(), '1.0', 'all');

	// JavaScript link
	wp_enqueue_script('jquery'); // That's men script now place at the bottom
	wp_enqueue_script('jquery-min', EDUCARE_URL.'assets/js/jquery-2.1.3.min.js');
}

add_action('wp_enqueue_scripts', 'educare_results_style');


?>