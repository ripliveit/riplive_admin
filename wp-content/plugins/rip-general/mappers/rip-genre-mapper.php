<?php

namespace Rip_General\Mappers;

/**
 * Map raw data from the database
 * into a more coherent structure.
 *
 * @author Gabriele
 */
class Rip_Genre_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {

    public function __construct() {
        
    }

    /**
     * Map an array of genre's data
     * into a more coherent structure.
     * 
     * @param array $genres
     * @return boolean|array
     */
    public function map(array $genres = array()) {
        if (empty($genres)) {
            return false;
        }

        $accumulator = array();

        foreach ($genres as $item) {
            array_push($accumulator, array(
                'id' => $item->term_id,
                'slug' => $item->slug,
                'name' => $item->name,
                'count' => $item->count,
            ));
        }

        return $accumulator;
    }

}
