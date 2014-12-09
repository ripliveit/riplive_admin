<?php

/*
  Plugin Name: Etichette
  Description: Plugin per la gestione delle Etichette
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general.php';
$autoloader = new rip_autoloader(plugin_dir_path(__FILE__));

/**
 * Etichette plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_plugin
 */
class rip_labels extends rip_abstract_plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {

        $this->_template_folder = plugin_dir_path(__FILE__) . 'templates';

        $this->_theme_root = get_template_directory();

        $this->_metabox_prefix = 'rip_';

        $this->_templates = array(
            'archive-etichette.php',
            'single-etichette.php'
        );

        $this->_post_types = array(
            array(
                'name' => 'labels',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Etichette', 'etichette'),
                        'singular_name' => _x('Etichette', 'etichette'),
                        'add_new' => _x('Aggiungi nuova', ''),
                        'add_new_item' => __('Aggiungi nuova etichetta'),
                        'edit_item' => __('Modifica etichetta'),
                        'new_item' => __('Nuova etichetta'),
                        'all_items' => __('Tutte le etichette'),
                        'view_item' => __('Visualizza le etichette'),
                        'search_items' => __('Cerca brano'),
                        'not_found' => __('Nessuna etichetta'),
                        'not_found_in_trash' => __('Nessuna etichetta nel cestino'),
                        'menu_name' => __('Etichette')
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
                'taxonomy_name' => 'label-genre',
                'object_type' => array('labels'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Genere etichetta',
                    'singular_label' => 'Genere etichetta',
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
        );

        $this->_metaboxes = array(
            array(
                'args' => array(
                    'post_type' => 'labels',
                    'id' => 'labels-metabox',
                    'title' => 'Informazioni dell\'Etichetta',
                    'context' => 'normal',
                    'priority' => 'high',
                ),
                'fields' => array(
                    array(
                        'id' => 'labels-hidden',
                        'type' => 'hidden',
                        'name' => 'labels-hidden',
                        'description' => '',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'labels-email',
                        'type' => 'email',
                        'name' => 'labels-email',
                        'description' => 'Email',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci l\'indirizzo email dell\'etichetta'
                    ),
                    array(
                        'id' => 'labels-telephone',
                        'type' => 'text',
                        'name' => 'labels-telephone',
                        'description' => 'Telefono',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il telefono'
                    ),
                    array(
                        'id' => 'labels-website',
                        'type' => 'text',
                        'name' => 'labels-website',
                        'description' => 'Sito Web',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il sito web'
                    ),
                ),
            ),
        );

        $this->_ajax = array(
            'rip_labels_get_all_labels' => array(
                'class' => 'rip_labels_ajax_front_controller',
                'method_name' => 'get_all_labels',
            ),
            'rip_labels_get_all_press_offices' => array(
                'class' => 'rip_labels_ajax_front_controller',
                'method_name' => 'get_all_press_offices',
            ),
            'rip_labels_get_all_bookings' => array(
                'class' => 'rip_labels_ajax_front_controller',
                'method_name' => 'get_all_bookings',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$labels = new rip_labels();