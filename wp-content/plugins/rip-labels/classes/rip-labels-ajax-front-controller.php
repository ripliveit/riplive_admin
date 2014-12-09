<?php

/**
 * Return data in Json format from Labels custom post type.
 */
class rip_labels_ajax_front_controller {

    /**
     * Return all Labels custom post type where custom taxonomy 'label-genre'
     * is equal to 'etichetta'
     */
    public static function get_all_labels() {
        $dao = new rip_labels_dao();
        $json_helper = rip_general_json_helper::get_istance();

        $results = $dao->get_all_labels();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all Labels custom post type where custom taxonomy 'label-genre'
     * is equal to 'ufficio-stampa'
     */
    public static function get_all_press_offices() {
        $dao = new rip_labels_dao();
        $json_helper = rip_general_json_helper::get_istance();

        $results = $dao->get_all_press_offices();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return all Labels custom post type where custom taxonomy 'label-genre'
     * is equal to 'booking'
     */
    public static function get_all_bookings() {
        $dao = new rip_labels_dao();
        $json_helper = rip_general_json_helper::get_istance();

        $results = $dao->get_all_bookings();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'items' => empty($results) ? array() : $results,
        ));
    }

}