<?php

/**
 * Implements method that return data in Json format
 * stored in:
 * wp_general_comuni, wp_general_province, wp_general_regioni, wp_general_nazioni
 */
class rip_general_ajax_front_controller {

    /**
     * Return all data from wp_general_comuni
     */
    public static function get_comuni() {
        $dao = new rip_general_dao();
        $json_helper = rip_general_json_helper::get_instance();

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
    public static function get_province() {
        $dao = new rip_general_dao();
        $json_helper = rip_general_json_helper::get_instance();

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
    public static function get_regioni() {
        $dao = new rip_general_dao();
        $json_helper = rip_general_json_helper::get_instance();

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
    public static function get_nazioni() {
        $dao = new rip_general_dao();
        $json_helper = rip_general_json_helper::get_instance();

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