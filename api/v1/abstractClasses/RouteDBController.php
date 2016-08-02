<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

abstract class RouteDBController {

    /*
     * System Database Helper Instance
     */
    protected $DBConn;
    
    /*
     * Database Table Prefix
     */
    protected $prefix;

    public function __construct(\API\ApiDBConn $ApiDBConn) {
        $this->DBConn = $ApiDBConn;
        $this->prefix = $ApiDBConn->prefix();
    }
}