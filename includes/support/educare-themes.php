<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


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
 * @last-update 1.6.0
 * @return void
 */
function educare_results_style() {
  // Enqueue 'bootstrap' stylesheet if not enqueued
  if (!wp_style_is('bootstrap', 'enqueued')) {
    wp_enqueue_style('bootstrap', EDUCARE_URL . 'assets/css/bootstrap.min.css');
  }

  // Enqueue 'educare-results' stylesheet
  wp_enqueue_style('educare-results', EDUCARE_URL . 'assets/css/results.css', array(), '2.0', 'all');

  // Enqueue 'jquery' script if not enqueued
  if (!wp_script_is('jquery', 'enqueued')) {
    wp_enqueue_script('jquery');
  }

  // Enqueue 'bootstrap' script if not enqueued
  if (!wp_script_is('bootstrap', 'enqueued')) {
    wp_enqueue_script('bootstrap', EDUCARE_URL . 'assets/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3', true);
  }

  // Enqueue 'recaptcha-v2' script
  wp_enqueue_script('recaptcha-v2', 'https://www.google.com/recaptcha/api.js', array(), null, true);
}

add_action('wp_enqueue_scripts', 'educare_results_style');

// Add async and defer to 'recaptcha-v2' script loading tag.
add_filter(
  'script_loader_tag',
  function ($tag, $handle) {
    // Check for the handle we used when enqueuing the script.
    if ('recaptcha-v2' !== $handle) {
      return $tag;
    }
    // Add async and defer at the end of the opening script tag.
    return str_replace('></', ' async defer></', $tag);
  },
  20,
  2
);


function educare_encryptData($data, $educare) {
  $data = json_encode($data);
  $ivLength = openssl_cipher_iv_length('aes-256-cbc');
  $iv = openssl_random_pseudo_bytes($ivLength);
  $encrypted = openssl_encrypt($data, 'aes-256-cbc', $educare, 0, $iv);
  return base64_encode($iv . $encrypted);
}

function educare_decryptData($data, $educare) {
	$data = base64_decode($data);
	$ivLength = openssl_cipher_iv_length('aes-256-cbc');
	$iv = substr($data, 0, $ivLength);
	$encrypted = substr($data, $ivLength);

  if ($encrypted) {
    $get_data = openssl_decrypt($encrypted, 'aes-256-cbc', $educare, 0, $iv);
    
    if ($get_data) {
      return json_decode($get_data);
    }
  }

  return false;
}



function educare_enqueue_front_script() {
	wp_enqueue_script('educare-front-script', EDUCARE_URL.'assets/js/educare-front.js', array('jquery'), '2.0', false);
	wp_localize_script( 'educare-front-script', 'educareAjax', array(
			'url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'educare_form_nonce' )
	) );
}

add_action( 'admin_enqueue_scripts', 'educare_enqueue_front_script' );
add_action( 'wp_enqueue_scripts', 'educare_enqueue_front_script' );

?>