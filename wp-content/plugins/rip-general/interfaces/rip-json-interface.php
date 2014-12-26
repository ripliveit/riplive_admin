<?php

namespace Rip_General\Interfaces;

/**
 * Define the public methods used to output JSON data.
 */
interface Rip_Json_Interface {

    /**
     * Set the response header.
     * 
     * @param string $type
     */
    public function _set_header($type);

    /**
     * Output data in json format.
     * 
     * @param mixed $data
     */
    public function to_json($data);

    /**
     * Output data in jsonp format.
     * 
     * @param mixed $data
     * @param string $callback
     */
    public function to_jsonp($data, $callback);
}
