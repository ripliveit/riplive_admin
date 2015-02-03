<?php

namespace Rip_Charts\View_Helpers;

/**
 * Admin charts view helper.    
 */
class Rip_Admin_Charts_View_helper extends \Rip_General\Classes\Rip_Abstract_View_Helper {

    /**
     * On construction,
     * all administrative panel assets are enquequed.
     */
    public function __construct() {
        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-admin-charts-partial.php';

        add_action('admin_enqueue_scripts', array($this, 'enqueque'));
    }

    /**
     * Enqueque all needed assets.
     */
    public function enqueque() {
        wp_enqueue_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.11/angular.min.js');
        wp_enqueue_script('angular-route', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.11/angular-route.min.js');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('admin-chart', '/wp-content/plugins/rip-charts/assets/admin-chart.css');
    }

}
