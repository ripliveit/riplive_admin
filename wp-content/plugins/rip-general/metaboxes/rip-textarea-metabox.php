<?php

/**
 * Concrete metabox.
 * Dispay a select box with, if presents, all saved value from database.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_metabox
 */
class rip_textarea_metabox extends rip_abstract_metabox {

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
        
        $this->_partial_path = plugin_dir_path(__FILE__) . 'partials/rip-textarea-partial.php';
    }
}