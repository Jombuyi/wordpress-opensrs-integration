<?php
/* includes/class-opensrs-form.php - Form Renderer */
class OpenSRS_Form {
    private $post_id;
    private $settings;

    public function __construct($post_id) {
        $this->post_id = $post_id;
        $this->settings = get_option('opensrs_settings');
    }

    /**
     * Render the form based on the selected feature and options.
     */
    public function render() {
        $feature = get_post_meta($this->post_id, '_opensrs_feature', true);
        $options = get_post_meta($this->post_id, '_opensrs_options', true);
        $position = $this->settings['sidebar_position'] ?? 'right';

        // Only render if a feature is selected
        if (empty($feature)) return;

        ?>
        <div class="opensrs-sidebar opensrs-<?= esc_attr($position) ?>">
            <form class="opensrs-form">
                <?php $this->render_fields($feature, $options); ?>
                <div class="form-response"></div>
                <button type="submit">Submit</button>
                <div class="spinner">
                    <img src="<?= esc_url(OPENSRS_PLUGIN_URL . 'assets/images/spinner.gif') ?>" alt="Loading">
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render form fields based on the selected feature and options.
     *
     * @param string $feature The selected feature (e.g., 'contact_form', 'newsletter').
     * @param array $options The selected options for the feature.
     */
    private function render_fields($feature, $options) {
        switch ($feature) {
            case 'contact_form':
                ?>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <?php if (in_array('phone', $options)) : ?>
                    <input type="tel" name="phone" placeholder="Phone" required>
                <?php endif; ?>
                <?php if (in_array('gdpr', $options)) : ?>
                    <label><input type="checkbox" name="gdpr" required> GDPR Consent</label>
                <?php endif; ?>
                <?php
                break;

            case 'newsletter':
                ?>
                <input type="email" name="email" placeholder="Email" required>
                <?php if (in_array('double_optin', $options)) : ?>
                    <input type="hidden" name="double_optin" value="1">
                <?php endif; ?>
                <?php
                break;

            default:
                echo '<p>No form available for this feature.</p>';
                break;
        }
    }
}