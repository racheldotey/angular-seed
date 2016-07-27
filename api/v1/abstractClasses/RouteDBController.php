<?php namespace API;


abstract class RouteDBController {

    protected $DBConn;
    
    protected $prefix;

    public function __construct(\API\ApiDBConn $DBConn) {
        $this->DBConn = $DBConn;
        $this->prefix = $this->DBConn->prefix();
    }
}