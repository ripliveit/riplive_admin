<?php

namespace Rip_Songs\Services;

/**
 * Dependency Injection container
 * for songs plugin.
 *
 * @author Gabriele
 */
class Rip_Songs_Container extends \Rip_General\Vendor\Pimple\Lib\Pimple {

    public function __construct() {
        $this->_set_dependencies();
    }

    private function _set_dependencies() {
        $this['songsDao'] = function($container) {
            return new \Rip_Songs\Daos\Rip_Songs_Dao();
        };
        
        $this['postsDao'] = function($container) {
            return new \Rip_General\Daos\Rip_Posts_Dao();
        };
        
        $this['generalService'] = function($container) {
            return new \Rip_General\Services\Rip_General_Service();
        };

        $this['songsQueryService'] = function($container) {
            return new \Rip_Songs\Services\Rip_Songs_Query_Service(
                    $container['songsDao'], $container['postsDao'], $container['generalService']
            );
        };

        return $this;
    }

}
