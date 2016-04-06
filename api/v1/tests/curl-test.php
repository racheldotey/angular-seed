<?php

$ch = curl_init('http://www.hartwick.edu/');
$response = curl_exec($ch);

echo '<pre>';

echo '<p>CURL Get Info</p>';
print_r(curl_getinfo($ch));

echo '<p>CURL Error</p>';
print_r(curl_error($ch));

echo '<p>CURL Response</p>';
print_r($response);

die;