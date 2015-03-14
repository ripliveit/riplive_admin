<?php

namespace Rip_Artists\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Artists_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Chart Dao.
     * 
     * @var Object 
     */
    private $_artists_dao;

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * Holds a reference to Rip_General_Service
     * 
     * @var Object 
     */
    private $_general_service;

    /**
     * Class constructor.
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $artists_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, \Rip_General\Services\Rip_General_Service $general_service
    ) {
        $this->_artists_dao = $artists_dao;
        $this->_posts_dao = $posts_dao;
        $this->_general_service = $general_service;
    }

    /**
     * Retrieve all artists.
     */
    public function get_all_artists($count = null, $page = null, $divide = null) {
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Artists\Mappers\Rip_Artist_Mapper', $this->_posts_dao
        );

        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $pages = $this->_posts_dao->get_post_type_number_of_page('artists', $count);

        $data = $mapper->map($this->_artists_dao->get_all_artists($page_args));

        if ($divide) {
            $data = $this->_general_service->divide_data_by_letter('artist_title', $data);
        }

        $message = new \Rip_General\Dto\Message();
        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_artists(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Retrieve all artists within a specific genre.
     */
    public function get_all_artists_by_genre_slug($slug, $count = null, $page = null, $divide = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a genre slug');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Artists\Mappers\Rip_Artist_Mapper', $this->_posts_dao
        );

        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $pages = $this->_posts_dao->get_post_type_number_of_page('artists', $count, array(
            'artist-genre' => $slug
        ));

        $data = $mapper->map($this->_artists_dao->get_all_artists_by_genre_slug($slug, $page_args));

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find artists with genre ' . $slug);

            return $message;
        }

        if ($divide) {
            $data = $this->_general_service->divide_data_by_letter('artist_title', $data);
        }

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_genre(get_term_by('slug', $slug, 'artist-genre'))
                ->set_artists(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Retrieve all artists within a specific tag.
     */
    public function get_all_artists_by_tag_slug($slug, $count = null, $page = null, $divide = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a tag slug');

            return $message;
        }
        
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Artists\Mappers\Rip_Artist_Mapper', $this->_posts_dao
        );

        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $pages = $this->_posts_dao->get_post_type_number_of_page('artists', $count, array(
            'artist-tag' => $slug
        ));

        $data = $mapper->map($this->_artists_dao->get_all_artists_by_tag_slug($slug, $page_args));
        
        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find artists with tag ' . $slug);

            return $message;
        }

        if ($divide) {
            $data = $this->_general_service->divide_data_by_letter('artist_title', $data);
        }

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_tag(get_term_by('slug', $slug, 'artist-tag'))
                ->set_artists(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Retrieve an artist by it's unique identifier.
     */
    public function get_artist_by_slug($slug) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify an artist slug');

            return $message;
        }
        
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Artists\Mappers\Rip_Artist_Mapper', $this->_posts_dao
        );
        
        $data = $mapper->map($this->_artists_dao->get_artist_by_slug($slug));

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find an artist with slug ' . $slug);

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_artist(current($data));

        return $message;
    }

    /**
     * Return all artists genres
     */
    public function get_artists_genres() {
        $message = new \Rip_General\Dto\Message();
        
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_Genre_Mapper', $this->_posts_dao
        );
        
        $data = $mapper->map($this->_artists_dao->get_artists_genres());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total(count($data))
                ->set_pages(1)
                ->set_genres(empty($data) ? array() : $data);

        return $message;
    }

}
