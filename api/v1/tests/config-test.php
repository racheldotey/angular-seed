<?php namespace API\Test;

require_once dirname(dirname(__FILE__)) . "/config/config.php";

echo '<pre><code>';

print_r(\API\APIConfig::get());

$host = $_SERVER['HTTP_HOST'];
$addr = $_SERVER['SERVER_ADDR'];
if($host === 'api.seed.dev' || $host === 'localhost' || $addr === '127.0.0.1') {
	// Localhost
	echo 'One vote for localhost';
} else if($host === 'api-dot-triviajoint-qa2.appspot.com' || $addr === '24.235.64.136') {
	// QA on Google Cloud
	echo 'One vote for QA';
} else {
	echo 'We didnt vote';
}
	echo $host;
	echo $addr;
		
print_r($_SERVER);