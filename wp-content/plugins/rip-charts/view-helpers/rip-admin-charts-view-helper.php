<?php
/**
 * Admin charts view helper.    
 */
class rip_admin_charts_view_helper extends rip_abstract_view_helper {
    
    /**
     * On construction all administrative panel assets are enquequed.
     */
    public function __construct() {
        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-admin-charts-partial.php';

        add_action('admin_enqueue_scripts', array($this, 'enqueque'));
    }
    
    /**
     * Enqueque all needed assets.
     */
    public function enqueque() {
        wp_enqueue_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.2.5/angular.min.js');
        wp_enqueue_script('angular-route', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.2.5/angular-route.js');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('admin-chart', '/wp-content/plugins/rip-charts/assets/admin-chart.css');
    }
    
    /**
     * Render the template.
     */
    public function render() {
        if (!empty($this->_field)) {
            extract($this->_view_args);
        }

        ob_start();
        include($this->_partial_path);
        $out = ob_get_contents();
        ob_end_clean();

        echo $out;
    }

}