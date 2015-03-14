<?php

namespace Rip_General\Classes;

/**
 * Abstract Data Access Object.
 * Implements method that all DAO inherits.
 */
class Rip_Abstract_Dao {

    /**
     * Holds a reference to the database.
     * 
     * @var Object 
     */
    private $_db = null;


    /**
     * Return the global instance of wpdb.
     * 
     * @global Object $wpdb
     * @return Object
     */
    protected function get_db() {
        global $wpdb;

        if ($this->_db === null) {
            return $this->_db = $wpdb;
        }

        return $this->_db;
    }

    /**
     * A method that retun a formatted array
     * with genre's data.
     * 
     * @param array $genres
     * @return boolean|array
     */
//    protected function _set_genres_data(array $genres = array()) {
//        if (empty($genres)) {
//            return false;
//        }
//
//        $out = array();
//
//        foreach ($genres as $item) {
//            array_push($out, array(
//                'id' => $item->term_id,
//                'slug' => $item->slug,
//                'name' => $item->name,
//                'count' => $item->count,
//            ));
//        }
//
//        return $out;
//    }
}
