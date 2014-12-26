<?php

namespace Rip_General\Metaboxes;

/**
 * Dispay an autocomplete checkobox
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_metabox
 */
class Rip_Autocomplete_Metabox extends \Rip_General\Classes\Rip_Abstract_Metabox {

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

        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-autocomplete-partial.php';

        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    }

}
