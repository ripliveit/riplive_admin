<?php

namespace Rip_Highlights\Daos;

/**
 * Data Access Object for Highlights.
 */
class Rip_Highlights_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve all highlights.
     * 
     * @param array $page_args
     * @return \WP_Query
     */
    public function get_all_highlights(array $page_args = array()) {
        $args = array(
            'post_type' => 'highlights',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);
        
        return $query;
    }

}
