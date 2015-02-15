<?php

namespace Rip_Charts\Mappers;

/**
 * Map Chart Custom Post Type data
 */
class Rip_Chart_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {
    
        /**
     * Holds a reference
     * to Post_Dao, used to hydrate
     * some properties that require a query through Wordpress system.
     * 
     * @var Object 
     */
    protected $_posts_dao;
    protected $_posts_dao;
    
    /**
     * On construction
     * set the Post_Dao dependency.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $dao
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $dao) {
        $this->_posts_dao = $dao;
    }
    
    /**
     * Map WP_Query data
     * to a coherent chart structure.
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
                'id_chart' => html_entity_decode($post->ID, ENT_COMPAT, 'UTF-8'),
                'chart_slug' => $post->post_name,
                'chart_title' => get_the_title(),
                'chart_content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'chart_excerpt' => get_the_excerpt(),
                'chart_genre' => wp_get_post_terms($post->ID, 'chart-genre'),
                'chart_tags' => wp_get_post_terms($post->ID, 'chart-tag'),
                'chart_images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
