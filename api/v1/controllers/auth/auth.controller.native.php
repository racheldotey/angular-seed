<?php namespace API;
require_once dirname(__FILE__) . '/auth.data.php';
require_once dirname(__FILE__) . '/auth.additionalInfo.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/system-variables/config.data.php';
use \Respect\Validation\Validator as v;
class AuthControllerNative {
    static $maxattempts = 6;
    static $passwordRules = "Passwords must be at least 8 characters long, contain no whitespace, have at least one letter and one number and any of the following !@#$%^&*_+=-.";


    
}
