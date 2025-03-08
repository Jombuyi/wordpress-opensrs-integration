<?php
/**
 * Handles WordPress integration and settings for OpenSRS SSL
 * 
 * @package OpenSRS_Integration
 */
class OpenSRS_Integration {
    const OPTION_KEY = 'opensrs_ssl_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_shortcode('opensrs_ssl_form', [$this, 'render_ssl_form']);
        add_action('admin_post_nopriv_opensrs_ssl_purchase', [$this, 'handle_form_submission']);
        add_action('admin_post_opensrs_ssl_purchase', [$this, 'handle_form_submission']);
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    // Method to get styles
    public function enqueue_styles() {
        $css_path = plugin_dir_url(__FILE__) . '../assets/css/opensrs-form.css';
        $css_version = file_exists(dirname(__FILE__) . '/../assets/css/opensrs-form.css') 
            ? filemtime(dirname(__FILE__) . '/../assets/css/opensrs-form.css')
            : time();

        wp_enqueue_style(
            'opensrs-ssl-form',
            $css_path,
            [],
            $css_version
        );
    }

    public function register_settings_page() {
        add_options_page(
            'OpenSRS SSL Settings',
            'OpenSRS SSL',
            'manage_options',
            'opensrs-ssl-settings',
            [$this, 'settings_page_callback']
        );
    }

    public function settings_page_callback() {
        ?>
        <div class="wrap">
            <h1>OpenSRS SSL Configuration</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_KEY);
                do_settings_sections('opensrs-ssl-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting(
            self::OPTION_KEY,
            self::OPTION_KEY,
            function ($input) {
                $input['api_username'] = sanitize_text_field($input['api_username']);
                $input['api_key'] = sanitize_text_field($input['api_key']);
                $input['environment'] = in_array($input['environment'], ['test_environment', 'production']) 
                    ? $input['environment'] 
                    : 'test_environment';
                return $input;
            }
        );

        add_settings_section(
            'opensrs_main_section',
            'API Configuration',
            function() {
                echo '<p>Configure your OpenSRS API credentials</p>';
            },
            'opensrs-ssl-settings'
        );

