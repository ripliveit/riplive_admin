<?php

namespace Rip_General\Metaboxes;

/**
 * Dispay a textarea.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_metabox
 */
class Rip_Textarea_Metabox extends \Rip_General\Classes\Rip_Abstract_Metabox {

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
