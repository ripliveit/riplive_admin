<?php

namespace Rip_Songs\Daos;

/**
 * Data Access Object for Songs Custom Post Type.
 */
class Rip_Songs_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve songs.
     * 
     * @param array $page_args
     * @return Object
     */
    public function get_all_songs(array $page_args = array()) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve all songs with a specific genre's slug.
     * 
     * @param string $slug
     * @param array $page_args
     * @return Object
     */
    public function get_all_songs_by_genre_slug($slug, array $page_args = array()) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'title',
            'song-genre' => $slug,
            'order' => 'ASC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve all songs with a specific tag's slug.
     * 
     * @param string $slug
     * @param array $page_args
     * @return Object
     */
    public function get_all_songs_by_tag_slug($slug, array $page_args = array()) {
        $args = array(
            'post_type' => 'songs',
            'post_status' => 'publish',
            'orderby' => 'title',
            'song-tag' => $slug,
            'order' => 'ASC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve a single songs's post by it's slug
     * 
     * @param string $slug
     * @return Object
     */
    public function get_song_by_slug($slug) {
        $args = array(
            'post_type' => 'songs',
            'name' => $slug,
        );

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Return all song-genre custom taxonomy.
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

        return $query;
    }

}
