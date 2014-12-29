<?php

namespace Rip_Podcasts;

/*
  Plugin Name: Podcasts
  Description: Plugin per la gestione dei podcast
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Podcasts Plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Podcasts_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set plugin's configuration.
     */
    protected function _init() {
        $this->_admin_podcasts_view_helper = new \Rip_Podcasts\View_Helpers\Rip_Admin_Podcasts_View_Helper();

        $this->_menu_pages = array(
            array(
                'page_title' => __('Podcasts '),
                'menu_title' => __('Gestione Podcasts'),
                'capability' => 'edit_posts',
                'menu_slug' => __FILE__,
                'function' => array($this->_admin_podcasts_view_helper, 'render'),
            ),
        );

        $this->_ajax = array(
            'rip_podcasts_get_podcasts_number_of_pages' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'get_podcasts_number_of_pages',
            ),
            'rip_podcasts_get_all_podcasts' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'get_all_podcasts',
            ),
            'rip_podcasts_get_all_podcasts_by_program_slug' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'get_all_podcasts_by_program_slug',
            ),
            'rip_podcasts_get_podcast_by_id' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'get_podcast_by_id',
            ),
            'rip_podcasts_insert_podcast' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'insert_podcast',
            ),
            'rip_podcasts_update_podcast' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'update_podcast',
            ),
            'rip_podcasts_delete_podcast' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'delete_podcast',
            ),
            'rip_podcasts_upload_podcast_image' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'upload_podcast_image',
            ),
            'rip_podcasts_generate_podcasts_xml' => array(
                'class_name' => '\Rip_Podcasts\Controllers\Rip_Podcasts_Controller',
                'method_name' => 'generate_podcasts_xml',
            ),
        );
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$rip_podcasts_plugin = new \Rip_Podcasts\Rip_Podcasts_Plugin();
