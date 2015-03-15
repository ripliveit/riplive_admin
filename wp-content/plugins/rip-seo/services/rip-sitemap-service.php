<?php

namespace Rip_Seo\Services;

/**
 * A Service used to retrieve / create
 * the xml sitemap.
 * 
 * @author Gabriele
 */
class Rip_Sitemap_Service {
    
    /**
     * The class used to
     * genereta the xml sitemap.
     * 
     * @var Object
     */
    private $_sitemap_generator;

    /**
     * Holds a reference
     * to Artists Query Service
     * 
     * @var Object
     */
    private $_artists_service;
    
    /**
     * Holds a reference
     * to Authors Query Service
     * 
     * @var Object
     */
    private $_authors_service;
    
    /**
     * Holds a reference
     * to Charts Query Service
     * 
     * @var Object
     */
    private $_charts_service;
    
    /**
     * Holds a reference
     * to Podcasts Query Service
     * 
     * @var Object
     */
    private $_podcasts_service;
    
    /**
     * Holds a reference
     * to Posts Query Service
     * 
     * @var Object
     */
    private $_posts_service;
    
    /**
     * Holds a reference
     * to Progras Query Service
     * 
     * @var Object
     */
    private $_programs_service;
    
    /**
     * Holds a reference
     * to Songs Query Service
     * 
     * @var Object
     */
    private $_songs_service;
    
    /**
     * The folder where the sitempa stay.
     * 
     * @var Object
     */
    private $_folder;
    
    /**
     * On construction set
     * all query service to retrieve data for the sitemap generator.
     * 
     * @param type $sitemap_generator
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $artists_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $authors_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $charts_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $podcasts_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $posts_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $programs_service
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $songs_service
     */
    public function __construct(
        $sitemap_generator, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $artists_service, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $authors_service, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $charts_service, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $podcasts_service,
            \Rip_General\Classes\Rip_Abstract_Query_Service $posts_service,
            \Rip_General\Classes\Rip_Abstract_Query_Service $programs_service, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $songs_service
    ) {
        $this->_sitemap_generator = $sitemap_generator;
        $this->_artists_service = $artists_service;
        $this->_authors_service = $authors_service;
        $this->_charts_service = $charts_service;
        $this->_podcasts_service = $podcasts_service;
        $this->_posts_service = $posts_service;
        $this->_programs_service = $programs_service;
        $this->_songs_service = $songs_service;
        
        $this->_folder = plugin_dir_path(__FILE__) . '../assets/';
    }
    
    /**
     * Return the xml sitemap.
     * If no sitempa can be found on the default folder
     * then a new one is generated.
     * 
     * @return type
     */
    public function get_sitemap() {
        $filename = $this->_folder . 'sitemap.xml';
        
        if (file_exists($filename)) {
            return file_get_contents($filename);
        } else {
            $this->generate_xml_sitemap();
            return $this->get_sitemap();
        }
    }
    
    /**
     * Generate an XML sitemap
     * with all the site's url.
     * 
     * @return Object
     */
    public function generate_xml_sitemap() {
        $artists_obj = $this->_artists_service->get_all_artists();
        $authors_obj = $this->_authors_service->get_all_authors();
        $charts_obj = $this->_charts_service->get_all_complete_charts();

        $podcasts_number_obj = $this->_podcasts_service->get_podcasts_number_of_pages();
        $podcasts_obj = $this->_podcasts_service->get_all_podcasts(
                $podcasts_number_obj->get_number_of_pages()['count_total']
        );
        
        $posts_obj = $this->_posts_service->get_all_posts();
        $programs_obj = $this->_programs_service->get_all_programs();
        $songs_obj = $this->_songs_service->get_all_songs();

        $result = $this->_sitemap_generator->set_folder($this->_folder)
                ->generate(
                        $artists_obj->get_artists(), 
                        $authors_obj->get_authors(), 
                        $charts_obj->get_complete_charts(), 
                        $podcasts_obj->get_podcasts(),
                        $posts_obj->get_posts(),
                        $programs_obj->get_programs(), 
                        $songs_obj->get_songs()
        );
        
        return $result;
    }

}
