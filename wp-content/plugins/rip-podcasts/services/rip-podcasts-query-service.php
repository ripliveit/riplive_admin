<?php

namespace Rip_Podcasts\Services;

/**
 * 
 *
 * @author Gabriele
 */
class Rip_Podcasts_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    private $_podcasts_dao;
    private $_posts_dao;
    private $__authors_query_service;

    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, \Rip_General\Classes\Rip_Abstract_Query_Service $authors_query_service
    ) {
        $this->_podcasts_dao = $podcasts_dao;
        $this->_posts_dao = $posts_dao;
        $this->_authors_query_service = $authors_query_service;

        $this->set_items_per_page(24);
    }

    public function get_podcasts_number_of_pages($slug = null, $count = null) {
        $message = new \Rip_General\Dto\Message();
        $count = $this->validate_items_per_page((int) $count);
        $result = $this->_podcasts_dao->get_podcasts_number_of_pages($slug, $count);

        if (empty($result)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Pages not found');

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_number_of_pages($result);

        return $message;
    }

    public function get_all_podcasts($count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Podcasts\Mappers\Rip_Podcast_Mapper', $this->_posts_dao, $this->_authors_query_service
        );

        $count = $this->validate_items_per_page((int) $count);
        $data = $mapper->map($this->_podcasts_dao->get_all_podcasts($count, $page));
        $pages = $this->_podcasts_dao->get_podcasts_number_of_pages(null, $count);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_podcasts(empty($data) ? array() : $data);

        return $message;
    }

    public function get_all_podcasts_by_program_slug($slug = null, $count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Podcasts\Mappers\Rip_Podcast_Mapper', $this->_posts_dao, $this->_authors_query_service
        );
        
        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a program slug');

            return $message;
        }

        $count = $this->validate_items_per_page((int) $count);
        $data = $mapper->map($this->_podcasts_dao->get_all_podcasts_by_program_slug($slug, $count, $page));
        
        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot found podcasst with program slug ' . $slug);

            return $message;
        }
        
        $pages = $pages = $this->_podcasts_dao->get_podcasts_number_of_pages($slug, $count);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_podcasts(empty($data) ? array() : $data);

        return $message;
    }

    public function get_podcast_by_id($id = null) {
        $message = new \Rip_General\Dto\Message();
        
        if (empty($id)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a podcast id');

            return $message;
        }
        
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Podcasts\Mappers\Rip_Podcast_Mapper', $this->_posts_dao, $this->_authors_query_service
        );
        
        $result = $this->_podcasts_dao->get_podcast_by_id($id);
        
        if (empty($result)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot found podcast with id ' . $id);

            return $message;
        }
        
        $data = $mapper->map(array(
            $result
        ));
      
        $message->set_code(200)
                    ->set_status('ok')
                    ->set_podcast(current($data));
        
        return $message;
    }

}
