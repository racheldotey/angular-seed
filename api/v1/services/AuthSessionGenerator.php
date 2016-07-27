<?php namespace \API;

use \Respect\Validation\Validator as v;

class AuthSessionGenerator {

    private $ApiConfig;

    private $ApiLogging;

    public function __construct($ApiConfig, $ApiLogging) {
        $this->ApiConfig = $ApiConfig;
        $this->ApiLogging = $ApiLogging;
    }
    
}