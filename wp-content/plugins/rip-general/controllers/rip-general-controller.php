<?php

namespace Rip_General\Controllers;

/**
 * Implements method that return data in Json format.
 * 
 */
class Rip_General_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return all data from wp_general_comuni.
     */
    public function get_comuni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $results = $dao->get_comuni();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_province.
     */
    public function get_province() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $results = $dao->get_province();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_regioni.
     */
    public function get_regioni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $results = $dao->get_regioni();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_nazioni.
     */
    public function get_nazioni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $results = $dao->get_nazioni();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

}
