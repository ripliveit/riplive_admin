<?php

namespace Rip_General\Classes;

/**
 * A simple class to handler the HTTP request.
 */
class Rip_Http_Request {

    /**
     * Request object. Holds $_POST parameters.
     * 
     * @var Object 
     */
    public $request;

    /**
     * Query Object. 
     * 
     * @var Object 
     */
    public $query;

    /**
     * On construction
     * set the request and query object.
     */
    public function __construct() {
        $this->request = new \Rip_General\Classes\Rip_Http_Parameters($_POST);
        $this->query = new \Rip_General\Classes\Rip_Http_Parameters($_GET);
    }
}
