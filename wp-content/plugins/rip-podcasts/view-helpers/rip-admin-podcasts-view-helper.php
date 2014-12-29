<?php

namespace Rip_Podcasts\View_Helpers;

/**
 * Administrative podcasts view helper.    
 */
class Rip_Admin_Podcasts_View_Helper extends \Rip_General\Classes\Rip_Abstract_View_Helper {

    /**
     * On construction all administrative panel assets are enquequed.
     */
    public function __construct() {
        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-admin-podcasts-partial.php';
        add_action('admin_enqueue_scripts', array($this, 'enqueque'));
    }

    /**
     * Enqueque all needed assets.
     */
    public function enqueque() {
        wp_enqueue_script('angular-shim', '/wp-content/plugins/rip-podcasts/assets/angular-file-upload-shim.min.js');
        wp_enqueue_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.2.5/angular.min.js');
        wp_enqueue_script('angular-route', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.2.5/angular-route.js');
        wp_enqueue_script('angular-upload', '/wp-content/plugins/rip-podcasts/assets/angular-file-upload.min.js');
        wp_enqueue_script('aws', 'https://sdk.amazonaws.com/js/aws-sdk-2.0.0-rc5.min.js');
        wp_enqueue_script('id3', '/wp-content/plugins/rip-podcasts/assets/id3-minimized.js');
        wp_enqueue_style('admin-podcast', '/wp-content/plugins/rip-podcasts/assets/admin-podcasts.css');
    }

}
