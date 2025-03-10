<?php
/* includes/class-opensrs-integration.php - Core Plugin Functionality */
class OpenSRS_Integration {
    private static $instance = null;
    public $features = [
        'contact_form' => [
            'label' => 'Contact Form',
            'options' => [
                'phone' => 'Require Phone Number',
                'gdpr' => 'Enable GDPR Checkbox'
            ]
        ],
        'newsletter' => [
            'label' => 'Newsletter Signup',
            'options' => [
                'double_optin' => 'Enable Double Opt-In'
            ]
        ]
    ];

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Hook into WordPress
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_post_meta']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('wp_ajax_opensrs_submit', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_opensrs_submit', [$this, 'handle_ajax_submission']);
        add_action('wp_footer', [$this, 'render_frontend_form']);
    }

    /**
     * Add OpenSRS settings page to the WordPress admin menu.
     */
    public function add_settings_page() {
        add_options_page(
            'OpenSRS Settings',
            'OpenSRS',
            'manage_options',
            'opensrs-settings',
            [$this, 'render_settings_page']
        );
        register_setting('opensrs_settings', 'opensrs_settings');
    }

    /**
     * Render the OpenSRS settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>OpenSRS Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('opensrs_settings');
                do_settings_sections('opensrs-settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="opensrs_settings[api_key]" value="<?= esc_attr(get_option('opensrs_settings')['api_key'] ?? '') ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Environment</th>
                        <td>
                            <select name="opensrs_settings[environment]">
                                <option value="sandbox" <?= selected(get_option('opensrs_settings')['environment'] ?? '', 'sandbox') ?>>Sandbox</option>
                                <option value="production" <?= selected(get_option('opensrs_settings')['environment'] ?? '', 'production') ?>>Production</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Sidebar Position</th>
                        <td>
                            <select name="opensrs_settings[sidebar_position]">
                                <option value="left" <?= selected(get_option('opensrs_settings')['sidebar_position'] ?? '', 'left') ?>>Left</option>
                                <option value="right" <?= selected(get_option('opensrs_settings')['sidebar_position'] ?? '', 'right') ?>>Right</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Add meta boxes to posts, pages, and products.
     */
    public function add_meta_boxes() {
        foreach (['post', 'page', 'product'] as $post_type) {
            add_meta_box(
                'opensrs-settings',
                'OpenSRS Settings',
                [$this, 'render_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the meta box content.
     */
    public function render_meta_box($post) {
        wp_nonce_field('opensrs_meta_nonce', 'opensrs_meta_nonce');
        $active = get_post_meta($post->ID, '_opensrs_active', true);
        $feature = get_post_meta($post->ID, '_opensrs_feature', true);
        $options = get_post_meta($post->ID, '_opensrs_options', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="opensrs_active" value="1" <?= checked($active, 1) ?>> Enable OpenSRS
            </label>
        </p>
        <p>
            <label for="opensrs_feature">Select Feature:</label>
            <select name="opensrs_feature" id="opensrs_feature">
                <option value="">— Select —</option>
                <?php foreach ($this->features as $key => $feature_data) : ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($feature, $key) ?>><?= esc_html($feature_data['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <div class="opensrs-options">
            <?php foreach ($this->features as $key => $feature_data) : ?>
                <div class="opensrs-option-group" data-feature="<?= esc_attr($key) ?>" style="display: <?= $feature === $key ? 'block' : 'none' ?>;">
                    <?php foreach ($feature_data['options'] as $option_key => $option_label) : ?>
                        <label>
                            <input type="checkbox" name="opensrs_options[]" value="<?= esc_attr($option_key) ?>" <?= checked(in_array($option_key, (array)$options)) ?>> <?= esc_html($option_label) ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Save meta box data.
     */
    public function save_post_meta($post_id) {
        if (!isset($_POST['opensrs_meta_nonce']) || !wp_verify_nonce($_POST['opensrs_meta_nonce'], 'opensrs_meta_nonce')) return;

        update_post_meta($post_id, '_opensrs_active', (int)($_POST['opensrs_active'] ?? 0));
        update_post_meta($post_id, '_opensrs_feature', sanitize_text_field($_POST['opensrs_feature'] ?? ''));
        update_post_meta($post_id, '_opensrs_options', array_map('sanitize_text_field', $_POST['opensrs_options'] ?? []));
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function admin_scripts($hook) {
        if ('post.php' !== $hook) return;
        wp_enqueue_style('opensrs-admin', OPENSRS_PLUGIN_URL . 'assets/css/admin.css');
        wp_enqueue_script('opensrs-admin', OPENSRS_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], null, true);
    }

    /**
     * Enqueue frontend scripts and styles.
     */
    public function frontend_scripts() {
        if (!is_singular()) return;
        wp_enqueue_style('opensrs-frontend', OPENSRS_PLUGIN_URL . 'assets/css/frontend.css');
        wp_enqueue_script('opensrs-frontend', OPENSRS_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], null, true);
        wp_localize_script('opensrs-frontend', 'opensrsData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('opensrs_form_nonce')
        ]);
    }

    /**
     * Handle AJAX form submissions.
     */
    public function handle_ajax_submission() {
        check_ajax_referer('opensrs_form_nonce', 'nonce');

        try {
            $api = new OpenSRS_API();
            $response = $api->submit_form($_POST);
            wp_send_json_success($response);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Render the frontend form in the footer.
     */
    public function render_frontend_form() {
        if (!is_singular()) return;
        $post_id = get_the_ID();
        if (!get_post_meta($post_id, '_opensrs_active', true)) return;

        $form = new OpenSRS_Form($post_id);
        $form->render();
    }
}