<?php

namespace Rip_General\Classes;

/**
 * A simple class to handler the HTTP request.
 */
class Rip_Http_Response implements \Rip_General\Interfaces\Rip_Json_Interface {

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
     * Set the response header.
     * 
     * @param string $type
     */
    public function _set_header($type) {
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
