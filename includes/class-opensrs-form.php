<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Opensrs_Form {
    /**
     * Outputs the OpenSRS form.
     */
    public static function display_form() {
        ?>
        <div id="opensrs-form-container">
            <form id="opensrs-form">
                <?php wp_nonce_field( 'opensrs_form_nonce', 'opensrs_form_nonce_field' ); ?>
                <p>
                    <label for="ssl_duration">SSL Certificate Duration:</label>
                    <input type="text" id="ssl_duration" name="ssl_duration" placeholder="e.g., 1 year" required />
                </p>
                <div id="sans-fields">
                    <p>
                        <label for="sans_0">SAN:</label>
                        <input type="text" id="sans_0" name="sans[]" placeholder="Enter SAN" />
                    </p>
                </div>
                <p>
                    <button type="button" id="add-san">+</button>
                </p>
                <p>
                    <label for="contact">Contact Details:</label>
                    <input type="text" id="contact" name="contact" placeholder="Your contact details" required />
                </p>
                <p>
                    <button type="submit">Submit</button>
                </p>
                <p id="opensrs-loading" style="display:none;">
                    <img src="<?php echo OSRS_PLUGIN_URL; ?>assets/images/spinner.gif" alt="Loading..." />
                </p>
                <p id="opensrs-response"></p>
            </form>
        </div>
        <?php
    }
}
