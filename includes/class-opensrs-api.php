<?php
class OpenSRS_API {
    private $api_username;
    private $api_key;
    private $environment;

    public function __construct($api_username, $api_key, $environment) {
        $this->api_username = $api_username;
        $this->api_key = $api_key;
        $this->environment = $environment;
    }

    public function enroll_ssl($data) {
        $endpoint = $this->get_api_endpoint();
        $xml_content = $this->build_xml($data);
        $signature = $this->generate_signature($xml_content);

        // Set required headers
        $headers = [
            'Content-Type' => 'text/xml',
            'X-Username' => $this->api_username,
            'X-Signature' => $signature,
        ];

        $args = [
            'body' => $xml_content,
            'headers' => $headers,
            'timeout' => 30,
            'sslverify' => false, // Disable SSL verification temporarily
            'blocking' => true
        ];

        // Force cURL transport
        add_filter('http_api_transports', function() {
            return ['curl'];
        });

        $response = wp_remote_post($endpoint, $args);
        
        return $this->handle_response($response);
    }

    /**
     * Generate API signature
     * @param string $xml_content XML request body
     * @return string MD5 signature
     */
    private function generate_signature($xml_content) {
        $first_hash = md5($xml_content . $this->api_key);
        return md5($first_hash . $this->api_key);
    }

    private function get_api_endpoint() {
        return ($this->environment === 'test_environment') 
            ? 'https://horizon.opensrs.net:55443'
            : 'https://rr-n1-tor.opensrs.net:55443';
    }

    private function build_xml($data) {
        // XML structure
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE OPS_envelope SYSTEM "ops.dtd">
<OPS_envelope>
  <header>
    <version>0.9</version>
  </header>
  <body>
    <data_block>
      <dt_assoc>
        <item key="protocol">XCP</item>
        <item key="action">sw_register</item>
        <item key="object">trust_service</item>
        <item key="attributes">
          <dt_assoc>' . $this->build_attributes($data) . '</dt_assoc>
        </item>
      </dt_assoc>
    </data_block>
  </body>
</OPS_envelope>';
    }

    private function build_attributes($data) {
        $xml = '';
        foreach ($data as $key => $value) {
            if ($key === 'contact_set') {
                $xml .= '<item key="contact_set"><dt_assoc>';
                foreach ($value as $contact_key => $contact_value) {
                    $xml .= "<item key=\"$contact_key\">$contact_value</item>";
                }
                $xml .= '</dt_assoc></item>';
            } else {
                $xml .= "<item key=\"$key\">$value</item>";
            }
        }
        return $xml;
    }

    private function handle_response($response) {
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        return [
            'success' => $status_code === 200,
            'status_code' => $status_code,
            'response' => $body
        ];
    }
}