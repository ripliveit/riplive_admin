<?php

namespace Rip_Programs;

/*
  Plugin Name: Programmi
  Description: Plugin per la gestione dei programmi
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Programmi plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Programs_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {
    
    private $_days;
    
    private $_authors;
    
    public function __construct(
            \Rip_General\Classes\Rip_Abstract_Query_Service $authors_query_service, 
            \Rip_General\Services\Rip_General_Service $general_service
    ) {
        $this->_authors = $authors_query_service->get_users_for_metabox();
        $this->_days    = $general_service->get_days();
        parent::__construct();
        
        
    }
    
    /**
     * Set plugin's configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'Rip_';

        $this->_assets_folder = plugin_dir_path(__FILE__) . 'assets';

        $this->_assets = array(
            'css' => 'jquery-ui-timepicker.css',
            'js' => 'jquery-ui-timepicker.js'
        );

        $this->_post_types = array(
            array(
                'name' => 'programs',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Programmi', 'programmi'),
                        'singular_name' => _x('Programma', 'programma'),
                        'add_new' => _x('Aggiungi nuovo programma', ''),
                        'add_new_item' => __('Aggiungi nuovo programma'),
                        'edit_item' => __('Modifica programma'),
                        'new_item' => __('Nuovo programma'),
                        'all_items' => __('Tutti i programmi'),
                        'view_item' => __('Visualizza i programmi'),
                        'search_items' => __('Cerca programma'),
                        'not_found' => __('Nessun programma'),
                        'not_found_in_trash' => __('Nessun programma nel cestino'),
                        'menu_name' => __('Programmi')
                    ),
                    'public' => true,
                    'exclude_from_search' => false,
                    'publicly_queryable' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'query_var' => true,
                    'rewrite' => true,
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'show_in_admin_bar' => true,
                    'menu_position' => 5,
                    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
                ),
            ),
        );

        $this->_taxonomies = array(
            array(
                'taxonomy_name' => 'program-category',
                'object_type' => array('programs'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Categoria programma',
                    'singular_label' => 'Categoria programma',
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
                'taxonomy_name' => 'program-tag',
                'object_type' => array('programs'),
                'args' => array(
                    'hierarchical' => false,
                    'labels' => array(
                        'name' => __('Tag programma'),
                        'singular_name' => __('Tag brano'),
                        'search_items' => __('Cerca tag programmi'),
                        'popular_items' => __('Tag programmi piu popolari'),
                        'all_items' => __('Tutti i tag'),
                        'edit_item' => __('Modifica tag programma'),
                        'update_item' => __('Aggiorna tag programma'),
                        'add_new_item' => __('Aggiungi nuovo tag programma'),
                        'new_item_name' => __('Nuovo tag programma'),
                        'separate_items_with_commas' => __('Separa i tag con delle virgole'),
                        'add_or_remove_items' => __('Aggiungi o rimuovi tag'),
                        'choose_from_most_used' => __('Scegli un tag fra quelli più utilizzati'),
                        'menu_name' => __('Tag Programmi'),
                    ),
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'update_count_callback' => '_update_post_term_count',
                    'query_var' => true,
                    'rewrite' => true,
                ),
            ),
        );

        $this->_metaboxes = array(
            array(
                'args' => array(
                    'post_type' => 'programs',
                    'id' => 'programs-metabox',
                    'title' => 'Informazioni aggiuntive programma',
                    'context' => 'normal',
                    'priority' => 'high',
                ),
                'fields' => array(
                    array(
                        'id' => 'programs-hidden',
                        'type' => 'hidden',
                        'name' => 'programs-hidden',
                        'description' => '',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'programs-schedule',
                        'type' => 'time',
                        'name' => 'programs-schedule',
                        'description' => 'Orari di messa in onda del programma',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'programs-days',
                        'type' => 'checkbox',
                        'name' => 'programmi-days',
                        'description' => 'Giorni di messa in onda del programma',
                        'default' => '',
                        'required' => '',
                        'placeholder' => '',
                        'options' => $this->_days
                    ),
                    array(
                        'id' => 'reruns-schedule',
                        'type' => 'time',
                        'name' => 'reruns-schedule',
                        'description' => 'Orari di messa in onda della replica',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'reruns-days',
                        'type' => 'checkbox',
                        'name' => 'reruns-days',
                        'description' => 'Giorni di messa in onda della replica',
                        'default' => '',
                        'required' => '',
                        'placeholder' => '',
                        'options' => $this->_days
                    ),
                    array(
                        'id' => 'programs-authors',
                        'type' => 'checkbox',
                        'name' => 'programs-author',
                        'description' => 'Conduttori del programma',
                        'default' => '',
                        'required' => '',
                        'options' => $this->_authors
                    ),
                    array(
                        'id' => 'programs-facebook',
                        'type' => 'text',
                        'name' => 'programs-facebook',
                        'description' => 'Link pagina Facebook',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina facebook del programma'
                    ),
                    array(
                        'id' => 'programs-twitter',
                        'type' => 'text',
                        'name' => 'programs-twitter',
                        'description' => 'Link pagina Twitter',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina twitter del programma'
                    ),
                    array(
                        'id' => 'programs-gplus',
                        'type' => 'text',
                        'name' => 'programs-gplus',
                        'description' => 'Link pagina Google Plus',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Google Plus del programma'
                    ),
                ),
            ),
        );

        $this->_ajax = array(
            'rip_programs_get_all_programs' => array(
                'class_name' => '\Rip_Programs\Controllers\Rip_Programs_Controller',
                'method_name' => 'get_all_programs',
            ),
            'rip_programs_get_program_by_slug' => array(
                'class_name' => '\Rip_Programs\Controllers\Rip_Programs_Controller',
                'method_name' => 'get_program_by_slug',
            ),
            'rip_programs_get_programs_schedule' => array(
                'class_name' => '\Rip_Programs\Controllers\Rip_Programs_Controller',
                'method_name' => 'get_programs_schedule',
            ),
        );
        
        $this->_filters_to_add = array(
            array(
                'tag' => 'json_api_encode',
                'class_name' => '\Rip_Programs\Filters\Rip_Programs_Json_Api_Filter',
                'method_name' => 'check_programs_meta',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$container = \Rip_General\Classes\Rip_Di_Container::get_instance()->get_container();
$programs_plugin = new \Rip_Programs\Rip_Programs_Plugin($container['authorsQueryService'], $container['generalService']);
