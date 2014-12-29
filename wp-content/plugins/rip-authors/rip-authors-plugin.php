<?php

/*
  Plugin Name: Autori di Rip
  Description: Plugin per la gestione degli autori di Radio Illusioni Parallele
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general.php';
$autoloader = new rip_autoloader(plugin_dir_path(__FILE__));

/**
 * Rip Authors
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_programmi_abstract_plugin
 */
class rip_authors extends rip_abstract_plugin {

    /**
     * Set all plugin configuration.
     */
    public function _init() {
        $this->_ajax = array(
            'rip_authors_get_all_authors' => array(
                'class' => 'rip_authors_ajax_front_controller',
                'method_name' => 'get_all_authors',
            ),
            'rip_authors_get_author_by_slug' => array(
                'class' => 'rip_authors_ajax_front_controller',
                'method_name' => 'get_author_by_slug',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$rip_authors = new rip_authors();
