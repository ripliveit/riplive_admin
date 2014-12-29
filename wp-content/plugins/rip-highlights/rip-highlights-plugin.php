<?php

namespace Rip_Highlights;

/*
  Plugin Name: Highlights
  Description: Plugin per la gestione dei brani
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Highlight Plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Highlights_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set plugin's configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'Rip_';

        $this->_post_types = array(
            array(
                'name' => 'highlights',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Highlights', 'highlights'),
                        'singular_name' => _x('Highlight', 'highlight'),
                        'add_new' => _x('Aggiungi nuovo', ''),
                        'add_new_item' => __('Aggiungi nuovo highlight'),
                        'edit_item' => __('Modifica highlight'),
                        'new_item' => __('Nuovo highlight'),
                        'all_items' => __('Tutti gli highlights'),
                        'view_item' => __('Visualizza gli highlights'),
                        'search_items' => __('Cerca highlight'),
                        'not_found' => __('Nessun highlight'),
                        'not_found_in_trash' => __('Nessun highlight nel cestino'),
                        'menu_name' => __('Highlights')
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
                    'supports' => array('title', 'author', 'thumbnail', 'excerpt',)
                ),
            ),
        );

        $this->_taxonomies = array(
            array(
                'taxonomy_name' => 'highlights-genre',
                'object_type' => array('highlights'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Genere highlight',
                    'singular_label' => 'Genere highlight',
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
            )
        );

        $this->_metaboxes = array(
            array(
                'args' => array(
                    'post_type' => 'highlights',
                    'id' => 'highlights-metabox',
                    'title' => 'Informazioni Highlights',
                    'context' => 'normal',
                    'priority' => 'low',
                ),
                'fields' => array(
                    array(
                        'id' => 'highlights-hidden',
                        'type' => 'hidden',
                        'name' => 'highlights-hidden',
                        'description' => '',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'highlights-link',
                        'type' => 'text',
                        'name' => 'highlights-link',
                        'description' => 'Link del contenuto',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link del contenuto che vuoi mettere in evidenza'
                    ),
                ),
            ),
        );
        
        $this->_ajax = array(
            'rip_highlights_get_all_highlights' => array(
                'class_name' => '\Rip_Highlights\Controllers\Rip_Highlights_Controller',
                'method_name' => 'get_all_highlights',
            )
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$highlights_plugin = new \Rip_Highlights\Rip_Highlights_Plugin();
