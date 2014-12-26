<?php
namespace Rip_General\Controllers;

/**
 * Implements method that return data in Json format
 * stored in:
 * wp_general_comuni, wp_general_province, wp_general_regioni, wp_general_nazioni
 */
class Rip_General_Front_Controller {

    /**
     * Return all data from wp_general_comuni
     */
    public function get_comuni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $json_helper = \Rip_General\Classes\Rip_General_Json_Helper::get_instance();

        $results = $dao->get_comuni();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_province
     */
    public function get_province() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $json_helper = \Rip_General\Classes\Rip_General_Json_Helper::get_instance();

        $results = $dao->get_province();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_regioni
     */
    public function get_regioni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $json_helper = \Rip_General\Classes\Rip_General_Json_Helper::get_instance();

        $results = $dao->get_regioni();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all data from wp_general_nazioni
     */
    public function get_nazioni() {
        $dao = new \Rip_General\Daos\Rip_General_Dao();
        $json_helper = \Rip_General\Classes\Rip_General_Json_Helper::get_instance();

        $results = $dao->get_nazioni();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

}