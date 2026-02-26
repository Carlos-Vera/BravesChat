<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    wp_set_current_user(1);
}

// Emulate admin environment minimally for history
define('WP_ADMIN', true);
$current_page = 'braves-chat-history';

echo "<pre>";
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include('./includes/admin/templates/history.php');
} catch (Throwable $e) {
    echo "\n\nCRITICAL EXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . " on line " . $e->getLine() . "\n";
    print_r($e->getTraceAsString());
}

$err = error_get_last();
if ($err) {
    echo "\n\nLAST ERROR CAUGHT:\n";
    print_r($err);
}
echo "</pre>";
