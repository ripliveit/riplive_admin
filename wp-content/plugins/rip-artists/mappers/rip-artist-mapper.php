<?php

namespace Rip_Artists\Mappers;

/**
 * Map raw data from the database
 * into a more coherent structure.
 *
 * @author Gabriele
 */
class Rip_Artist_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Wp_Query_Interface {

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
                'id_artist' => $post->ID,
                'artist_slug' => $post->post_name,
                'artist_title' => get_the_title(),
                'artist_content' => $this->_posts_dao->get_the_content_by_id($post->ID),
                'artist_excerpt' => get_the_excerpt(),
                'artist_images' => array(
                    'thumbnail' => $this->_posts_dao->get_post_images($post->ID, 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($post->ID, 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($post->ID, 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($post->ID, 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($post->ID, 'large-landscape'),
                ),
                'artist_genre' => wp_get_post_terms($post->ID, 'artist-genre'),
                'artist_tags' => wp_get_post_terms($post->ID, 'artist-tag'),
                'artist_lineup' => get_post_meta($post->ID, 'artists-lineup', true),
                'artist_foundation' => get_post_meta($post->ID, 'artists-foundation', true),
                'artist_label' => get_post_meta($post->ID, 'artists-label', true),
                'artist_booking' => get_post_meta($post->ID, 'artists-booking', true),
                'artist_press' => get_post_meta($post->ID, 'artists-press', true),
                'artist_email' => get_post_meta($post->ID, 'artists-email', true),
                'artist_telephone' => get_post_meta($post->ID, 'artists-telephone', true),
                'artist_website' => get_post_meta($post->ID, 'artists-website', true),
                'artist_facebook' => get_post_meta($post->ID, 'artists-facebook', true),
                'artist_gplus' => get_post_meta($post->ID, 'artists-gplus', true),
                'artist_twitter' => get_post_meta($post->ID, 'artists-twitter', true),
                'artist_itunes' => get_post_meta($post->ID, 'artists-itunes', true),
                'artist_comune' => get_post_meta($post->ID, 'artists-comune', true),
                'artist_regione' => get_post_meta($post->ID, 'artists-regione', true),
                'artist_provincia' => get_post_meta($post->ID, 'artists-provincia', true),
                'artist_nazione' => get_post_meta($post->ID, 'artists-nazione', true),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $accumulator;
    }

}
