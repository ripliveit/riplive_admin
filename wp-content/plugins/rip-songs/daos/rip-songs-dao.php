<?php

namespace Rip_Songs\Daos;

/**
 * Data Access object for Songs Custom Post Type.
 */
class Rip_Songs_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve all posts from Brani Custom Post Type.
     * 
     * @param array $page_args
     * @return Array
     */
    public function get_all_songs(array $page_args = array()) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);
        
        return $query;
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

        $query = new \WP_Query($args);
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

        $query = new \WP_Query($args);
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

        $query = new \WP_Query($args);
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
