<?php
namespace Rip_General\Classes;

/**
 * A class used to retrieve information
 * from S_POST or $_GET global array.
 */
class Rip_General_Http_Parameters {

    /**
     * Holds a reference to $_POST
     * or $_GET
     * @var type 
     */
    protected $_parameters;

    /*
     * On construction
     * set the parameters variable.
     */

    public function __construct(array $parameters = array()) {
        $this->_parameters = $parameters;
    }

    /**
     * Return all keys from $parameters array.
     * 
     * @return array
     */
    public function keys() {
        return array_keys($this->_parameters);
    }

    /**
     * Return the $parameter array.
     * 
     * @return array
     */
    public function all() {
        return $this->_parameters;
    }

    /**
     * Return the value with a specific key from parameter array.
     * Null otherwise.
     * 
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null) {
        if (!array_key_exists($key, $this->_parameters)) {
            return $default;
        }

        if (empty($this->_parameters[$key])) {
            return $default;
        }

        return $this->_parameters[$key];
    }

}