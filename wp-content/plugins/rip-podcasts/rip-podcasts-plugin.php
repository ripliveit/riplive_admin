<?php

/*
  Plugin Name: Podcasts
  Description: Plugin per la gestione dei podcast
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */


require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general.php';
$autoloader = new rip_autoloader(plugin_dir_path(__FILE__));

class rip_podcasts extends rip_abstract_plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {

//        $this->_tables = array(
//            array(
//                'name' => 'podcasts',
//                'sql' => 'CREATE TABLE wp_podcasts (
//                            id int(10) unsigned NOT NULL AUTO_INCREMENT,
//                            id_program int(10) unsigned NOT NULL,
//                            title varchar(255) DEFAULT NULL,
//                            summary text,
//                            genre varchar(255) DEFAULT NULL,
//                            authors varchar(255) DEFAULT NULL,
//                            file_name varchar(255) DEFAULT NULL,
//                            file_length int(10) unsigned DEFAULT NULL,
//                            duration varchar(255) DEFAULT NULL,
//                            year year(4) DEFAULT NULL,
//                            date date NOT NULL,
//                            upload_date datetime NOT NULL,
//                            url varchar(255) DEFAULT NULL,
//                            PRIMARY KEY (id)
//                          ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;'
//            ),
//        );

        $this->_admin_podcasts_view_helper = new rip_admin_podcasts_view_helper();

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
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'get_podcasts_number_of_pages',
            ),
            'rip_podcasts_get_all_podcasts' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'get_all_podcasts',
            ),
            'rip_podcasts_get_all_podcasts_by_program_slug' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'get_all_podcasts_by_program_slug',
            ),
            'rip_podcasts_get_podcast_by_id' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'get_podcast_by_id',
            ),
            'rip_podcasts_insert_podcast' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'insert_podcast',
            ),
            'rip_podcasts_update_podcast' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'update_podcast',
            ),
            'rip_podcasts_delete_podcast' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'delete_podcast',
            ),
            'rip_podcasts_upload_podcast_image' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'upload_podcast_image',
            ),
            'rip_podcasts_generate_podcasts_xml' => array(
                'class' => 'rip_podcasts_ajax_front_controller',
                'method_name' => 'generate_podcasts_xml',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        register_activation_hook(__FILE__, array($this, 'load_tables'));
    }

}

$rip_podcasts = new rip_podcasts();