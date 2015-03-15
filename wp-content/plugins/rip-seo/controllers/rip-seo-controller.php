<?php

namespace Rip_Seo\Controllers;

/**
 * Seo Controller
 */
class Rip_Seo_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve the sitemap
     */
    public function get_sitemap() {
        $service = $this->_container['sitemapService'];
        $result = $service->get_sitemap();

        $this->_response->set_code(200)
                ->to_xml($result);
    }

    /**
     * Generator the sitemap generator.
     */
    public function generate_xml_sitemap() {
        $service = $this->_container['sitemapService'];
        $result = $service->generate_xml_sitemap();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
