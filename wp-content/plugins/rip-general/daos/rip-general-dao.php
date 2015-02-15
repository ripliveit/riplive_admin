<?php

namespace Rip_General\Daos;

/**
 * Generic plugin DAO.
 */
class Rip_General_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Return all data from wp_general_comuni.
     * 
     * @return array
     */
    public function get_comuni() {
        $wpdb = $this->get_db();

        $sql = "SELECT comune AS value
                FROM wp_general_comuni";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Return all data from wp_general_province.
     * 
     * @return array
     */
    public function get_province() {
        $wpdb = $this->get_db();

        $sql = "SELECT provincia AS value
                FROM wp_general_province";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Return all data from wp_general_regioni.
     * 
     * @return array
     */
    public function get_regioni() {
        $wpdb = $this->get_db();

        $sql = "SELECT regione AS value
                FROM wp_general_regioni
                ORDER BY regione ASC";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Return all data from wp_general_nazioni.
     * 
     * @return array
     */
    public function get_nazioni() {
        $wpdb = $this->get_db();

        $sql = "SELECT name AS value
                FROM wp_general_nazioni";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

}
