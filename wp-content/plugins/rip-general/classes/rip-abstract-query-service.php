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
     * @param type $count
     * @return \rip_songs_dao
     */
    public function set_items_per_page($count = null) {
        if ($count && is_int($count)) {
            $this->_items_per_page = (int) $count;
        }

        return $this;
    }
    
    
    public function validate_items_per_page($count = null) {
        if ($count && is_int($count)) {
            return (int) $count;
        }
        
        return $this->_items_per_page;
    }

}
