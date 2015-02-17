<?php

namespace Rip_General\Classes;

/**
 * Description of rip-di-container
 *
 * @author Gabriele
 */
class Rip_Di_Container {

    private $_container;
    private static $_instance = null;

    /**
     * On construction set all 
     * plugin dependencies.
     */
    private function __construct() {
        $this->_set_dependencies();
    }

    public static function get_instance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_container() {
        return $this->_container;
    }

    /**
     * Set the plugin dependencies..
     * 
     * @return \Rip_General\Services\Rip_General_Container
     */
    private function _set_dependencies() {
        $this->_container = new \Rip_General\Vendor\Pimple\Lib\Pimple();

        //
        // General plugin's dependencies
        //
        $this->_container['generalDao'] = function($c) {
            return new \Rip_General\Daos\Rip_General_Dao();
        };

        $this->_container['postsDao'] = function($c) {
            return new \Rip_General\Daos\Rip_Posts_Dao();
        };

        $this->_container['attachmentsDao'] = function($c) {
            return new \Rip_General\Daos\Rip_Attachment_Dao();
        };

        $this->_container['generalService'] = function($c) {
            return new \Rip_General\Services\Rip_General_Service();
        };

        $this->_container['generalQueryService'] = function($c) {
            return new \Rip_General\Services\Rip_General_Query_Service(
                    $c['generalDao'], $c['postsDao']
            );
        };

        $this->_container['transaction'] = function($c) {
            return new \Rip_General\Classes\Rip_Transaction();
        };

        //
        // Charts plugin's dependencies
        //
        $this->_container['chartsDao'] = function($c) {
            return new \Rip_Charts\Daos\Rip_Charts_Dao();
        };

        $this->_container['completeChartsDao'] = function($c) {
            return new \Rip_Charts\Daos\Rip_Complete_Charts_Dao();
        };

        $this->_container['chartsVoteValidator'] = function ($c) {
            return new \Rip_Charts\Validators\Rip_Charts_Vote_Validator();
        };

        $this->_container['chartsQueryService'] = function($c) {
            return new \Rip_Charts\Services\Rip_Charts_Query_Service(
                    $c['chartsDao'], $c['completeChartsDao'], $c['postsDao']
            );
        };

        $this->_container['chartsPersistsService'] = function($c) {
            return new \Rip_Charts\Services\Rip_Charts_Persist_Service(
                    $c['completeChartsDao'], $c['chartsQueryService'], $c['chartsVoteValidator'], $c['transaction']
            );
        };

        //
        // Songs plugin's dependencies
        //
        $this->_container['songsDao'] = function($c) {
            return new \Rip_Songs\Daos\Rip_Songs_Dao();
        };

        $this->_container['songsQueryService'] = function($c) {
            return new \Rip_Songs\Services\Rip_Songs_Query_Service(
                    $c['songsDao'], $c['postsDao'], $c['generalService']
            );
        };

        return $this;
    }

}
