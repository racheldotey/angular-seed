<?php
$params = array(
    'email' => 'test@test.com',
    'firstName' => 'FirstTest',
    'lastName' => 'LastTest',
    'password' => '1234567890passwords',
    'appVersion' => '2',
    'code' => 'gBa4U7UYHX4Q3amRXnxGvH1rKAZsHXTXz31tbWsSTwIXG',
    'authKey' => 'W5fLHehgfHUhmI7x7clD8x1Ki1Gf8oY4uePbs7rHOmZb4',
    'os' => '4',
    'packageCode' => 'com.hotsalsainteractive.browserTrivia'
);

// create curl resource 
$ch = curl_init(); 

// set url 
curl_setopt($ch, CURLOPT_URL, 'https://svcdev.hotsalsainteractive.com/user/registerAPI'); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_POST, true); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

//return the transfer as a string 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

// $output contains the output string 
$response = curl_exec($ch);

echo '<pre>';

echo '<p>Params</p>';
print_r($params);

echo '<p>CURL Get Info</p>';
print_r(curl_getinfo($ch));

echo '<p>CURL Error</p>';
print_r(curl_error($ch));

echo '<p>CURL Response</p>';
print_r($response);

die;