<?php namespace API;

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

abstract class RouteController {

    protected $slimContainer;

    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
    }
}