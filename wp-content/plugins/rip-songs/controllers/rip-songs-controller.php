<?php

namespace Rip_Songs\Controllers;

/**
 * Songs Controller.
 * Each method, publicly available, return data in JSON format.
 */
class Rip_Songs_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all songs.
     */
    public function get_all_songs() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $service = $this->_container['songsQueryService'];
        $result = $service->get_all_songs($count, $page, $divide);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all songs with a specific genre.
     */
    public function get_all_songs_by_genre_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $service = $this->_container['songsQueryService'];
        $result = $service->get_all_songs_by_genre_slug($slug, $count, $page, $divide);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all songs with a specific tag.
     */
    public function get_all_songs_by_tag_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $service = $this->_container['songsQueryService'];
        $result = $service->get_all_songs_by_tag_slug($slug, $count, $page, $divide);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve a song by it's unique identifier.
     */
    public function get_song_by_slug() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['songsQueryService'];
        $result = $service->get_song_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list of all 
     * taxonomy of custom post type 'Songs'.
     */
    public function get_songs_genres() {
        $service = $this->_container['songsQueryService'];
        $result = $service->get_songs_genres();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
