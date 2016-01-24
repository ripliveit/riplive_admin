<?php

namespace Rip_Authors;

/*
  Plugin Name: Autori di Rip
  Description: Plugin per la gestione degli autori di Radio Illusioni Parallele
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Authors Plugin
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Authors_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    public function _init() {
        $this->_ajax = array(
            'rip_authors_get_all_authors' => array(
                'class_name' => '\Rip_Authors\Controllers\Rip_Authors_Controller',
                'method_name' => 'get_all_authors',
            ),
            'rip_authors_get_author_by_slug' => array(
                'class_name' => '\Rip_Authors\Controllers\Rip_Authors_Controller',
                'method_name' => 'get_author_by_slug',
            ),
        );
        
        add_action( 'admin_init', function() {
            $role = get_role( 'author' );
            $role->add_cap( 'unfiltered_html' );
        });

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$rip_authors_plugin = new \Rip_Authors\Rip_Authors_Plugin();
