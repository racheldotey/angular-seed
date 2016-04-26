<?php namespace API\Test;

require_once dirname(dirname(__FILE__)) . "/config/config.php";

echo '<pre><code>';

print_r(\API\APIConfig::get());

print_r($_SERVER);