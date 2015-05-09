<?php

namespace Rip_Flash_News;

/*
  Plugin Name: Flash news
  Description: Plugin per la gestione delle notizie flash.
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Flash News Plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Flash_News_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {
        $this->_metabox_prefix = 'Rip_';

        $this->_post_types = array(
            array(
                'name' => 'flash-news',
                'args' => array(
                    'labels' => array(
                        'name' => _x('Flash News', 'flash news'),
                        'singular_name' => _x('Flash', 'flash news'),
                        'add_new' => _x('Aggiungi nuova flash news', ''),
                        'add_new_item' => __('Aggiungi nuova flash news'),
                        'edit_item' => __('Modifica flash news'),
                        'new_item' => __('Nuova flash news'),
                        'all_items' => __('Tutte le flash news'),
                        'view_item' => __('Visualizza le flash news'),
                        'search_items' => __('Cerca flash news'),
                        'not_found' => __('Nessuna flash news'),
                        'not_found_in_trash' => __('Nessuna flash news nel cestino'),
                        'menu_name' => __('Flash News')
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
                'taxonomy_name' => 'flash-news-tag',
                'object_type' => array('flash-news'),
                'args' => array(
                    'hierarchical' => false,
                    'labels' => array(
                        'name' => __('Tag flash news'),
                        'singular_name' => __('Tag flash news'),
                        'search_items' => __('Cerca tag flash news'),
                        'popular_items' => __('Tag flash news piu popolari'),
                        'all_items' => __('Tutti i tag'),
                        'edit_item' => __('Modifica tag flash news'),
                        'update_item' => __('Aggiorna tag flash news'),
                        'add_new_item' => __('Aggiungi nuovo tag flash news'),
                        'new_item_name' => __('Nuovo tag flash news'),
                        'separate_items_with_commas' => __('Separa i tag con delle virgole'),
                        'add_or_remove_items' => __('Aggiungi o rimuovi tag'),
                        'choose_from_most_used' => __('Scegli un tag fra quelli più utilizzati'),
                        'menu_name' => __('Tag Flash News'),
                    ),
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'update_count_callback' => '_update_post_term_count',
                    'query_var' => true,
                    'rewrite' => true,
                ),
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$flash_news_plugin = new \Rip_Flash_News\Rip_Flash_News_Plugin();
