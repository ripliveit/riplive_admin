<?php

/*
  Plugin Name: AAA - General - AAA
  Description: Plugin generale per le varie funzionalità di RIP. Attivare prima di ogni altro plugin.
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

include_once 'classes/rip-autoloader.php';
$autoloader = new rip_autoloader(plugin_dir_path(__FILE__));

/**
 * General plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_programmi_abstract_plugin
 */
class rip_general extends rip_abstract_plugin {

    public function _init() {

        $this->_tables = array(
            array(
                'name' => 'general_comuni',
                'sql' => 'CREATE TABLE {NAME} (
                            id int(10) unsigned NOT NULL auto_increment,
                            id_regione int(10) unsigned NOT NULL,
                            id_provincia int(10) unsigned NOT NULL,
                            comune text NOT NULL,
                            PRIMARY KEY  (id)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8101;'
            ),
            array(
                'name' => 'general_province',
                'sql' => 'CREATE TABLE {NAME} (
                            id int(10) unsigned NOT NULL,
                            id_regione int(10) unsigned NOT NULL,
                            provincia text NOT NULL,
                            sigla varchar(2) NOT NULL,
                            PRIMARY KEY  (id)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            ),
            array(
                'name' => 'general_regioni',
                'sql' => 'CREATE TABLE {NAME} (
                            id int(11) unsigned NOT NULL,
                            regione text NOT NULL,
                            PRIMARY KEY  (id)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            ),
            array(
                'name' => 'general_nazioni',
                'sql' => 'CREATE TABLE {NAME} (
                            id INTEGER(11) NOT NULL AUTO_INCREMENT,
                            name VARCHAR(255) COLLATE latin1_swedish_ci NOT NULL,
                            PRIMARY KEY  (id)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            ),
        );

        $this->_ajax = array(
            'rip_general_get_comuni' => array(
                'class' => 'rip_general_ajax_front_controller',
                'method_name' => 'get_comuni',
            ),
            'rip_general_get_province' => array(
                'class' => 'rip_general_ajax_front_controller',
                'method_name' => 'get_province',
            ),
            'rip_general_get_regioni' => array(
                'class' => 'rip_general_ajax_front_controller',
                'method_name' => 'get_regioni',
            ),
            'rip_general_get_nazioni' => array(
                'class' => 'rip_general_ajax_front_controller',
                'method_name' => 'get_nazioni',
            ),
        );

        $this->_filters_to_add = array(
            array(
                'tag' => 'the_title',
                'class' => 'rip_general_output_filter',
                'function' => 'decode_wp_output',
            ),
            array(
                'tag' => 'the_content',
                'class' => 'rip_general_output_filter',
                'function' => 'decode_wp_output'
            ),
            array(
                'tag' => 'excerpt_more',
                'class' => 'rip_general_output_filter',
                'function' => 'remove_ellipsis'
            ),
            array(
                'tag' => 'the_excerpt',
                'class' => 'rip_general_output_filter',
                'function' => 'decode_wp_output'
            ),
            array(
                'tag' => 'the_permalink_rss',
                'class' => 'rip_general_rss_filters',
                'function' => 'change_rss_link'
            ),
            array(
                'tag' => 'get_the_guid',
                'class' => 'rip_general_rss_filters',
                'function' => 'change_rss_guid_link'
            ),
            array(
                'tag' => 'comments_link_feed',
                'class' => 'rip_general_rss_filters',
                'function' => 'change_rss_comment_link'
            ),
            array(
                'tag' => 'the_excerpt_rss',
                'class' => 'rip_general_rss_filters',
                'function' => 'add_featured_image'
            ),
            array(
                'tag' => 'the_content_feed',
                'class' => 'rip_general_rss_filters',
                'function' => 'add_featured_image'
            ),
        );

        $this->_dump = plugin_dir_path(__FILE__) . '/sql/dump.sql';

        register_activation_hook(__FILE__, array($this, 'load_tables'));
    }

}

$general = new rip_general();
