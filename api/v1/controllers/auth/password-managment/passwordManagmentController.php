<?php namespace API;

require_once dirname(__FILE__) . '/passwordManagmentDB.php';

use \Respect\Validation\Validator as v;

class PasswordManagmentController {

    protected $slimContainer;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
    }
    
    public function requestResetEmail($request, $response, $args) {
        return $this->slimContainer->view->render($response, 200, 'requestResetEmail');
    }
    
    public function validateResetToken($request, $response, $args) {
        return $this->slimContainer->view->render($response, 200, 'validateResetToken');
    }
    
    public function changeUserPassword($request, $response, $args) {
        return $this->slimContainer->view->render($response, 200, 'changeUserPassword');
    }

}