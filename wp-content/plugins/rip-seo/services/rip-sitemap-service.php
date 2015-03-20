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
     * to Seo Dao
     * 
     * @var Object
     */
    private $_seo_dao;
    
    /**
     * The folder where the sitempa stay.
     * 
     * @var Object
     */
    private $_folder;
    
    /**
     * On construction set
     * service dependency.
     * 
     * @param type $sitemap_generator
     * @param \Rip_General\Classes\Rip_Abstract_Dao $seo_dao
     */
    public function __construct(
        $sitemap_generator, 
        \Rip_General\Classes\Rip_Abstract_Dao $seo_dao
    ) {
        $this->_folder = plugin_dir_path(__FILE__) . '../assets/';
        $this->_sitemap_generator = $sitemap_generator;
        $this->_seo_dao = $seo_dao;
        
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
        $data = $this->_seo_dao->get_all_path();

        $result = $this->_sitemap_generator->set_folder($this->_folder)
                ->generate($data);
        
        return $result;
    }

}
