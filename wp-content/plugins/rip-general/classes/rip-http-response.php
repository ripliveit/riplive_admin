<?php

namespace Rip_General\Classes;

/**
 * A simple class to handler the HTTP request.
 */
class Rip_Http_Response implements \Rip_General\Interfaces\Rip_Json_Interface {

    /**
     * Http status code.
     * Default is 200 OK.
     * 
     * @var in 
     */
    protected $_code = 200;

    /**
     * All http codes.
     * 
     * @var array 
     */
    protected $_codes = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

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
     * Set the http status code.
     * 
     * @param int $code
     * @return \Rip_General\Classes\Rip_Http_Response
     */
    public function set_code($code) {
        if (array_key_exists((int) $code, $this->_codes)) {
            $this->_code = (int) $code;
            http_response_code($this->_code);
        }

        return $this;
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
