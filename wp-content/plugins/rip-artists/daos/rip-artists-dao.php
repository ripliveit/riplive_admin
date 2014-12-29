<?php

/**
 * Data Access object for Artists Custom Post Type.
 */
class rip_artists_dao extends rip_abstract_dao {

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->_general_service = new rip_general_service();
    }

    /**
     * A private method that set the Song data.
     * 
     * @param WP_Query $query
     * @return array
     */
    protected function _set_artists_data(WP_Query $query) {
        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($out, array(
                'id_artist' => get_the_ID(),
                'artist_slug' => $post->post_name,
                'artist_title' => get_the_title(),
                'artist_content' => $this->get_the_content(),
                'artist_excerpt' => get_the_excerpt(),
                'artist_images' => array(
                    'thumbnail' => $this->get_post_images(get_the_ID(), 'thumbnail'),
                    'image_medium' => $this->get_post_images(get_the_ID(), 'medium'),
                    'image_large' => $this->get_post_images(get_the_ID(), 'large'),
                    'image_full' => $this->get_post_images(get_the_ID(), 'full'),
                    'landscape_medium' => $this->get_post_images(get_the_ID(), 'medium-landscape'),
                    'landscape_large' => $this->get_post_images(get_the_ID(), 'large-landscape'),
                ),
                'artist_genre' => wp_get_post_terms(get_the_ID(), 'artist-genre'),
                'artist_tags' => wp_get_post_terms(get_the_ID(), 'artist-tag'),
                'artist_lineup' => get_post_meta(get_the_ID(), 'artists-lineup', true),
                'artist_foundation' => get_post_meta(get_the_ID(), 'artists-foundation', true),
                'artist_label' => get_post_meta(get_the_ID(), 'artists-label', true),
                'artist_booking' => get_post_meta(get_the_ID(), 'artists-booking', true),
                'artist_press' => get_post_meta(get_the_ID(), 'artists-press', true),
                'artist_email' => get_post_meta(get_the_ID(), 'artists-email', true),
                'artist_telephone' => get_post_meta(get_the_ID(), 'artists-telephone', true),
                'artist_website' => get_post_meta(get_the_ID(), 'artists-website', true),
                'artist_facebook' => get_post_meta(get_the_ID(), 'artists-facebook', true),
                'artist_gplus' => get_post_meta(get_the_ID(), 'artists-gplus', true),
                'artist_twitter' => get_post_meta(get_the_ID(), 'artists-twitter', true),
                'artist_itunes' => get_post_meta(get_the_ID(), 'artists-itunes', true),
                'artist_comune' => get_post_meta(get_the_ID(), 'artists-comune', true),
                'artist_regione' => get_post_meta(get_the_ID(), 'artists-regione', true),
                'artist_provincia' => get_post_meta(get_the_ID(), 'artists-provincia', true),
                'artist_nazione' => get_post_meta(get_the_ID(), 'artists-nazione', true),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $out;
    }

    /**
     * Retrieve all posts from Artist Custom Post Type.
     * 
     * @param int $page
     * @return array
     */
    public function get_all_artists($page = null) {
        $args = array(
            'post_type' => 'artists',
            'post_status' => 'publish',
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_artists_data($query);

        return $results;
    }

    /**
     * Retrieve all posts from Artist Custom Post Type with a specific genre's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_artists_by_genre_slug($slug, $page = null) {
        $args = array(
            'post_type' => 'artists',
            'post_status' => 'publish',
            'orderby' => 'name',
            'artist-genre' => $slug,
            'order' => 'ASC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_artists_data($query);

        return $results;
    }

    /**
     * Retrieve all posts from Artist Custom Post Type with a specific tag's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_artists_by_tag_slug($slug, $page = null) {
        $args = array(
            'post_type' => 'artists',
            'post_status' => 'publish',
            'orderby' => 'name',
            'artist-tag' => $slug,
            'order' => 'ASC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_artists_data($query);

        return $results;
    }

    /**
     * Retrieve a single artist's post by it's slug.
     * 
     * @param int $id
     * @return array
     */
    public function get_artist_by_slug($slug) {
        $args = array(
            'post_type' => 'artists',
            'name' => $slug,
        );

        $query = new WP_Query($args);
        $results = $this->_set_artists_data($query);

        return current($results);
    }

    /**
     * Return all artist-genre custom taxonomy of Brani custom post type.
     * 
     * @return array.
     */
    public function get_artists_genres() {
        $taxonomies = array(
            'artist-genre',
        );

        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
        );

        $query = get_terms($taxonomies, $args);
        $results = $this->_set_genres_data($query);

        return $results;
    }

}