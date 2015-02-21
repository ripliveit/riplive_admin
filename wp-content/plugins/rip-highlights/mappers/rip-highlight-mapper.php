<?php

namespace Rip_Highlights\Mappers;

/**
 * A concrete mapper
 * for Highlight data.
 *
 * @author Gabriele
 */
class Rip_Highlight_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {

    /**
     * Holds a reference
     * to Post_Dao, used to hydrate
     * some properties that require a query through Wordpress system.
     * 
     * @var Object 
     */
    protected $_posts_dao;

    /**
     * On construction
     * set the Post_Dao dependency.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $dao
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $posts_dao) {
        $this->_posts_dao = $posts_dao;
    }
    
    /**
     * Map wp data to
     * a coherent structure.
     * 
     * @param \WP_Query $query
     * @return array
     */
    public function map(\WP_Query $query) {
        $accumulator = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($accumulator, array(
                'id_highlight' => $post->ID,
                'highlight_slug' => $post->post_name,
                'highlight_title' => get_the_title(),
                'highlight_content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'highlight_excerpt' => get_the_excerpt(),
                'highlight_images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
                'highlight_genre' => wp_get_post_terms($post->ID, 'highlights-genre'),
                'highlight_link' => get_post_meta($post->ID, 'highlights-link', true),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
