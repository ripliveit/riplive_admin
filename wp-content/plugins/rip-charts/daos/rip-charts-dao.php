<?php

namespace Rip_Charts\Daos;

/**
 * Data Access object for Charts Custom Post Type.
 */
class Rip_Charts_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve all posts 
     * from Charts Custom Post Type. 
     * 
     * @return array
     */
    public function get_all_charts() {
        $args = array(
            'post_type' => 'charts',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC'
        );

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve a single chart's post by it's slug.
     * 
     * @param string $slug
     * @return array
     */
    public function get_chart_by_slug($slug) {
        $args = array(
            'post_type' => 'charts',
            'name' => $slug,
        );

        $query = new \WP_Query($args);

        return $query;
    }

}
