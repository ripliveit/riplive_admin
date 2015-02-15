<?php

namespace Rip_General\Controllers;

/**
 * General Controller
 * implements method that return data in JSON format.
 * 
 */
class Rip_General_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {
    
    /**
     * On construction set the container
     * as the main dependencies.
     * 
     * @param \Rip_General\Classes\Rip_Http_Request $request
     * @param \Rip_General\Classes\Rip_Http_Response $response
     */
    public function __construct(\Rip_General\Classes\Rip_Http_Request $request, \Rip_General\Classes\Rip_Http_Response $response) {
        parent::__construct($request, $response);
        $this->_container = new \Rip_General\Services\Rip_General_Container();
    }

    /**
     * Return a list
     * of all italian cities.
     */
    public function get_comuni() {
        $service = $this->_container['generalQueryService'];
        $result = $service->get_comuni();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list
     * of all italian provinces.
     */
    public function get_province() {
        $service = $this->_container['generalQueryService'];
        $result = $service->get_province();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list
     * of all italian regions.
     */
    public function get_regioni() {
        $service = $this->_container['generalQueryService'];
        $result = $service->get_regioni();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list
     * of all words nations.
     */
    public function get_nazioni() {
        $service = $this->_container['generalQueryService'];
        $result = $service->get_nazioni();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
