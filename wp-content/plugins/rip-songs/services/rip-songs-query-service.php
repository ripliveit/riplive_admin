<?php

namespace Rip_Songs\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Songs_Query_Service {

    /**
     * Number of items per page when listing all charts.
     * 
     * @var int 
     */
    protected $_items_per_page;

    /**
     * Holds a reference to Chart Dao.
     * 
     * @var Object 
     */
    protected $_songs_dao;

    /**
     * Holds a reference to Post Dao.
     * 
     * @var type 
     */
    protected $_posts_dao;
    
    /**
     * Holds a reference to the factory mapper.
     * 
     * @var type 
     */
    protected $_factory_mapper;

    /**
     * Class constructor.
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $songs_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, \Rip_General\Mappers\Rip_Factory_Mapper $factory_mapper) {
        $this->_songs_dao = $songs_dao;
        $this->_posts_dao = $posts_dao;
        $this->_factory_mapper = $factory_mapper;
    }

    /**
     * Retrieve all songs.
     */
    public function get_all_songs($count = null, $page = null, $divide = null) {
        $page_args = $this->_posts_dao->get_pagination_args($count, $page);
        $pages     = $this->_posts_dao->get_post_type_number_of_pages('songs', $count);
        $results   = $this->_songs_dao->get_all_songs($page_args);
        
        if ($divide) {
            $general_service = new \Rip_General\Services\Rip_General_Service();
            $results = $general_service->divide_data_by_letter('song_title', $results);
        }

        return (array(
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