        add_settings_field(
            'api_username',
            'OpenSRS Username',
            [$this, 'render_text_field'],
            'opensrs-ssl-settings',
            'opensrs_main_section',
            ['id' => 'api_username']
        );

        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'render_password_field'],
            'opensrs-ssl-settings',
            'opensrs_main_section',
            ['id' => 'api_key']
        );

        add_settings_field(
            'environment',
            'Environment',
            [$this, 'render_environment_field'],
            'opensrs-ssl-settings',
            'opensrs_main_section'
        );
    }

    public function render_text_field($args) {
        $options = get_option(self::OPTION_KEY);
        $value = $options[$args['id']] ?? '';
        echo '<input type="text" name="' . self::OPTION_KEY . '[' . $args['id'] . ']" 
              value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_password_field($args) {
        $options = get_option(self::OPTION_KEY);
        $value = $options[$args['id']] ?? '';
        echo '<input type="password" name="' . self::OPTION_KEY . '[' . $args['id'] . ']" 
              value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_environment_field() {
        $options = get_option(self::OPTION_KEY);
        $value = $options['environment'] ?? 'test_environment';
        ?>
        <select name="<?php echo self::OPTION_KEY; ?>[environment]">
            <option value="test_environment" <?php selected($value, 'test_environment'); ?>>Test Environment</option>
            <option value="production" <?php selected($value, 'production'); ?>>Production</option>
        </select>
        <p class="description">
            Endpoints:<br>
            Test: https://horizon.opensrs.net:55443<br>
            Production: https://rr-n1-tor.opensrs.net:55443
        </p>
        <?php
    }

    public function render_ssl_form() {
        ob_start(); ?>
        <div class="opensrs-ssl-form">
            <?php $this->show_messages(); ?>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <div class="form-section">
                    <h3>Certificate Details</h3>
                    
                    <div class="form-group">
                        <label>CSR (Certificate Signing Request):*</label>
                        <textarea name="csr" rows="8" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Domain Name:*</label>
                        <input type="text" name="domain" required 
                               pattern="^([a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.)+[a-zA-Z]{2,}$">
                    </div>

                    <div class="form-group">
                        <label>Product Type:*</label>
                        <select name="product_type" required>
                            <option value="ssl_standard">Standard SSL</option>
                            <option value="ssl_premium">Premium SSL</option>
                            <option value="ssl_wildcard">Wildcard SSL</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Registration Type:*</label>
                        <select name="reg_type" required>
                            <option value="new">NEW</option>
                            <option value="renew">Renewal</option>
                            <option value="upgrade">Upgrade</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Period (Years):*</label>
                        <input type="number" name="period" min="1" max="3" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Certificate Name:*</label>
                        <input type="text" name="name" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Contact Information</h3>
                    
                    <div class="contact-fields">
                        <?php 
                        $contact_fields = [
                            'first_name' => 'First Name',
                            'last_name' => 'Last Name',
                            'email' => 'Email',
                            'phone' => 'Phone',
                            'address1' => 'Address Line 1',
                            'address2' => 'Address Line 2',
                            'address3' => 'Address Line 3',
                            'city' => 'City',
                            'state' => 'State/Province',
                            'postal_code' => 'Postal Code',
                            'country' => 'Country',
                            'fax' => 'Fax'
                        ];
                        
                        foreach ($contact_fields as $name => $label): ?>
                            <div class="form-group">
                                <label><?php echo $label; ?>:</label>
                                <?php if ($name === 'country'): ?>
                                    <select name="contact[country]" required>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                <?php else: ?>
                                    <input type="<?php echo ($name === 'email') ? 'email' : 'text'; ?>" 
                                           name="contact[<?php echo $name; ?>]" 
                                           <?php echo in_array($name, ['first_name', 'last_name', 'email', 'phone', 'address1', 'city', 'state', 'postal_code', 'country']) ? 'required' : ''; ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php wp_nonce_field('opensrs_ssl_purchase', 'opensrs_nonce'); ?>
                <input type="hidden" name="action" value="opensrs_ssl_purchase">
                <button type="submit" class="button-primary">Submit Order</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private function show_messages() {
        if (!empty($_GET['ssl_msg'])) {
            $class = !empty($_GET['error']) ? 'error' : 'success';
            echo '<div class="notice notice-' . $class . '"><p>' 
                 . esc_html(urldecode($_GET['ssl_msg'])) 
                 . '</p></div>';
        }
    }

    public function handle_form_submission() {
        if (!wp_verify_nonce($_POST['opensrs_nonce'], 'opensrs_ssl_purchase')) {
            $this->redirect_with_message('Security verification failed', true);
        }

        $required = [
            'csr' => 'CSR',
            'domain' => 'Domain',
            'product_type' => 'Product Type',
            'reg_type' => 'Registration Type',
            'period' => 'Period',
            'name' => 'Certificate Name'
        ];

        foreach ($required as $field => $name) {
            if (empty($_POST[$field])) {
                $this->redirect_with_message("$name is required", true);
            }
        }

        if (!preg_match('/-----BEGIN CERTIFICATE REQUEST-----.*-----END CERTIFICATE REQUEST-----/s', $_POST['csr'])) {
            $this->redirect_with_message("Invalid CSR format", true);
        }

        $api_data = [
            'reg_type' => strtoupper(sanitize_text_field($_POST['reg_type'])),
            'product_type' => sanitize_text_field($_POST['product_type']),
            'csr' => sanitize_textarea_field($_POST['csr']),
            'contact_set' => [
                'organization' => [
                    'first_name' => sanitize_text_field($_POST['first_name']),
                    'last_name' => sanitize_text_field($_POST['last_name']),
                    'org_name' => sanitize_text_field($_POST['organization']),
                    'address1' => sanitize_text_field($_POST['address1']),
                    'address2' => sanitize_text_field($_POST['address2'] ?? ''),
                    'city' => sanitize_text_field($_POST['city']),
                    'state' => sanitize_text_field($_POST['state']),
                    'country' => sanitize_text_field($_POST['country']),
                    'postal_code' => sanitize_text_field($_POST['postal_code']),
                    'phone' => sanitize_text_field($_POST['phone']),
                    'fax' => sanitize_text_field($_POST['fax'] ?? ''),
                    'email' => sanitize_email($_POST['email']),
                ]
            ]
        ];

        $options = get_option(self::OPTION_KEY);
        $api = new OpenSRS_API(
            $options['api_username'],
            $options['api_key'],
            $options['environment']
        );

        $response = $api->enroll_ssl($api_data);

        if ($response['success']) {
            $this->redirect_with_message('SSL certificate order submitted successfully!');
        } else {
            $error = $response['error'] ?? 'Order submission failed';
            $this->log_error($error, $api_data);
            $this->redirect_with_message("Error: $error", true);
        }
    }

    private function sanitize_contact($contact) {
        return [
            'first_name' => sanitize_text_field($contact['first_name']),
            'last_name' => sanitize_text_field($contact['last_name']),
            'email' => sanitize_email($contact['email']),
            'phone' => sanitize_text_field($contact['phone']),
            'address1' => sanitize_text_field($contact['address1']),
            'address2' => sanitize_text_field($contact['address2'] ?? ''),
            'address3' => sanitize_text_field($contact['address3'] ?? ''),
            'city' => sanitize_text_field($contact['city']),
            'state' => sanitize_text_field($contact['state']),
            'postal_code' => sanitize_text_field($contact['postal_code']),
            'country' => sanitize_text_field($contact['country']),
            'fax' => sanitize_text_field($contact['fax'] ?? '')
        ];
    }

    private function redirect_with_message($message, $is_error = false) {
        $url = add_query_arg([
            'ssl_msg' => urlencode($message),
            'error' => $is_error ? 1 : 0
        ], wp_get_referer() ?: home_url());
        
        wp_redirect($url);
        exit;
    }

    private function log_error($error, $data) {
        error_log("[OpenSRS SSL] Error: $error - Data: " . print_r($data, true));
    }
}