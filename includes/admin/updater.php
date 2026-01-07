<?php
// ###################################
//  SENSITIVE DATA, DON'T TOUCH ME!!!
// ###################################

// Update checker
class educareUpdateChecker {
    private $in_plugin_api = false;
    public $plugin_slug;
    public $version;
    public $cache_key;
    public $cache_allowed;

    public function __construct() {
        $this->plugin_slug = EDUCARE_FOLDER;
        $this->version = EDUCARE_VERSION;
        $this->cache_key = 'educare_custom_upd';
        $this->cache_allowed = false;

        add_filter('plugins_api', array($this, 'info'), 20, 3);
        add_filter('site_transient_update_plugins', array($this, 'update'));
        add_action('upgrader_process_complete', array($this, 'purge'), 10, 2);
    }

    public function request() {
        $key = sanitize_text_field(educare_get_svr_data('key'));
        $remote = get_transient($this->cache_key);

        if (false === $remote || ! $this->cache_allowed) {
            $remote = wp_remote_get(
                EDUCARE_SVR . '/wp-json/educare/v1/update-info?key=' . urlencode($key),
                array(
                    'timeout' => 5, // Reduced timeout
                    'headers' => array(
                        'Accept' => 'application/json'
                    )
                )
            );

            if (
                is_wp_error($remote)
                || 200 !== wp_remote_retrieve_response_code($remote)
                || empty(wp_remote_retrieve_body($remote))
            ) {
                return false;
            }

            // Cache the response
            set_transient($this->cache_key, $remote, WEEK_IN_SECONDS);
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));
        return $remote;
    }


    function info($res, $action, $args) {
        // do nothing if not getting plugin information
        if ('plugin_information' !== $action || $this->plugin_slug !== $args->slug || $this->in_plugin_api) {
            return $res;
        }

        // getting plugin information
        $this->in_plugin_api = true;

        // Get custom plugin info updates
        $remote = $this->request();

        if ($remote) {
            $args = array(
                'slug' => 'educare',
            );

            // Get the default plugin information
            $default_info = plugins_api('plugin_information', $args);

            // Ensure $default_info is a valid object
            if (is_wp_error($default_info) || ! is_object($default_info)) {
                $default_info = new stdClass();
            }

            // Dynamically replace top-level properties
            foreach (get_object_vars($remote) as $key => $value) {
                // Convert child objects to arrays
                if (is_object($value)) {
                    $default_info->$key = (array) $value;
                } elseif (is_array($value)) {
                    $default_info->$key = $value;
                } else {
                    $default_info->$key = sanitize_text_field($value);
                }
            }

            // Sanitize sections (if it's an object or array)
            if (isset($default_info->sections)) {
                if (is_object($default_info->sections)) {
                    $default_info->sections = (array) $default_info->sections; // Convert sections to array
                    foreach ($default_info->sections as $section_key => $section_value) {
                        $default_info->sections[$section_key] = wp_kses_post($section_value);
                    }
                } elseif (is_array($default_info->sections)) {
                    foreach ($default_info->sections as $section_key => $section_value) {
                        $default_info->sections[$section_key] = wp_kses_post($section_value);
                    }
                }
            }

            // Sanitize banners (if it's an object or array)
            if (isset($default_info->banners)) {
                if (is_object($default_info->banners)) {
                    $default_info->banners = (array) $default_info->banners; // Convert banners to array
                    foreach ($default_info->banners as $banner_key => $banner_value) {
                        $default_info->banners[$banner_key] = esc_url_raw($banner_value);
                    }
                } elseif (is_array($default_info->banners)) {
                    foreach ($default_info->banners as $banner_key => $banner_value) {
                        $default_info->banners[$banner_key] = esc_url_raw($banner_value);
                    }
                }
            }

            return $default_info;
        } else {
            return $res;
        }

        $this->in_plugin_api = false;

        return $res;
    }


    public function update($transient) {
        // Ensure we have plugin data to check
        if (empty($transient->checked)) {
            return $transient;
        }

        // Determine if the API request should be sent.
        $svr_data = educare_get_svr_data();
        $send_request = false;

        // If it's an AJAX request, always send the request.
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/wp-admin/admin-ajax.php') {
            $send_request = true;
        } else {
            add_filter('check_update_current_screen', function ($svr_data) {
                // Ensure the request is in the admin area
                if (!is_admin()) {
                    return false;
                }

                // Ensure the current user has admin privileges
                if (!current_user_can('manage_options')) {
                    // log_it('User is not an admin');
                    return false;
                }

                if ( ! function_exists( 'get_current_screen' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/screen.php';
                }

                // Get the current screen object safely
                $screen = get_current_screen();

                if (!$screen) {
                    // log_it('Unable to get screen information');
                    return false;
                }

                // Allow actions for specific screens
                if ($screen->id === 'plugins') {
                    return true;
                }
                
                if ($svr_data) {
                    $status = isset($svr_data['status']) ? sanitize_text_field($svr_data['status']) : '';

                    if ($status !== 'expired') {
                        return true;
                    }
                }

                // For active licenses, only allow actions on the plugins screen
                return $screen->id === 'plugins';
            });

            // Apply the filter and log the result
            $send_request = apply_filters('check_update_current_screen', $svr_data);
        }

        // If we need to send the request, handle the update check.
        if ($send_request) {
            if (isset($transient->checked[EDUCARE_FILE])) {
                // Use a static variable to prevent repeated execution during the same request.
                static $already_executed = false;
                static $remote = false;

                // Log the request the first time it's executed.
                if (!$already_executed) {
                    $already_executed = true;
                    // Fetch remote data
                    $remote = $this->request();
                }
                
                if (
                    isset($remote->version) &&
                    version_compare($this->version, sanitize_text_field($remote->version), '<') &&
                    version_compare(sanitize_text_field($remote->requires), get_bloginfo('version'), '<=') &&
                    version_compare(sanitize_text_field($remote->requires_php), PHP_VERSION, '<')
                ) {
                    $res = new stdClass();
                    $res->slug        = sanitize_text_field($this->plugin_slug);
                    $res->plugin      = sanitize_text_field(EDUCARE_FILE);
                    $res->new_version = sanitize_text_field($remote->version);
                    $res->tested      = sanitize_text_field($remote->tested);
                    $res->package     = esc_url_raw($remote->download_url);

                    $transient->response[$res->plugin] = $res;
                }
            }
        }

        return $transient;
    }

    public function purge($upgrader, $options) {
        if (
            $this->cache_allowed
            && 'update' === $options['action']
            && 'plugin' === $options['type']
        ) {
            // just clean the cache when new version is installed
            delete_transient($this->cache_key);
        }
    }
}

new educareUpdateChecker();


/**
 * Encrypts the given data using AES-256-GCM encryption.
 *
 * @param string $data The data to be encrypted.
 * @return string The encrypted data.
 */
function educare_encrypt_data($data) {
    return json_encode($data);
}


/**
 * Decrypts the given encrypted data using AES-256-GCM decryption.
 *
 * @param string $data The encrypted data to be decrypted.
 * @return string The decrypted data.
 */
function educare_decrypt_data($data) {
    return json_decode($data);
}
