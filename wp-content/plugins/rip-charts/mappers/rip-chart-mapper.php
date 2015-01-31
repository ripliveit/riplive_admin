<?php

namespace Rip_Charts\Mappers;

/**
 * Description of rip-charts-mapper
 *
 * @author Gabriele
 */
class Rip_Chart_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {
    
    protected $_dao;

    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $dao) {
        $this->_dao = $dao;
    }

    public function map(\WP_Query $query) {
        $accumulator = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($accumulator, array(
                'id_chart' => html_entity_decode($post->ID, ENT_COMPAT, 'UTF-8'),
                'chart_slug' => $post->post_name,
                'chart_title' => get_the_title(),
                'chart_content' => $this->_dao->get_the_content_by_id($post->ID),
                'chart_excerpt' => get_the_excerpt(),
                'chart_genre' => wp_get_post_terms($post->ID, 'chart-genre'),
                'chart_tags' => wp_get_post_terms($post->ID, 'chart-tag'),
                'chart_images' => array(
                    'thumbnail' => $this->_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_dao->get_post_images($post->ID, 'large-landscape'),
                ),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
