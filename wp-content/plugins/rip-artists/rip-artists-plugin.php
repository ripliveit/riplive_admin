<?php

namespace Rip_Artists;

/*
  Plugin Name: Artisti
  Description: Plugin per la gestione degli artisti.
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Artist Plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Artists_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'Rip_';

        $this->_post_types = array(
            array(
                'name' => 'artists',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Artisti', 'artisti'),
                        'singular_name' => _x('Programma', 'artista'),
                        'add_new' => _x('Aggiungi nuovo artista', ''),
                        'add_new_item' => __('Aggiungi nuovo artista'),
                        'edit_item' => __('Modifica artista'),
                        'new_item' => __('Nuovo artista'),
                        'all_items' => __('Tutti gli artisti'),
                        'view_item' => __('Visualizza gli artisti'),
                        'search_items' => __('Cerca artista'),
                        'not_found' => __('Nessun artista'),
                        'not_found_in_trash' => __('Nessun artista nel cestino'),
                        'menu_name' => __('Artisti')
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
                'taxonomy_name' => 'artist-genre',
                'object_type' => array('artists'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Genere artista',
                    'singular_label' => 'Genere artista',
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
                'taxonomy_name' => 'artist-tag',
                'object_type' => array('artists'),
                'args' => array(
                    'hierarchical' => false,
                    'labels' => array(
                        'name' => __('Tag artisti'),
                        'singular_name' => __('Tag brano'),
                        'search_items' => __('Cerca tag artisti'),
                        'popular_items' => __('Tag artisti piu popolari'),
                        'all_items' => __('Tutti i tag'),
                        'edit_item' => __('Modifica tag programma'),
                        'update_item' => __('Aggiorna tag programma'),
                        'add_new_item' => __('Aggiungi nuovo tag programma'),
                        'new_item_name' => __('Nuovo tag programma'),
                        'separate_items_with_commas' => __('Separa i tag con delle virgole'),
                        'add_or_remove_items' => __('Aggiungi o rimuovi tag'),
                        'choose_from_most_used' => __('Scegli un tag fra quelli più utilizzati'),
                        'menu_name' => __('Tag Artisti'),
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
                    'post_type' => 'artists',
                    'id' => 'artists-metabox',
                    'title' => 'Dettagli artista',
                    'context' => 'normal',
                    'priority' => 'high',
                ),
                'fields' => array(
                    array(
                        'id' => 'artists-hidden',
                        'type' => 'hidden',
                        'name' => 'artists-hidden',
                        'description' => '',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'artists-lineup',
                        'type' => 'text',
                        'name' => 'artists-lineup',
                        'description' => 'Line Up',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci la Line Up'
                    ),
                    array(
                        'id' => 'artists-foundation',
                        'type' => 'date',
                        'name' => 'artisti-foundation',
                        'description' => 'Data di fondazione',
                        'default' => '',
                        'required' => '',
                        'placeholder' => '',
                    ),
                    array(
                        'id' => 'artists-label',
                        'type' => 'autocomplete',
                        'name' => 'artists-label',
                        'description' => 'Etichetta',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_labels_get_all_labels'
                    ),
                    array(
                        'id' => 'artists-booking',
                        'type' => 'autocomplete',
                        'name' => 'artists-booking',
                        'description' => 'Booking',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_labels_get_all_bookings'
                    ),
                    array(
                        'id' => 'artists-press',
                        'type' => 'autocomplete',
                        'name' => 'artists-press',
                        'description' => 'Ufficio Stampa',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_labels_get_all_press_offices'
                    ),
                    array(
                        'id' => 'artists-email',
                        'type' => 'email',
                        'name' => 'artists-email',
                        'description' => 'Email',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci l\'indirizzo email dell\'artista'
                    ),
                    array(
                        'id' => 'artists-telephone',
                        'type' => 'text',
                        'name' => 'artists-telephone',
                        'description' => 'Telefono',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il telefono'
                    ),
                    array(
                        'id' => 'artists-website',
                        'type' => 'text',
                        'name' => 'artists-website',
                        'description' => 'Sito Web',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci indirizzo sito web'
                    ),
                    array(
                        'id' => 'artists-facebook',
                        'type' => 'text',
                        'name' => 'artists-facebook',
                        'description' => 'Link pagina Facebook',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Facebook  dell\'artista'
                    ),
                    array(
                        'id' => 'artists-gplus',
                        'type' => 'text',
                        'name' => 'artists-gplus',
                        'description' => 'Link pagina Google Plus',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Google Plus  dell\'artista'
                    ),
                    array(
                        'id' => 'artists-twitter',
                        'type' => 'text',
                        'name' => 'artists-twitter',
                        'description' => 'Link pagina Twitter',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Twitter  dell\'artista'
                    ),
                    array(
                        'id' => 'artists-youtube',
                        'type' => 'text',
                        'name' => 'artists-youtube',
                        'description' => 'Youtube',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Youtube dell\'artista'
                    ),
                    array(
                        'id' => 'artists-itunes',
                        'type' => 'text',
                        'name' => 'artists-itunes',
                        'description' => 'Itunes',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link della pagina Itunes dell\'artista'
                    ),
                    array(
                        'id' => 'artists-comune',
                        'type' => 'autocomplete',
                        'name' => 'artists-comune',
                        'description' => 'Comune',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_general_get_comuni'
                    ),
                    array(
                        'id' => 'artists-regione',
                        'type' => 'autocomplete',
                        'name' => 'artists-regione',
                        'description' => 'Regione',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_general_get_regioni'
                    ),
                    array(
                        'id' => 'artists-provincia',
                        'type' => 'autocomplete',
                        'name' => 'artists-provincia',
                        'description' => 'Provincia',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_general_get_province'
                    ),
                    array(
                        'id' => 'artists-nazione',
                        'type' => 'autocomplete',
                        'name' => 'artists-nazione',
                        'description' => 'Nazione',
                        'default' => '',
                        'required' => '',
                        'data-action' => 'rip_general_get_nazioni'
                    ),
                ),
            ),
        );
        
        $this->_ajax = array(
            'rip_artists_get_all_artists' => array(
                'class_name' => '\Rip_Artists\Controllers\Rip_Artists_Controller',
                'method_name' => 'get_all_artists',
            ),
            'rip_artists_get_all_artists_by_genre_slug' => array(
                'class_name' => '\Rip_Artists\Controllers\Rip_Artists_Controller',
                'method_name' => 'get_all_artists_by_genre_slug',
            ),
            'rip_artists_get_all_artists_by_tag_slug' => array(
                'class_name' => '\Rip_Artists\Controllers\Rip_Artists_Controller',
                'method_name' => 'get_all_artists_by_tag_slug',
            ),
            'rip_artists_get_artist_by_slug' => array(
                'class_name' => '\Rip_Artists\Controllers\Rip_Artists_Controller',
                'method_name' => 'get_artist_by_slug',
            ),
            'rip_artists_get_artists_genres' => array(
                'class_name' => '\Rip_Artists\Controllers\Rip_Artists_Controller',
                'method_name' => 'get_artists_genres',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$artists_plugin = new \Rip_Artists\Rip_Artists_Plugin();
