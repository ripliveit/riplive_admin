<?php

/*
  Plugin Name: Riplive User
  Description: Plugin per la gestione dei brani
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general.php';
$autoloader = new rip_autoloader(plugin_dir_path(__FILE__));

/**
 * Social Users plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_plugin
 */
class rip_social_users extends rip_abstract_plugin {

    /**
     * Set all plugin configuration.
     */
    protected function _init() {
        $this->_ajax = array(
            'rip_social_users_get_social_user_by_email' => array(
                'class' => 'rip_social_users_ajax_front_controller',
                'method_name' => 'get_social_user_by_email',
            ),
            'rip_social_users_get_social_user_by_uuid' => array(
                'class' => 'rip_social_users_ajax_front_controller',
                'method_name' => 'get_social_user_by_uuid',
            ),
            'rip_social_users_insert_social_user' => array(
                'class' => 'rip_social_users_ajax_front_controller',
                'method_name' => 'insert_social_user',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$social_users = new rip_social_users();