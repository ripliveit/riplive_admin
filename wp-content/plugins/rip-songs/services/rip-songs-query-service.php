<?php

namespace Rip_Songs\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Songs_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Chart Dao.
     * 
     * @var Object 
     */
    protected $_songs_dao;

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    protected $_posts_dao;

    /**
     * Holds a reference to Rip_General_Service
     * 
     * @var Object 
     */
    protected $_general_service;

    /**
     * Class constructor.
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $songs_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, \Rip_General\Services\Rip_General_Service $general_service
    ) {
        $this->_songs_dao = $songs_dao;
        $this->_posts_dao = $posts_dao;
        $this->_general_service = $general_service;
    }

    /**
     * Retrieve all songs.
     */
    public function get_all_songs($count = null, $page = null, $divide = null) {
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Songs\Mappers\Rip_Songs_Mapper', $this->_posts_dao
        );

        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $pages = $this->_posts_dao->get_posts_type_number_of_pages('songs', $count);
        $data = $mapper->map($this->_songs_dao->get_all_songs($page_args));

        if ($divide) {
            $data = $this->_general_service->divide_data_by_letter('song_title', $data);
        }

        $message = new \Rip_General\Dto\Message();
        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_charts(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Retrieve all songs with a specific genre.
     */
    public function get_all_songs_by_genre_slug($count = null, $page = null, $divide = null) {
        $message = new \Rip_General\Dto\Message();
        
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
