<div class="opensrs-ssl-form">
    <form id="opensrs-form" method="post">
        <div class="form-group">
            <label for="csr">CSR (Certificate Signing Request):</label>
            <textarea id="csr" name="csr" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="duration">Certificate Duration:</label>
            <select id="duration" name="duration" required>
                <option value="1">1 Year</option>
                <option value="2">2 Years</option>
            </select>
        </div>

        <div class="sans-section">
            <h4><?php _e('Subject Alternative Names (SANs)', 'opensrs-integration'); ?></h4>
            <div id="san-fields">
                <div class="san-field">
                    <input type="text" name="sans[]" placeholder="example.com">
                </div>
            </div>
            <button type="button" id="add-san" class="button">+ Add SAN</button>
        </div>

        <div class="contact-details">
            <h4><?php _e('Contact Information', 'opensrs-integration'); ?></h4>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="tel" name="phone" placeholder="Phone" required>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="button-primary">
                <?php _e('Submit Order', 'opensrs-integration'); ?>
            </button>
            <div class="spinner"></div>
            <div class="response-message"></div>
        </div>
    </form>
</div>