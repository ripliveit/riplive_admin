<?php

namespace Rip_General\Mappers;

/**
 * A generic Mapper?!
 *
 * @author Gabriele
 */
class Rip_General_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {

    public function set_data(\WP_Query $query) {
        if (empty($data)) {
            return false;
        }

        $out = array();

        foreach ($data as $item) {
            array_push($out, $item['value']);
        }

        return $out;
    }

}
