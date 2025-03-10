<?php
/* includes/class-opensrs-api.php - API Handler */
class OpenSRS_API {
    public function submit_form($data) {
        $settings = get_option('opensrs_settings');
        $api_url = ($settings['environment'] ?? 'sandbox') === 'production' 
            ? 'https://api.opensrs.com' 
            : 'https://sandbox.opensrs.com';
        
        $response = wp_remote_post($api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . ($settings['api_key'] ?? '')
            ],
            'body' => json_encode($data),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($body['status'] !== 'success') {
            throw new Exception($body['message'] ?? 'Unknown error occurred');
        }

        return $body;
    }
}