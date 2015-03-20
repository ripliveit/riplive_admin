<?php

namespace Rip_Seo;

/*
  Plugin Name: Seo
  Description: Plugin per la creazione della sitemap
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Seo Plugin
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Seo_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    public function _init() {
        $this->_ajax = array(
            'rip_seo_get_sitemap' => array(
                'class_name' => '\Rip_Seo\Controllers\Rip_Seo_Controller',
                'method_name' => 'get_sitemap',
            ),
            'rip_seo_generate_xml_sitemap' => array(
                'class_name' => '\Rip_Seo\Controllers\Rip_Seo_Controller',
                'method_name' => 'generate_xml_sitemap',
            ),
            'rip_seo_get_meta_by_path' => array(
                'class_name' => '\Rip_Seo\Controllers\Rip_Seo_Controller',
                'method_name' => 'get_meta_by_path',
            )
        );
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$rip_seo_plugin = new \Rip_Seo\Rip_Seo_Plugin();
