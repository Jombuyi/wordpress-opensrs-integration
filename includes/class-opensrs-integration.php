<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Opensrs_Integration {
    private static $instance;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Add settings page under the WordPress Settings menu
        add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Add meta boxes for OpenSRS settings on products, pages (and posts if needed)
        add_action( 'add_meta_boxes', array( $this, 'add_opensrs_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_opensrs_meta_box_data' ) );

        // Enqueue admin assets (if additional styles needed)
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Enqueue frontend assets (CSS, JS)
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // AJAX handler for form submission
        add_action( 'wp_ajax_opensrs_form_submit', array( $this, 'handle_form_submission' ) );
        add_action( 'wp_ajax_nopriv_opensrs_form_submit', array( $this, 'handle_form_submission' ) );
    }

    public static function activate() {
        // Activation tasks (e.g., creating tables if needed) can be added here.
    }

    /* -------------------------------
     * Admin Settings Page: OpenSRS API Settings
     * ------------------------------- */
    public function register_settings_page() {
        add_options_page(
            'OpenSRS API Settings',
            'OpenSRS API Settings',
            'manage_options',
            'opensrs-api-settings',
            array( $this, 'settings_page_html' )
        );
    }

    public function register_settings() {
        register_setting( 'opensrs_api_settings_group', 'opensrs_api_key' );
        register_setting( 'opensrs_api_settings_group', 'opensrs_environment' );

        add_settings_section(
            'opensrs_api_settings_section',
            'OpenSRS API Settings',
            null,
            'opensrs-api-settings'
        );

        add_settings_field(
            'opensrs_api_key',
            'API Key',
            array( $this, 'api_key_field_callback' ),
            'opensrs-api-settings',
            'opensrs_api_settings_section'
        );

        add_settings_field(
            'opensrs_environment',
            'Environment',
            array( $this, 'environment_field_callback' ),
            'opensrs-api-settings',
            'opensrs_api_settings_section'
        );
    }

    public function api_key_field_callback() {
        $api_key = esc_attr( get_option( 'opensrs_api_key', '' ) );
        echo "<input type='text' name='opensrs_api_key' value='$api_key' class='regular-text' />";
    }

    public function environment_field_callback() {
        $env = esc_attr( get_option( 'opensrs_environment', 'sandbox' ) );
        ?>
        <select name="opensrs_environment">
            <option value="sandbox" <?php selected( $env, 'sandbox' ); ?>>Sandbox</option>
            <option value="production" <?php selected( $env, 'production' ); ?>>Production</option>
        </select>
        <?php
    }

    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>OpenSRS API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'opensrs_api_settings_group' );
                do_settings_sections( 'opensrs-api-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /* -------------------------------
     * Meta Box for Page/Product Settings
     * ------------------------------- */
    public function add_opensrs_meta_box() {
        // Add to pages and WooCommerce products (if exists). You can also add to posts if needed.
        $post_types = array('page');
        if ( post_type_exists( 'product' ) ) {
            $post_types[] = 'product';
        }
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'opensrs_meta_box',
                'OpenSRS Form Settings',
                array( $this, 'render_meta_box' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    public function render_meta_box( $post ) {
        // Use a nonce field for security
        wp_nonce_field( 'opensrs_meta_box_nonce_action', 'opensrs_meta_box_nonce' );

        // Retrieve current settings (if any)
        $enable_opensrs = get_post_meta( $post->ID, '_opensrs_enable', true );
        $linked_product = get_post_meta( $post->ID, '_opensrs_linked_product', true );

        // If WooCommerce is active, get list of products for dropdown selection
        $products = array();
        if ( post_type_exists( 'product' ) ) {
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            );
            $product_query = new WP_Query( $args );
            if ( $product_query->have_posts() ) {
                while ( $product_query->have_posts() ) {
                    $product_query->the_post();
                    $products[get_the_ID()] = get_the_title();
                }
                wp_reset_postdata();
            }
        }
        ?>
        <p>
            <label>
                <input type="checkbox" name="opensrs_enable" value="1" <?php checked( $enable_opensrs, '1' ); ?> />
                Enable OpenSRS
            </label>
        </p>
        <p>
            <label for="opensrs_linked_product">Link SSL Product:</label>
            <select name="opensrs_linked_product" id="opensrs_linked_product" style="width:100%;">
                <option value="">-- Select Product --</option>
                <?php foreach ( $products as $id => $title ) : ?>
                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $linked_product, $id ); ?>>
                        <?php echo esc_html( $title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function save_opensrs_meta_box_data( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['opensrs_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['opensrs_meta_box_nonce'], 'opensrs_meta_box_nonce_action' ) ) {
            return;
        }
        // Avoid autosave and permission issues
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Save the checkbox value
        $enable = isset( $_POST['opensrs_enable'] ) ? '1' : '';
        update_post_meta( $post_id, '_opensrs_enable', $enable );

        // Save the linked product selection
        $linked_product = isset( $_POST['opensrs_linked_product'] ) ? sanitize_text_field( $_POST['opensrs_linked_product'] ) : '';
        update_post_meta( $post_id, '_opensrs_linked_product', $linked_product );
    }

    /* -------------------------------
     * Enqueue Assets
     * ------------------------------- */
    public function enqueue_admin_assets( $hook ) {
        // Load only on post edit screens and settings page
        if ( in_array( $hook, array('post.php', 'post-new.php', 'settings_page_opensrs-api-settings') ) ) {
            // Example: enqueue a custom admin CSS file if desired
            wp_enqueue_style( 'opensrs-admin', OSRS_PLUGIN_URL . 'assets/css/admin.css' );
        }
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'opensrs-frontend', OSRS_PLUGIN_URL . 'assets/css/frontend.css' );
        wp_enqueue_script( 'opensrs-frontend', OSRS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), false, true );
        wp_localize_script( 'opensrs-frontend', 'opensrs_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'opensrs_form_nonce' )
        ) );
    }

    /* -------------------------------
     * AJAX Form Submission Handler
     * ------------------------------- */
    public function handle_form_submission() {
        // Verify nonce
        check_ajax_referer( 'opensrs_form_nonce', 'nonce' );

        // Retrieve and sanitize submitted data
        $ssl_duration = isset( $_POST['ssl_duration'] ) ? sanitize_text_field( $_POST['ssl_duration'] ) : '';
        $sans         = isset( $_POST['sans'] ) ? array_map( 'sanitize_text_field', $_POST['sans'] ) : array();
        $contact      = isset( $_POST['contact'] ) ? sanitize_text_field( $_POST['contact'] ) : '';

        // Prepare data for the API
        $data = array(
            'ssl_duration' => $ssl_duration,
            'sans'         => $sans,
            'contact'      => $contact,
        );

        // Process the request using the OpenSRS API class
        $api = new Opensrs_API();
        $response = $api->send_request( $data );

        if ( isset( $response['success'] ) && $response['success'] ) {
            wp_send_json_success( array( 'message' => 'SSL enrollment successful!' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Error processing SSL enrollment.' ) );
        }
    }
}
