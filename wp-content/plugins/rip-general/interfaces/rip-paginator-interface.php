<?php

namespace Rip_General\Interfaces;

/**
 * An interface that all
 * Wp_Query mapper must respect.
 * 
 * @author Gabriele
 */
interface Rip_Paginator_Interface {
    public function get_items_per_page();

    public function set_items_per_page($count = null);
}
