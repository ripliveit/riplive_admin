<?php

/**
 * Concrete metabox.
 * Dispay an input type text with, if presents, all saved value from database.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_metabox
 */
class rip_time_metabox extends rip_abstract_metabox {

    /**
     * On construction set the partial path.
     */
    public function __construct($field, $meta) {
        $this->_field = array(
            'field' => $field
        );

        $this->_meta = array(
            'meta' => $meta
        );

        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-time-partial.php';

        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('jquery-timepicker', get_template_directory_uri() . '/js/jquery-ui-timepicker.js');
        wp_enqueue_style('jquery-timepicker-style', get_template_directory_uri() . '/css/jquery-ui-timepicker.css');
    }

}