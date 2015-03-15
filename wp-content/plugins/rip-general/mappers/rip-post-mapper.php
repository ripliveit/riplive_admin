<?php

namespace Rip_General\Mappers;

/**
 * Map raw data from the database
 * into a more coherent structure.
 *
 * @author Gabriele
 */
class Rip_Post_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {

    /**
     * Holds a reference
     * to Post_Dao, used to hydrate
     * some properties that require a query through Wordpress system.
     * 
     * @var Object 
     */
    protected $_posts_dao;

    /**
     * On construction set the dependecies.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $posts_dao) {
        $this->_posts_dao = $posts_dao;
    }

       /**
     * Map a Wp_Query data to
     * an array that contains song's data.
     * 
     * @param WP_Query $query
     * @return array
     */
    public function map(\WP_Query $query) {
        $accumulator = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($accumulator, array(
                'id' => $post->ID,
                'slug' => $post->post_name,
                'title' => get_the_title(),
                'content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'excerpt' => get_the_excerpt(),
                'images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
                'genre' => wp_get_post_terms($post->ID, 'category'),
                'tags' => wp_get_post_terms($post->ID, 'post_tag'),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
