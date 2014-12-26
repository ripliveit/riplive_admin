<?php

namespace Rip_General\Classes;

/**
 * A json utility helper.
 * This is a singleton class.
 */
class Rip_General_Json_Helper {

    /**
     * Accepted content type.
     * 
     * @var array 
     */
    protected $_type = array(
        'application/json',
        'text/javascript'
    );

    /**
     * Hold static singleton instance.
     * 
     * @var object 
     */
    protected static $_instance = null;

    /**
     * Private constructor.
     */
    private function __construct() {}

    /**
     * Return the singleton instance.
     * 
     * @return object
     */
    public static function get_instance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Set the response header.
     * 
     * @param string $type
     */
    protected function _set_header($type) {
        if (in_array($type, $this->_type)) {
            header('Content-Type:' . $type . '; charset=utf-8');
        } else {
            header('Content-Type: application/json; charset=utf-8');
        }
    }

    /**
     * Output data in json format.
     * 
     * @param mixed $data
     */
    public function to_json($data) {
        $this->_set_header('application/json');
        echo json_encode($data);
        exit(0);
    }

    /**
     * Output data in jsonp format.
     * 
     * @param mixed $data
     * @param string $callback
     */
    public function to_jsonp($data, $callback) {
        if (isset($callback) && !empty($callback)) {
            $this->_set_header('text/javascript');
            echo $callback;
            echo '(' . json_encode($data) . ')';
            exit(0);
        }
    }
}