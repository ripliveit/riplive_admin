<?php

namespace Rip_General\Classes;

/**
 * Description of rip-abstract-query-service
 *
 * @author Gabriele
 */
class Rip_Abstract_Query_Service implements \Rip_General\Interfaces\Rip_Paginator_Interface {

    /**
     * Number of items per page.
     * 
     * @var int 
     */
    private $_items_per_page;

    /**
     * Return the total number of 
     * items per page of the query service.
     * 
     * @return int
     */
    public function get_items_per_page() {
        return $this->_items_per_page;
    }

    /**
     * Set the number of items per page to retrieve.
     * 
     * @param int $count
     * @return \rip_songs_dao
     */
    public function set_items_per_page($count = null) {
        if ($count && is_int($count)) {
            $this->_items_per_page = (int) $count;
        }

        return $this;
    }
    
    /**
     * If valid return
     * the choosen number, othewise the default
     * number of items per page.
     * 
     * @param int $count
     * @return int
     */
    public function validate_items_per_page($count = null) {
        if ($count && is_int($count)) {
            return (int) $count;
        }

        return $this->_items_per_page;
    }
    
    /**
     * Return an array of 
     * pagination arguments ready to be use by a WP_Query
     * 
     * @param int $count
     * @param int $page
     * @return array
     */
    public function get_wpquery_pagination_args($count = null, $page = null) {
        $args = array();
        
        if ($count) {
            $args['posts_per_page'] = (int) $count;
        } else {
            $args['posts_per_page'] = -1;
        }

        if ($page) {
            $args['paged'] = (int) $page;
        } else {
            $args['paged'] = 1;
        }

        return $args;
    }
}
