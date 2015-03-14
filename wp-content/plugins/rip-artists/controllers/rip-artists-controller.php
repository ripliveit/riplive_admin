<?php

namespace Rip_Artists\Controllers;

/**
 * Artists Controller
 * Implements methods invoked by ajax request
 * to retrieve artists's data.
 */
class Rip_Artists_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all artists.
     */
    public function get_all_artists() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');
        
        $service = $this->_container['artistsQueryService'];
        $result = $service->get_all_artists($count, $page, $divide);
        
        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all artists within a specific genre.
     */
    public function get_all_artists_by_genre_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $service = $this->_container['artistsQueryService'];
        $result = $service->get_all_artists_by_genre_slug($slug, $count, $page, $divide);
       
        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all artists within a specific tag.
     */
    public function get_all_artists_by_tag_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $service = $this->_container['artistsQueryService'];
        $result = $service->get_all_artists_by_tag_slug($slug, $count, $page, $divide);
        
        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }
    
    /**
     * Retrieve an artist by it's unique identifier.
     */
    public function get_artist_by_slug() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['artistsQueryService'];
        $result = $service->get_artist_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return all artists genre.
     */
    public function get_artists_genres() {
        $service = $this->_container['artistsQueryService'];
        $result = $service->get_artists_genres();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
