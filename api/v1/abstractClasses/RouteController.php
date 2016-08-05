<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

abstract class RouteController {

    protected $slimContainer;
    protected $ApiLogging;

    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
        $this->ApiLogging = $slimContainer->get('ApiLogging');
    }

    public function render($response, $httpCode, $data) {
        return $this->slimContainer->view->render($response, $httpCode, $data);
    }
}