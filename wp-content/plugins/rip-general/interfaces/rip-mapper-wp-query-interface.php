<?php

namespace Rip_General\Interfaces;

/**
 * An interface that all
 * Wp_Query mapper must respect.
 * 
 * @author Gabriele
 */
interface Rip_Mapper_Wp_Query_Interface {
    public function map(\WP_Query $query);
}
