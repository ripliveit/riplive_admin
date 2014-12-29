<?php

namespace Rip_Highlights\Daos;

/**
 * Data Access Object for Highlights Custom Post Type.
 */
class Rip_Highlights_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * A private method that set the Song data.
     * 
     * @param WP_Query $query
     * @return array
     */
    protected function _set_highlights_data(\WP_Query $query) {
        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($out, array(
                'id_highlight' => get_the_ID(),
                'highlight_slug' => $post->post_name,
                'highlight_title' => get_the_title(),
                'highlight_content' => $this->get_the_content(),
                'highlight_excerpt' => get_the_excerpt(),
                'highlight_images' => array(
                    'thumbnail' => $this->get_post_images(get_the_ID(), 'thumbnail'),
                    'image_medium' => $this->get_post_images(get_the_ID(), 'medium'),
                    'image_large' => $this->get_post_images(get_the_ID(), 'large'),
                    'image_full' => $this->get_post_images(get_the_ID(), 'full'),
                    'landscape_medium' => $this->get_post_images(get_the_ID(), 'medium-landscape'),
                    'landscape_large' => $this->get_post_images(get_the_ID(), 'large-landscape'),
                ),
                'highlight_genre' => wp_get_post_terms(get_the_ID(), 'highlights-genre'),
                'highlight_link' => get_post_meta(get_the_ID(), 'highlights-link', true),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $out;
    }

    /**
     * Retrieve all highlights.
     * 
     * @param int $page
     * @return array
     */
    public function get_all_highlights($page = null) {
        $args = array(
            'post_type' => 'highlights',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new \WP_Query($args);
        $results = $this->_set_highlights_data($query);

        return $results;
    }

}
