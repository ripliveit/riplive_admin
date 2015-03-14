<?php

namespace Rip_Artists\Daos;

/**
 * Data Access object for Artists Custom Post Type.
 */
class Rip_Artists_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Retrieve all artists.
     * 
     * @param int $page
     * @return array
     */
    public function get_all_artists($page_args = null) {
        $args = array(
            'post_type' => 'artists',
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
     * Retrieve all artists with a specific genre's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_artists_by_genre_slug($slug, $page_args = null) {
        $args = array(
            'post_type' => 'artists',
            'post_status' => 'publish',
            'orderby' => 'name',
            'artist-genre' => $slug,
            'order' => 'ASC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve all artists with a specific tag's slug.
     * 
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function get_all_artists_by_tag_slug($slug, $page_args = null) {
        $args = array(
            'post_type' => 'artists',
            'post_status' => 'publish',
            'orderby' => 'name',
            'artist-tag' => $slug,
            'order' => 'ASC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Retrieve a single artist's by it's slug.
     * 
     * @param string $slug
     * @return array
     */
    public function get_artist_by_slug($slug) {
        $args = array(
            'post_type' => 'artists',
            'name' => $slug,
        );

        $query = new \WP_Query($args);

        return $query;
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

        return $query;
    }

}
