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
        $screen = get_current_screen();
        
        if (in_array($screen->id, array('toplevel_page_rip-podcasts/rip-podcasts-plugin'))) {
            wp_enqueue_script('tinymce', '/wp-content/plugins/rip-podcasts/assets/js/vendor/tinymce/tinymce.min.js');
        }

        wp_enqueue_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.11/angular.min.js');
        wp_enqueue_script('angular-route', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.11/angular-route.min.js');
        wp_enqueue_script('angular-upload-shim', '/wp-content/plugins/rip-podcasts/assets/js/vendor/angular-file-upload-shim.js');
        wp_enqueue_script('angular-upload', '/wp-content/plugins/rip-podcasts/assets/js/vendor/angular-file-upload.js');
        wp_enqueue_script('angular-ui', '/wp-content/plugins/rip-podcasts/assets/js/vendor/angular-ui-tinymce/src/tinymce.js');

        wp_enqueue_script('aws', 'https://sdk.amazonaws.com/js/aws-sdk-2.0.0-rc5.min.js');
        wp_enqueue_script('id3', '/wp-content/plugins/rip-podcasts/assets/js/vendor/id3-minimized.js');

        wp_enqueue_style('admin-podcast', '/wp-content/plugins/rip-podcasts/assets/admin-podcasts.css');
    }

}
