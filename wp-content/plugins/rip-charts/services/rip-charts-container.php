<?php

namespace Rip_Charts\Services;

/**
 * Charts dependency
 * injection container.
 *
 * @author Gabriele
 */
class Rip_Charts_Container extends \Rip_General\Vendor\Pimple\Lib\Pimple {
    
    /**
     * On construction set all 
     * plugin dependencies.
     */
    public function __construct() {
        $this->_set_dependencies();
    }
    
    /**
     * Set the plugin dependencies.
     * 
     * @return \Rip_Charts\Services\Rip_Charts_Container
     */
    private function _set_dependencies() {
        $this['chartsDao'] = function($container) {
            return new \Rip_Charts\Daos\Rip_Charts_Dao();
        };

        $this['completeChartsDao'] = function($container) {
            return new \Rip_Charts\Daos\Rip_Complete_Charts_Dao();
        };

        $this['postsDao'] = function($container) {
            return new \Rip_General\Daos\Rip_Posts_Dao();
        };

        $this['transaction'] = function($container) {
            return new \Rip_General\Classes\Rip_Transaction();
        };
        
        $this['chartsVoteValidator'] = function ($container) {
            return new \Rip_Charts\Validators\Rip_Charts_Vote_Validator();
        };

        $this['chartsQueryService'] = function($container) {
            return new \Rip_Charts\Services\Rip_Charts_Query_Service(
                    $container['chartsDao'], $container['completeChartsDao'], $container['postsDao']
            );
        };

        $this['chartsPersistsService'] = function($container) {
            return new \Rip_Charts\Services\Rip_Charts_Persist_Service(
                    $container['completeChartsDao'], $container['chartsQueryService'], $container['chartsVoteValidator'], $container['transaction']
            );
        };

        return $this;
    }

}
