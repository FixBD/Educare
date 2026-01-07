<?php
// ###################################
//  SENSITIVE DATA, DON'T TOUCH ME!!!
// ###################################

class educareLicenseManager {
    private $svr = EDUCARE_SVR.'/wp-json/license/v1';
    private $svr_data = null;
    private $key = null;

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_educare_activate_license', array($this, 'activate_license_ajax'));
        add_action('wp_ajax_educare_deactivate_license', array($this, 'deactivate_license_ajax'));
        add_action('admin_notices', array($this, 'educare_renewal_notice'));
        add_action('wp_ajax_dismiss_educare_renewal_notice', array($this,'dismiss_educare_renewal_notice'));
        add_action('educare_svr_event', array($this, 'educare_update_svr_data'));

        $this->svr_data = educare_get_svr_data();
        $this->key = isset($this->svr_data['key']) ? sanitize_text_field($this->svr_data['key']) : null;
    }

    // License activation function
    public function activate_license($body) {
        $response = wp_remote_post("{$this->svr}/activate", array(
            'body' => $body
        ));

        $retrieve_body = wp_remote_retrieve_body($response);
        $data = json_decode($retrieve_body, true);

        if (is_wp_error($data)) {
            return $data;
        }
        
        if (isset($data['success']) && $data['success']) {
            $svr_data = isset($data['data']) ? $data['data'] : array();
            $this->educare_update_svr_data($svr_data);
            do_action('educare_activation_actions');
            wp_update_plugins();
            
            return $data;
        }

        return $data;
    }

    // License deactivation function
    public function deactivate_license($body) {
        $response = wp_remote_post("{$this->svr}/deactivate", array(
            'body' => $body
        ));

        $retrieve_body = wp_remote_retrieve_body($response);
        $data = json_decode($retrieve_body, true);

        if (is_wp_error($data)) {
            return $data;
        }
        
        if (isset($data['success']) && $data['success']) {
            delete_option('educare_svr_data');
            do_action('educare_activation_actions');
            wp_update_plugins();

            return $data;
        }
        
        return $data;
    }

    // Generate nonce for security
    public function get_nonce() {
        $response = wp_remote_post("{$this->svr}/nonce", ['method' => 'GET']);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data["nonce"])) {
            return sanitize_text_field($data["nonce"]);
        }
    }

    // Register settings and fields in the admin
    public function register_settings() {
        add_settings_section(
            'educare_activation_form',
            '',
            array ($this, 'educare_activation_form'),
            'educare-license-activation-form'
        );
    }

    public function educare_activation_form( $args ) {
        $info = $this->get_users_info();

        $response = wp_remote_post("{$this->svr}/info", array(
            'body' => array(
                'key' => sanitize_text_field($this->key),
                'nonce' => sanitize_text_field($this->get_nonce()),
                'url' => esc_url_raw($info['url']),
                'ip' => sanitize_text_field($info['ip'])
            )
        ));

        $retrieve_body = wp_remote_retrieve_body($response);

        if ($retrieve_body) {
            $retrieve_body =  json_decode($retrieve_body);
        }

        // update data
        $this->educare_update_svr_data($retrieve_body);
        $allowed_html = [
            'div' => [
                'class' => []
            ],
            'input' => [
                'type' => [],
                'name' => [],
                'value' => [],
                'placeholder' => [],
                'class' => [],
                'id' => [],
                'checked' => [],
                'disabled' => [],
                'readonly' => [],
                'maxlength' => [],
                'size' => []
            ],
            'label' => [
                'class' => [],
                'id' => [],
                'for' => []
            ],
            'button' => [
                'class' => [],
                'id' => [],
                'type' => [],
                'name' => []
            ],
        ];

        echo '<div id="status-messege">';
            if (isset($retrieve_body->code) && $retrieve_body->code === 'rest_no_route') {
                echo educare_show_msg(wp_kses_post($retrieve_body->message), false);
            } else {
                if ($retrieve_body) {
                    echo wp_kses_post($retrieve_body->message);
                } else {
                    echo educare_show_msg('Wrong server route!', false);
                }
            }
        echo '</div>';

        if (isset($retrieve_body->data->form_data)) {
            echo wp_kses($retrieve_body->data->form_data, $allowed_html);
        }

        if (current_user_can('manage_options')) {
            if (isset($retrieve_body->form_data)) {
                echo wp_kses($retrieve_body->form_data, $allowed_html);
            }
        } else {
            echo educare_show_msg('Please log in as an administrator to activate or deactivate the license.', 'info');
        }
    }

    // AJAX handler for license activation
    public function activate_license_ajax() {
        $info = $this->get_users_info();

        $body = array(
            'key' => sanitize_text_field($_POST['key']),
            'nonce' => sanitize_text_field($this->get_nonce()),
            'url' => esc_url_raw($info['url']),
            'ip' => sanitize_text_field($info['ip'])
        );

        $status = $this->activate_license($body);
        wp_send_json_success(['body' => $status]);
    }

    // AJAX handler for license deactivation
    public function deactivate_license_ajax() {
        $info = $this->get_users_info();
    
        // Build the body for the API call
        $body = array(
            'key' => sanitize_text_field($_POST['key']),
            'nonce'   => sanitize_text_field($this->get_nonce()),
            'url'         => esc_url_raw($info['url']), // Use esc_url_raw for non-output use
            'ip'          => sanitize_text_field($info['ip']), // IP addresses don't need esc_html here
        );
    
        // Call license deactivation and send response
        $status = $this->deactivate_license($body);
        wp_send_json_success(['body' => $status]);
    }


    // Get site URL and server IP address to validate license
    private function get_users_info() {
        // Get site URL
        $url = home_url();
        // Extract domain name safely
        $domain = parse_url($url, PHP_URL_HOST);
        // Resolve the domain to an IP address
        $ip = gethostbyname($domain);
        // Validate IP address
        $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';

        // Return the sanitized data, we need this details to validate license
        return array(
            'url'    => esc_url_raw($url), // Sanitized site URL
            'ip'     => $ip // Ensure valid IP or empty string
        );
    }


    // Notice
    public function educare_renewal_notice() {
        // Retrieve license data and dismissal timestamp.
        $svr_data = $this->svr_data;

        if (!$svr_data) {
            return;
        }

        $status = isset($svr_data['status']) ? sanitize_text_field($svr_data['status']) : '';
        $expire_date = isset($svr_data['expire_date']) ? sanitize_text_field($svr_data['expire_date']) : '';

        if ($status === 'expired' || (!$expire_date || strtotime($expire_date) < time())) {
            $svr_data['status'] = 'expired';
            update_option('educare_svr_data', educare_encryptData(json_encode($svr_data), 'educare'));
        } else {
            if ($status) {
                do_action('educare_activation_actions');
            }
        }

        if ($expire_date) {
            // Create a DateTime object using the original date
            $expire_msgs_date = new DateTime($expire_date);
            // Reduce 7 days to show msgs earlier
            $expire_msgs_date->modify('-7 days');
            // Format the new date dynamically based on the original date's format
            $expire_msgs_date = $expire_msgs_date->format('Y-m-d');
        } else {
            $expire_msgs_date = '';
        }
        
        $allowed_html = array(
            'strong' => array(), // Allow <strong> tags without attributes
            'a' => array(        // Allow <a> tags with specific attributes
                'href' => array(),
                'target' => array(),
            ),
        );

        $expire_msgs = isset($svr_data['expire_msgs']) ? wp_kses($svr_data['expire_msgs'], $allowed_html) : '';
        $renew_link = isset($svr_data['renew_link']) ? esc_url($svr_data['renew_link']) : '';
        
        $dismissed_until = get_user_meta(get_current_user_id(), 'educare_renewal_notice', true);

        if ($status === 'expired' || (!$expire_date || strtotime($expire_msgs_date) < time()) &&
            (!$dismissed_until || strtotime($dismissed_until) < time())) {
            // Only show the message to administrators.
            if (current_user_can('manage_options')) {
                if (isset($_GET['page']) && $_GET['page'] !== 'educare-license-key') {
                    echo '<div class="educare-container"><div class="educare_post"><div class="notice notice-error is-dismissible" id="educare-license-notice">';
                    echo '<p>';
                    echo sprintf(
                        __($expire_msgs, 'educare'),
                        $renew_link
                    );
                    echo '</p>';
                    echo '</div></div></div>';
                }
            }
        }
    }
    
    public function dismiss_educare_renewal_notice() {
        $dismiss_until = date('Y-m-d H:i:s', strtotime('+6 hours'));
        update_user_meta(get_current_user_id(), 'educare_renewal_notice', $dismiss_until);
        
        $this->educare_update_svr_data();
        wp_send_json_success();
    }


    public function get_info() {
        // get status
        $info = $this->get_users_info();

        $body = array(
            'key' => sanitize_text_field($this->key),
            'nonce' => sanitize_text_field($this->get_nonce()),
            'url' => esc_url_raw($info['url']),
            'ip' => sanitize_text_field($info['ip'])
        );
        
        $response = wp_remote_post("{$this->svr}/info", array(
            'body' => $body
        ));

        $retrieve_body = wp_remote_retrieve_body($response);

        if ($retrieve_body) {
            $retrieve_body =  json_decode($retrieve_body, true);
            unset($retrieve_body['message'], $retrieve_body['success']);
        }

        return $retrieve_body;
    }

    public function educare_update_svr_data($svr_data = null) {
        if ($svr_data === null) {
            $svr_data = $this->get_info();
        }

        // Check if $svr_data is an array, convert it if it's an object
        if (!is_array($svr_data)) {
            $svr_data = (array) $svr_data;
        }

        if (isset($svr_data['expire_msgs'])) {
            unset($svr_data['success'], $svr_data['message'], $svr_data['form_data']);
            $svr_data = educare_encryptData(json_encode($svr_data), 'educare');

            if (get_option('educare_svr_data')) {
                update_option('educare_svr_data', $svr_data);
            } else {
                add_option('educare_svr_data', $svr_data);
            }
        } else {
            if (isset($svr_data['code'])) {
                $code = sanitize_text_field($svr_data['code']);

                if ($code == 'invalid_license') {
                    delete_option('educare_svr_data');
                }
            }
        }
    }
}

