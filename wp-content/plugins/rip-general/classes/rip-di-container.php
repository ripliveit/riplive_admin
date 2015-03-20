<?php

namespace Rip_General\Classes;

/**
 * Description of rip-di-container
 *
 * @author Gabriele
 */
class Rip_Di_Container {
    
    /**
     * Holds a reference
     * to Pimple container.
     * 
     * @var Object 
     */
    private $_container;
    
    /**
     * The singleton instance.
     * 
     * @var Object 
     */
    private static $_instance = null;

    /**
     * On construction set all 
     * plugin dependencies.
     */
    private function __construct() {
        $this->_set_dependencies();
    }
    
    /**
     * Return the singleton instace.
     * 
     * @return Object
     */
    public static function get_instance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Return the container.
     * 
     * @return Object
     */
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

        $this->_container['postsValidator'] = function($c) {
            return new \Rip_General\Validators\Rip_Posts_Validator();
        };

        $this->_container['attachmentsDao'] = function($c) {
            return new \Rip_General\Daos\Rip_Attachment_Dao();
        };
        
        $this->_container['postsQueryService'] = function($c) {
            return new \Rip_General\Services\Rip_Posts_Query_Service(
                    $c['postsDao']
            );
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
        
        $this->_container['message'] = function($c) {
            return new \Rip_General\Dto\Message();
        };
        
        
        //
        // Artist plugin's dependencies
        //
        $this->_container['artistsDao'] = function($c) {
            return new \Rip_Artists\Daos\Rip_Artists_Dao();
        };

        $this->_container['artistsQueryService'] = function($c) {
            return new \Rip_Artists\Services\Rip_Artists_Query_Service(
                    $c['artistsDao'], 
                    $c['postsDao'], 
                    $c['generalService']
            );
        };
        
        
        //
        // Author plugin's dependencies
        //
        $this->_container['authorsDao'] = function($c) {
            return new \Rip_Authors\Daos\Rip_Authors_Dao();
        };

        $this->_container['authorsQueryService'] = function($c) {
            return new \Rip_Authors\Services\Rip_Authors_Query_Service($c['authorsDao']);
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
                    $c['chartsDao'], 
                    $c['completeChartsDao'], 
                    $c['postsDao']
            );
        };

        $this->_container['chartsPersistsService'] = function($c) {
            return new \Rip_Charts\Services\Rip_Charts_Persist_Service(
                    $c['completeChartsDao'], 
                    $c['chartsQueryService'], 
                    $c['chartsVoteValidator'], 
                    $c['transaction']
            );
        };

        
        //
        // Songs plugin's dependencies
        //
        $this->_container['highlightsDao'] = function($c) {
            return new \Rip_Highlights\Daos\Rip_Highlights_Dao();
        };

        $this->_container['highlightsQueryService'] = function($c) {
            return new \Rip_Highlights\Services\Rip_Highlights_Query_Service(
                    $c['highlightsDao'], 
                    $c['postsDao']
            );
        };


        //
        // Podcasts plugin's dependencies
        //
        $this->_container['podcastsDao'] = function($c) {
            return new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        };
        
        $this->_container['podcastsImageUploader'] = function($c) {
            return new \Rip_Podcasts\Classes\Rip_Podcasts_Image_Uploader($c['message']);
        };
        
        $this->_container['podcastsQueryService'] = function($c) {
            return new \Rip_Podcasts\Services\Rip_Podcasts_Query_Service(
                    $c['podcastsDao'], 
                    $c['postsDao'], 
                    $c['authorsQueryService']);
        };
        
        $this->_container['podcastsPersistService'] = function($c) {
            return new \Rip_Podcasts\Services\Rip_Podcasts_Persist_Service(
                    $c['podcastsDao'], 
                    $c['postsDao'], 
                    $c['podcastsQueryService'], 
                    $c['transaction'],
                    $c['podcastsImageUploader']
            );
        };
        
        $this->_container['podcastsS3Service'] = function($c) {
            return new \Rip_Podcasts\Services\Rip_Podcasts_S3_Service($c['message']);
        };
        
        $this->_container['podcastsXmlGenerator'] = function($c) {
          return new \Rip_Podcasts\Classes\Rip_Podcasts_Xml_Generator($c['message']);  
        };
        
        $this->_container['podcastsXmlService'] = function($c) {
            return new \Rip_Podcasts\Services\Rip_Podcasts_Xml_Service(
                    $c['podcastsQueryService'], 
                    $c['programsQueryService'], 
                    $c['podcastsS3Service'], 
                    $c['podcastsXmlGenerator']
            );
        };

        
        //
        // Programs plugin's dependencies
        //
        $this->_container['programsDao'] = function($c) {
            return new \Rip_Programs\Daos\Rip_Programs_Dao();
        };

        $this->_container['programsQueryService'] = function($c) {
            return new \Rip_Programs\Services\Rip_Programs_Query_Service(
                    $c['programsDao'], 
                    $c['podcastsDao'], 
                    $c['postsDao'], 
                    $c['postsValidator'], 
                    $c['authorsQueryService'], 
                    $c['generalService']
            );
        };
        
        
        //
        // SEO plugin's dependencies
        //
        $this->_container['seoDao'] = function($c) {
            return new \Rip_Seo\Daos\Rip_Seo_Dao();
        };
        
        $this->_container['sitemapGenerator'] = function($c) {
            return new \Rip_Seo\Classes\Rip_Sitemap_Generator($c['message']);
        };
        
        $this->_container['sitemapService'] = function($c) {
            return new \Rip_Seo\Services\Rip_Sitemap_Service(
                    $c['sitemapGenerator'],
                    $c['seoDao']
            );
        };
                
        $this->_container['seoQueryService'] = function($c) {
            return new \Rip_Seo\Services\Rip_Seo_Query_Service(
                    $c['seoDao']
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
                    $c['songsDao'], 
                    $c['postsDao'], 
                    $c['generalService']
            );
        };

        return $this;
    }

}
