<?php

namespace Rip_Social_Users;

/*
  Plugin Name: Riplive Social Users
  Description: Plugin per la registrazioe degli utenti tramite social networks.
  Author: Gabriele D'Arrigo - @acirdesign
  Version: 1.0
 */

require_once ABSPATH . 'wp-content/plugins/rip-general/rip-general-plugin.php';
$autoloader = new \Rip_General\Classes\Rip_Autoloader(plugin_dir_path(__FILE__));

/**
 * Social Users plugin.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see \Rip_General\Classes\Rip_Abstract_Plugin
 */
class Rip_Social_Users_Plugin extends \Rip_General\Classes\Rip_Abstract_Plugin {
    
    /**
     * Set plugin's configuration.
     */
    protected function _init() {
        $this->_ajax = array(
            'rip_social_users_get_social_user_by_email' => array(
                'class_name' => '\Rip_Social_Users\Controllers\Rip_Social_Users_Controller',
                'method_name' => 'get_social_user_by_email',
            ),
            'rip_social_users_get_social_user_by_uuid' => array(
                'class_name' => '\Rip_Social_Users\Controllers\Rip_Social_Users_Controller',
                'method_name' => 'get_social_user_by_uuid',
            ),
            'rip_social_users_insert_social_user' => array(
                'class_name' => '\Rip_Social_Users\Controllers\Rip_Social_Users_Controller',
                'method_name' => 'insert_social_user',
            ),
        );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

}

$social_users_plugin = new \Rip_Social_Users\Rip_Social_Users_Plugin();
