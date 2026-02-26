<?php
define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
wp_set_current_user(1);

$stats_webhook_url = get_option('braves_chat_stats_webhook_url', '');
$stats_api_key     = get_option('braves_chat_stats_api_key', '');

$response = wp_remote_get($stats_webhook_url, array(
    'headers' => array('x-api-key' => $stats_api_key),
    'timeout' => 15,
));

$out = "NO_DATA";
if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if(is_array($data)) {
        if(count($data) > 0) {
            $first = $data[0];
            $out = "RAW FIRST ELEMENT TYPE: " . gettype($first) . "\n";
            if (isset($first['json'])) {
                $out .= "HAS JSON KEY\n";
                $out .= "JSON TYPE: " . gettype($first['json']) . "\n";
                if(is_array($first['json'])) {
                    $keys = array_keys($first['json']);
                    $out .= "JSON KEYS: " . implode(', ', $keys) . "\n";
                    if(isset($first['json']['session_id'])) {
                        $out .= "SESSION ID TYPE: " . gettype($first['json']['session_id']) . "\n";
                    }
                }
            } else {
                 $out .= "KEYS: " . implode(', ', array_keys($first)) . "\n";
            }
        } else {
            $out = "EMPTY_DATA_ARRAY";
        }
    } else {
        $out = "DATA_NOT_ARRAY_TYPE_" . gettype($data);
    }
} else {
    $out = "WP_ERROR_" . $response->get_error_message();
}
echo $out;
