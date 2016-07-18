<?php namespace API;

class SignupController {

    protected $slimContainer;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
    }
    
    public function login($request, $response, $args) {
        return $this->slimContainer->view->render($response, 200, 'login');
    }

}