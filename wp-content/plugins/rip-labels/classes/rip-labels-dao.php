<?php

/**
 * Return data from Labels custom post type.
 */
class rip_labels_dao {
    
    /**
     * Private method used to set Label's data.
     * 
     * @param WP_Query $query
     * @return array
     */
    protected function _set_labels_data(WP_Query $query) {
        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            array_push($out, get_the_title());
        }

        wp_reset_query();
        wp_reset_postdata();

        return $out;
    }

    /**
     * Retrieve all Labels posts with 'etichetta' custom term.
     * 
     * @return array.
     */
    public function get_all_labels() {
        $args = array(
            'post_type' => 'labels',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'label-genre',
                    'field' => 'slug',
                    'terms' => 'etichetta'
                ),
            ),
        );

        $query = new WP_Query($args);

        $results = $this->_set_labels_data($query);

        return $results;
    }

    /**
     * Retrieve all Labels posts with 'ufficio-stampa' custom term.
     * 
     * @return array.
     */
    public function get_all_press_offices() {
        $args = array(
            'post_type' => 'labels',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'label-genre',
                    'field' => 'slug',
                    'terms' => 'ufficio-stampa'
                ),
            ),
        );

        $query = new WP_Query($args);

        $results = $this->_set_labels_data($query);

        return $results;
    }

    /**
     * Retrieve all Labels posts with 'booking' custom term.
     * 
     * @return array.
     */
    public function get_all_bookings() {
        $args = array(
            'post_type' => 'labels',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'label-genre',
                    'field' => 'slug',
                    'terms' => 'booking'
                ),
            ),
        );

        $query = new WP_Query($args);

        $results = $this->_set_labels_data($query);

        return $results;
    }

}