<?php

namespace Rip_Charts\Mappers;

/**
 * Map raw data from the database
 * into a more coherent structure.
 *
 * @author Gabriele
 */
class Rip_Songs_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {

    protected $_posts_dao;

    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $posts_dao) {
        $this->_posts_dao = $posts_dao;
    }

    /**
     * A private method that set songs data.
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
                'id_song' => $post->ID,
                'song_slug' => $post->post_name,
                'song_title' => get_the_title(),
                'song_content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'song_excerpt' => get_the_excerpt(),
                'song_images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
                'song_genre' => wp_get_post_terms($post->ID, 'song-genre'),
                'song_tags' => wp_get_post_terms($post->ID, 'song-tag'),
                'song_artist' => get_post_meta($post->ID, 'songs-artist', true),
                'song_album' => get_post_meta($post->ID, 'songs-album', true),
                'song_year' => get_post_meta($post->ID, 'songs-year', true),
                'url_spotify' => get_post_meta($post->ID, 'songs-spotify', true),
                'url_youtube' => get_post_meta($post->ID, 'songs-Youtube', true)
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
