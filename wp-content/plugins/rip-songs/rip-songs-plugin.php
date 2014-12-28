<?php

namespace Rip_Songs;

/*
  Plugin Name: Brani
  Description: Plugin per la gestione dei brani musicali di Rip.
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Songs plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_plugin
 */
class Rip_Songs_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'Rip_';

        $this->_post_types = array(
            array(
                'name' => 'songs',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Brani', 'brani'),
                        'singular_name' => _x('Brani', 'brani'),
                        'add_new' => _x('Aggiungi nuovo', ''),
                        'add_new_item' => __('Aggiungi nuovo brano'),
                        'edit_item' => __('Modifica brano'),
                        'new_item' => __('Nuovo brano'),
                        'all_items' => __('Tutti i brani'),
                        'view_item' => __('Visualizza i brani'),
                        'search_items' => __('Cerca brano'),
                        'not_found' => __('Nessun brano'),
                        'not_found_in_trash' => __('Nessun brano nel cestino'),
                        'menu_name' => __('Brani')
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
                'taxonomy_name' => 'song-genre',
                'object_type' => array('songs'),
                'args' => array(
                    'hierarchical' => true,
                    'label' => 'Genere brano',
                    'singular_label' => 'Genere brano',
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
                'taxonomy_name' => 'song-tag',
                'object_type' => array('songs'),
                'args' => array(
                    'hierarchical' => false,
                    'labels' => array(
                        'name' => __('Tag brano'),
                        'singular_name' => __('Tag brano'),
                        'search_items' => __('Cerca tag brani'),
                        'popular_items' => __('Tag brani piu popolari'),
                        'all_items' => __('Tutti i tag'),
                        'edit_item' => __('Modifica tag brano'),
                        'update_item' => __('Aggiorna tag brano'),
                        'add_new_item' => __('Aggiungi nuovo tag brano'),
                        'new_item_name' => __('Nuovo tag brano'),
                        'separate_items_with_commas' => __('Separa i tag con delle virgole'),
                        'add_or_remove_items' => __('Aggiungi o rimuovi tag'),
                        'choose_from_most_used' => __('Scegli un tag fra quelli più utilizzati'),
                        'menu_name' => __('Tag Brani'),
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
                    'post_type' => 'songs',
                    'id' => 'songs-metabox',
                    'title' => 'Informazioni Brano',
                    'context' => 'normal',
                    'priority' => 'high',
                ),
                'fields' => array(
                    array(
                        'id' => 'songs-hidden',
                        'type' => 'hidden',
                        'name' => 'songs-hidden',
                        'description' => '',
                        'default' => '',
                        'required' => '',
                        'placeholder' => ''
                    ),
                    array(
                        'id' => 'songs-artist',
                        'type' => 'text',
                        'name' => 'songs-artist',
                        'description' => 'Nome dell\'artista',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il nome dell\'artista'
                    ),
                    array(
                        'id' => 'songs-album',
                        'type' => 'text',
                        'name' => 'songs-album',
                        'description' => 'Titolo dell\'album',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il titolo dell\'album'
                    ),
                    array(
                        'id' => 'songs-year',
                        'type' => 'date',
                        'name' => 'songs-year',
                        'description' => 'Anno di pubblicazione',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci l\'anno di pubblicazione del brano'
                    ),
                    array(
                        'id' => 'songs-spotify',
                        'type' => 'text',
                        'name' => 'songs-spotify',
                        'description' => 'Link Spotify',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link Spotify del brano'
                    ),
                    array(
                        'id' => 'songs-Youtube',
                        'type' => 'text',
                        'name' => 'songs-spotify',
                        'description' => 'Link al video Youtube',
                        'default' => '',
                        'required' => '',
                        'placeholder' => 'Inserisci il link al video Youtube'
                    ),
                ),
            ),
        );

        $this->_ajax = array(
            'rip_songs_get_all_songs' => array(
                'class_name' => '\Rip_Songs\Controllers\Rip_Songs_Controller',
                'method_name' => 'get_all_songs',
            ),
            'rip_songs_get_all_songs_by_genre_slug' => array(
                'class_name' => '\Rip_Songs\Controllers\Rip_Songs_Controller',
                'method_name' => 'get_all_songs_by_genre_slug',
            ),
            'rip_songs_get_all_songs_by_tag_slug' => array(
                'class_name' => '\Rip_Songs\Controllers\Rip_Songs_Controller',
                'method_name' => 'get_all_songs_by_tag_slug',
            ),
            'rip_songs_get_song_by_slug' => array(
                'class_name' => '\Rip_Songs\Controllers\Rip_Songs_Controller',
                'method_name' => 'get_song_by_slug',
            ),
            'rip_songs_get_songs_genres' => array(
                'class_name' => '\Rip_Songs\Controllers\Rip_Songs_Controller',
                'method_name' => 'get_songs_genres',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$songs_plugin = new \Rip_Songs\Rip_Songs_Plugin();