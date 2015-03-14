<?php

namespace Rip_General\Mappers;

/**
 * A generic Mapper?!
 *
 * @author Gabriele
 */
class Rip_General_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {
    
    public function __construct() {
        
    }


    /**
     * Map geographic data 
     * into a more coheren structure.
     * 
     * @param array $data
     * @return boolean|array
     */
    public function map(array $data = array()) {
        if (empty($data)) {
            return false;
        }

        $accumulator = array();

        foreach ($data as $item) {
            array_push($accumulator, $item['value']);
        }

        return $accumulator;
    }

}
