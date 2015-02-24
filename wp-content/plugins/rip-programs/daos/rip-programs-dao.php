<?php

namespace Rip_Programs\Daos;

/**
 * Programs Data Access Object.
 */
class Rip_Programs_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve all programs.
     * 
     * @return array
     */
    public function get_all_programs($page_args = null, $post_status = 'publish') {
        $args = array(
            'post_type' => 'programs',
            'post_status' => $post_status,
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Return a single program by its unique slug.
     * 
     * @param string $slug
     * @return \WP_Query
     */
    public function get_program_by_slug($slug) {
        $args = array(
            'post_type' => 'programs',
            'name' => $slug,
            'post_status' => array(
                'publish',
                'pending',
            ),
        );

        $query = new \WP_Query($args);

        return $query;
    }
}