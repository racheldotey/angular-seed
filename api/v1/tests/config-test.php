<?php namespace API\Test;
require_once dirname(dirname(__FILE__)) . "/config/config.php";

echo "<h3>API Config</h3>";
echo '<pre><code>';

print_r(\API\APIConfig::get());
echo "</code></pre>";

$host = $_SERVER['HTTP_HOST'];
$addr = $_SERVER['SERVER_ADDR'];

if ($host === 'api.seed.dev' || $host === 'localhost' || $addr === '127.0.0.1') {
    // Localhost
    echo '<p>One vote for localhost</p>';
} else if ($host === 'api-dot-triviajoint-qa2.appspot.com' || $addr === '24.235.64.136') {
    // QA on Google Cloud
    echo '<p>One vote for QA</p>';
} else {
    echo '<p>We didnt vote</p>';
}

echo "</code></pre><h3>SERVER</h3>";
echo '<pre><code>';

print_r($_SERVER);
