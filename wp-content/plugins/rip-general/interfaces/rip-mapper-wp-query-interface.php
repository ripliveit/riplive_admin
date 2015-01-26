<?php

namespace Rip_General\Interfaces;

/**
 *
 * @author Gabriele
 */
interface Rip_Mapper_Wp_Query_Interface {
    public function set_data(\WP_Query $query, $data);
}
