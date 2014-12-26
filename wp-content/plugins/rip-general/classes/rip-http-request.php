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
     * Singleton instace.
     * 
     * @var Object 
     */
    protected static $_instance = null;

    /**
     * Private constructor.
     * Set the request and query object.
     */
    private function __construct() {
        $this->request = new \Rip_General\Classes\Rip_Http_Parameters($_POST);

        $this->query = new \Rip_General\Classes\Rip_Http_Parameters($_GET);
    }

    /**
     * Return the instance class.
     * 
     * @return Class
     */
    public static function get_instance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

}
