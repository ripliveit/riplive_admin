<?php

/**
 * Data Access object for Songs Custom Post Type.
 */
class rip_songs_dao extends rip_abstract_dao {

    /**
     * A private method that set the Song data.
     * 
     * @param WP_Query $query
     * @return array
     */
    protected function _set_songs_data(WP_Query $query) {
        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($out, array(
                'id_song' => get_the_ID(),
                'song_slug' => $post->post_name,
                'song_title' => get_the_title(),
                'song_content' => $this->get_the_content(),
                'song_excerpt' => get_the_excerpt(),
                'song_images' => array(
                    'thumbnail' => $this->get_post_images(get_the_ID(), 'thumbnail'),
                    'image_medium' => $this->get_post_images(get_the_ID(), 'medium'),
                    'image_large' => $this->get_post_images(get_the_ID(), 'large'),
                    'image_full' => $this->get_post_images(get_the_ID(), 'full'),
                    'landscape_medium' => $this->get_post_images(get_the_ID(), 'medium-landscape'),
                    'landscape_large' => $this->get_post_images(get_the_ID(), 'large-landscape'),
                ),
                'song_genre' => wp_get_post_terms(get_the_ID(), 'song-genre'),
                'song_tags' => wp_get_post_terms(get_the_ID(), 'song-tag'),
                'song_artist' => get_post_meta(get_the_ID(), 'songs-artist', true),
                'song_album' => get_post_meta(get_the_ID(), 'songs-album', true),
                'song_year' => get_post_meta(get_the_ID(), 'songs-year', true),
                'url_spotify' => get_post_meta(get_the_ID(), 'songs-spotify', true),
                'url_youtube' => get_post_meta(get_the_ID(), 'songs-Youtube', true)
            ));
        }

        wp_reset_query();
        wp_reset_postdata();
        
        return $out;
    }

    /**
     * Retrieve all posts from Brani Custom Post Type.
     * 
     * @param int $page
     * @return array
     */
    public function get_all_songs($page = null) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'name',
            'order' => 'ASC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_songs_data($query);

        return $results;
    }

    /**
     * Retrieve all posts from Brani Custom Post Type with a specific genre's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_songs_by_genre_slug($slug, $page = null) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'name',
            'song-genre' => $slug,
            'order' => 'ASC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_songs_data($query);

        return $results;
    }

    /**
     * Retrieve all posts from Brani Custom Post Type with a specific tag's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_songs_by_tag_slug($slug, $page = null) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'name',
            'song-tag' => $slug,
            'order' => 'ASC'
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new WP_Query($args);
        $results = $this->_set_songs_data($query);

        return $results;
    }

    /**
     * Retrieve a single songs's post by it's slug.
     * 
     * @param int $id
     * @return array
     */
    public function get_song_by_slug($slug) {
        $args = array(
            'post_type' => 'songs',
            'name' => $slug,
        );

        $query = new WP_Query($args);
        $results = $this->_set_songs_data($query);

        return current($results);
    }

    /**
     * Return all song-genre custom taxonomy of Brani custom post type.
     * 
     * @return array.
     */
    public function get_songs_genres() {
        $taxonomies = array(
            'song-genre',
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