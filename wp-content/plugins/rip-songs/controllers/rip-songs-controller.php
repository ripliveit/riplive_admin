<?php

namespace Rip_Songs\Controllers;

/**
 * Songs ajax front controller.
 * Implements method invoked byt ajax method to retrieve songs's data.
 */
class Rip_Songs_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all songs.
     */
    public function get_all_songs() {
        $dao = new \Rip_Songs\Daos\Rip_Songs_Dao();

        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $results = $dao->set_items_per_page($count)->get_all_songs($page);
        $pages = $dao->get_post_type_number_of_pages('songs');

        if ($divide) {
            $service = new \Rip_General\Services\Rip_General_Service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all songs with a specific genre.
     */
    public function get_all_songs_by_genre_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a genre slug'
            ));
        }

        $dao = new \Rip_Songs\Daos\Rip_Songs_Dao();
        $results = $dao->set_items_per_page($count)->get_all_songs_by_genre_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('songs', array(
            'song-genre' => $slug
        ));

        if ($divide) {
            $service = new \Rip_General\Services\Rip_General_Service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'genre' => get_term_by('slug', $slug, 'song-genre'),
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all songs with a specific tag.
     */
    public function get_all_songs_by_tag_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a tag slug'
            ));
        }

        $dao = new \Rip_Songs\Daos\Rip_Songs_Dao();
        $results = $dao->set_items_per_page($count)->get_all_songs_by_tag_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('songs', array(
            'song-tag' => $slug
        ));

        if ($divide) {
            $service = new \Rip_General\Services\Rip_General_Service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'tag' => get_term_by('slug', $slug, 'song-tag'),
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a song by it's unique identifier.
     */
    public function get_song_by_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a song slug'
            ));
        }

        $dao = new \Rip_Songs\Daos\Rip_Songs_Dao();
        $results = $dao->get_song_by_slug($slug);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'song' => $results
        ));
    }

    /**
     * Return a list of all 
     * taxonomy of custom post type 'Songs'.
     */
    public function get_songs_genres() {
        $dao = new \Rip_Songs\Daos\Rip_Songs_Dao();
        $results = $dao->get_songs_genres();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'genres' => empty($results) ? array() : $results,
        ));
    }

}
