<?php

namespace Rip_Charts;

/*
  Plugin Name: Classifiche
  Description: Plugin per la gestione delle classifiche
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Charts plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_general_abstract_plugin
 */
class Rip_Charts_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'rip_';
        $this->_admin_charts_view_helper = new \Rip_Charts\View_Helpers\Rip_Admin_Charts_View_helper();

        $this->_tables = array(
            array(
                'name' => 'charts_songs',
                'sql' => 'CREATE TABLE wp_charts_songs (
                            id int(10) unsigned NOT NULL AUTO_INCREMENT,
                            chart_archive_slug varchar(255) DEFAULT NULL,
                            id_chart int(10) unsigned NOT NULL,
                            id_song int(10) unsigned NOT NULL,
                            user_vote int(10) unsigned NOT NULL DEFAULT 0,
                            PRIMARY KEY  (id),
                            KEY ID_CHART_INDEX (id_chart),
                            KEY ID_SONG_INDEX (id_song),
                            KEY FK_CHART_ARCHIVE_SLUG_idx (chart_archive_slug),
                            CONSTRAINT FK_CHART_ARCHIVE_SLUG FOREIGN KEY (chart_archive_slug) REFERENCES wp_charts_archive (chart_archive_slug) ON DELETE CASCADE ON UPDATE CASCADE
                          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8$$'
            ),
            array(
                'name' => 'charts_archive',
                'sql' => 'CREATE TABLE wp_charts_archive (
                            id int(11) NOT NULL AUTO_INCREMENT,
                            chart_archive_slug varchar(255) DEFAULT NULL,
                            id_chart int(11) DEFAULT NULL,
                            chart_slug varchar(255) DEFAULT NULL,
                            chart_date date DEFAULT NULL,
                            chart_creation_date datetime DEFAULT NULL,
                            songs_number int(11) DEFAULT NULL,
                            PRIMARY KEY  (id),
                            UNIQUE KEY ID_CHART_CHART_DATE_INDEX (id_chart,chart_date),
                            UNIQUE KEY CHART_ARCHIVE_SLUG_INDEX (chart_archive_slug)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            ),
        );

        $this->_post_types = array(
            array(
                'name' => 'charts',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Charts', 'charts'),
                        'singular_name' => _x('Charts', 'charts'),
                        'add_new' => _x('Aggiungi nuova chart', ''),
                        'add_new_item' => __('Aggiungi nuova chart'),
                        'edit_item' => __('Modifica chart'),
                        'new_item' => __('Nuova chart'),
                        'all_items' => __('Tutte le chart'),
                        'view_item' => __('Visualizza le charts'),
                        'search_items' => __('Cerca charts'),
                        'not_found' => __('Nessuna chart'),
                        'not_found_in_trash' => __('Nessun chart nel cestino'),
                        'menu_name' => __('Charts')
                    ),
                    'public' => true,
                    'exclude_from_search' => false,
                    'publicly_queryable' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'query_var' => true,
                    'rewrite' => true,
                    'capability_type' => 'post',
                    'has' => true,
                    'hierarchical' => false,
                    'show_in_admin_bar' => true,
                    'menu_position' => 5,
                    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
                ),
            ),
        );

        $this->_taxonomies = array(
            array(
                'taxonomy_name' => 'chart-genre',
                'object_type' => array('charts'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Genere charts',
                    'singular_label' => 'Genere charts',
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'query_var' => true,
                    'rewrite' => true,
                    'capabilities' => array(
                        'manage_terms' => 'manage_categories',
                        'edit_terms' => 'manage_categories',
                        'delete_terms' => 'manage_categories',
                        'assign_terms' => 'edit_posts',
                    ),
                ),
            ),
            array(
                'taxonomy_name' => 'chart-tag',
                'object_type' => array('charts'),
                'args' => array(
                    'hierarchical' => false,
                    'labels' => array(
                        'name' => __('Tag charts'),
                        'singular_name' => __('Tag chart'),
                        'search_items' => __('Cerca tag chart'),
                        'popular_items' => __('Tag chart piu popolari'),
                        'all_items' => __('Tutti i tag'),
                        'edit_item' => __('Modifica tag chart'),
                        'update_item' => __('Aggiorna tag chart'),
                        'add_new_item' => __('Aggiungi nuovo tag chart'),
                        'new_item_name' => __('Nuovo tag chart'),
                        'separate_items_with_commas' => __('Separa i tag con delle virgole'),
                        'add_or_remove_items' => __('Aggiungi o rimuovi tag'),
                        'choose_from_most_used' => __('Scegli un tag fra quelli più utilizzati'),
                        'menu_name' => __('Tag charts'),
                    ),
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'update_count_callback' => '_update_post_term_count',
                    'query_var' => true,
                    'rewrite' => true,
                ),
            ),
        );

        $this->_menu_pages = array(
            array(
                'page_title' => __('Chart '),
                'menu_title' => __('Gestione Charts'),
                'capability' => 'edit_posts',
                'menu_slug' => __FILE__,
                'function' => array($this->_admin_charts_view_helper, 'render'),
            ),
        );

        $this->_ajax = array(
            'rip_charts_get_all_charts' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_all_charts',
            ),
            'rip_charts_get_chart_by_slug' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_chart_by_slug',
            ),
            'rip_charts_get_complete_charts_number_of_pages' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_complete_charts_number_of_pages',
            ),
            'rip_charts_get_all_complete_charts' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_all_complete_charts',
            ),
            'rip_charts_get_last_complete_charts_per_genre' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_last_complete_charts_per_genre',
            ),
            'rip_charts_get_all_complete_charts_by_chart_genre' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_all_complete_charts_by_chart_genre',
            ),
            'rip_charts_get_complete_chart_by_chart_archive_slug' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'get_complete_chart_by_chart_archive_slug',
            ),
            'rip_charts_insert_complete_chart' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'insert_complete_chart',
            ),
            'rip_charts_update_complete_chart' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'update_complete_chart',
            ),
            'rip_charts_delete_complete_chart' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'delete_complete_chart',
            ),
            'rip_charts_duplicate_complete_chart' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'duplicate_complete_chart',
            ),
            'rip_charts_insert_complete_chart_vote' => array(
                'class_name' => '\Rip_Charts\Controllers\Rip_Charts_Controller',
                'method_name' => 'insert_complete_chart_vote',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        //register_activation_hook(__FILE__, array($this, 'load_tables'));
    }

}

$charts = new \Rip_Charts\Rip_Charts_Plugin();
