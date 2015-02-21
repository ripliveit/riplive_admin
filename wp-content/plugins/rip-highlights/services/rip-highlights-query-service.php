<?php

namespace Rip_Highlights\Services;

/**
 * Description of rip-highlights-query-service
 *
 * @author Gabriele
 */
class Rip_Highlights_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    private $_highlights_dao;

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    protected $_posts_dao;

    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $highlights_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao) {
        $this->_highlights_dao = $highlights_dao;
        $this->_posts_dao = $posts_dao;
    }

    public function get_all_highlights($count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Highlights\Mappers\Rip_Highlight_Mapper', $this->_posts_dao
        );
        
        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $result = $this->_highlights_dao->get_all_highlights($page_args);
        
        
        $pages = $this->_posts_dao->get_post_type_number_of_page('highlights', $count);
        
        $data = $mapper->map($result);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_highlights(empty($data) ? array() : $data);

        return $message;
    }

}