// Instantiate the educareLicenseManager class
new educareLicenseManager();



function educare_get_svr_data($key = null) {
    $svr_data = get_option('educare_svr_data');

    if ($svr_data && is_string($svr_data)) {
        $svr_data = json_decode(educare_decryptData($svr_data, 'educare'), true);
        
        if ($key) {
            if (isset($svr_data[$key])) {
                if ($key === 'expire_msgs') {
                    return wp_kses_post($svr_data[$key]);
                } elseif ($key === 'renew_link') {
                    return esc_url($svr_data[$key]);
                } else {
                    return esc_html($svr_data[$key]);
                }
            }
            
            return;
        }

        return $svr_data;
    }
}


add_action('educare_activation_actions', 'educare_do_activation_actions');

function educare_do_activation_actions() {
    wp_update_plugins();

    // Include necessary WordPress files for plugin info
    if ( ! function_exists( 'plugins_api' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    }

    // Include necessary WordPress files for updates
    if (!class_exists('Automatic_Upgrader_Skin')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    }

    $args = array(
        'slug'   => 'educare', // Educare plugin's slug
        'fields' => array(
            'sections' => false, // Optional: Limit the response fields
        ),
    );
      
    // Fetch plugin information from WordPress.org
    $api = plugins_api( 'plugin_information', $args );

    if (is_wp_error($api)) {
        return; // Exit if plugin info cannot be retrieved
    }

    $skin = new Automatic_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result = $upgrader->upgrade(EDUCARE_FILE);

    if (is_wp_error($result)) {
        // Log the error or handle it appropriately
        error_log('Educare plugin upgrade failed: ' . $result->get_error_message());
        return;
    }

    // Activate the plugin if it's not already active
    if (!is_plugin_active(EDUCARE_FILE)) {
        $activation_result = activate_plugin(EDUCARE_FILE);

        if (is_wp_error($activation_result)) {
            // Log activation error
            error_log('Educare plugin activation failed: ' . $activation_result->get_error_message());
        }
    }
}

