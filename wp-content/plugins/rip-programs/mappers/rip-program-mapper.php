<?php

namespace Rip_Programs\Mappers;

/**
 * 
 *
 * @author Gabriele
 */
class Rip_Program_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {

    /**
     * Holds a reference
     * to Podcasts_Dao-
     * 
     * @var Object 
     */
    protected $_podcasts_dao;

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
     * @param \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao
     * @param \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
     */
    public function __construct(
            \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao,
            \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
    ) {
        $this->_podcasts_dao = $podcasts_dao;
        $this->_posts_dao = $posts_dao;
    }

    /**
     * A method that set the Program data.
     * Retrieve the total count of podcasts associated 
     * to a program via the podcasts dao.
     * 
     * @param \WP_Query $query
     * @return array
     */
    public function map(\WP_Query $query) {
        $accumulator = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            $number_of_podcasts = $this->_podcasts_dao->get_podcasts_number_by_program_slug($post->post_name);

            array_push($accumulator, array(
                'id_program' => $post->ID,
                'slug' => $post->post_name,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'status' => $post->post_status,
                'program_title' => get_the_title(),
                'program_content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'program_excpert' => get_the_excerpt(),
                'program_images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
                'program_category' => wp_get_post_terms($post->ID, 'program-category'),
                'program_tags' => wp_get_post_terms($post->ID, 'program-tag'),
                //'program_information' => $this->_posts_dao->get_program_information($post->ID),
                'total_podcasts' => (int) $number_of_podcasts['total_items'],
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
