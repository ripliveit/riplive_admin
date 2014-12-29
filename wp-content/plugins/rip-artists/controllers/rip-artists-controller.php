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
        $dao = new \Rip_Artists\Daos\Rip_Artists_Dao();
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $results = $dao->set_items_per_page($count)->get_all_artists($page);
        $pages = $dao->get_post_type_number_of_pages('artists');

        if ($divide) {
            $service = new \Rip_General\Services\Rip_General_Service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all artists within a specific genre.
     */
    public function get_all_artists_by_genre_slug() {
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

        $dao = new \Rip_Artists\Daos\Rip_Artists_Dao();
        $results = $dao->set_items_per_page($count)->get_all_artists_by_genre_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('artists', array(
            'artist-genre' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'genre' => get_term_by('slug', $slug, 'artist-genre'),
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all artists within a specific tag.
     */
    public function get_all_artists_by_tag_slug() {
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

        $dao = new \Rip_Artists\Daos\Rip_Artists_Dao();
        $results = $dao->set_items_per_page($count)->get_all_artists_by_tag_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('artists', array(
            'artist-tag' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'tag' => get_term_by('slug', $slug, 'artist-tag'),
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve an artist by it's unique identifier.
     */
    public function get_artist_by_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a artist slug'
            ));
        }

        $dao = new \Rip_Artists\Daos\Rip_Artists_Dao();
        $results = $dao->get_artist_by_slug($slug);

        if (empty($results)) {
            return $this->_response->set_code(404)->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'artist' => $results
        ));
    }

    /**
     * A list of all taxonomy of custom post type 'Songs'.
     */
    public function get_artists_genres() {
        $dao = new \Rip_Artists\Daos\Rip_Artists_Dao();
        $results = $dao->get_artists_genres();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'genres' => empty($results) ? array() : $results,
        ));
    }

}
