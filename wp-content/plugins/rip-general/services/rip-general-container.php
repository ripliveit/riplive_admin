<?php

namespace Rip_General\Services;

/**
 * Dependency Injection container
 * for general plugin.
 *
 * @author Gabriele
 */
class Rip_General_Container extends \Rip_General\Vendor\Pimple\Lib\Pimple {

    /**
     * On construction set all 
     * plugin dependencies.
     */
    public function __construct() {
        $this->_set_dependencies();
    }
    
    /**
     * Set the plugin dependencies..
     * 
     * @return \Rip_General\Services\Rip_General_Container
     */
    private function _set_dependencies() {
        $this['generalDao'] = function($container) {
            return new \Rip_General\Daos\Rip_General_Dao();
        };

        $this['postsDao'] = function($container) {
            return new \Rip_General\Daos\Rip_Posts_Dao();
        };

        $this['generalQueryService'] = function($container) {
            return new \Rip_General\Services\Rip_General_Query_Service(
                    $container['generalDao'], $container['postsDao']
            );
        };

        return $this;
    }

}
