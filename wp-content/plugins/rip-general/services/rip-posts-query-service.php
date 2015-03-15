<?php

namespace Rip_General\Services;

/**
 *
 * @author Gabriele
 */
class Rip_Posts_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * Class constructor.
     */
    public function __construct(
        \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
    ) {
        $this->_posts_dao = $posts_dao;
    }

    /**
     * Retrieve all posts.
     */
    public function get_all_posts($count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_Post_Mapper', $this->_posts_dao
        );

        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $pages = $this->_posts_dao->get_post_type_number_of_page('post', $count);
        $data = $mapper->map($this->_posts_dao->get_all_posts($page_args));

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_posts(empty($data) ? array() : $data);

        return $message;
    }

}
